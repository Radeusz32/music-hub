# Auth: E-mail Verification, Password Reset & Password Change

End-to-end reference for the three account flows built on **Laravel's built-in
mechanisms** (the `Password` broker, `MustVerifyEmail`, the `Registered` event,
signed URLs). All routes live in `routes/tenant.php` and run inside the tenant
context (`['web', 'tenant', 'prevent-central']`), so every generated URL and
e-mail link is scoped to the current tenant subdomain.

> Mail transport is global SMTP (`.env` `MAIL_*`), not tenant-specific. All
> notifications send **synchronously** during the request (no queue worker).

---

## Routes at a glance

| Method | URI                                 | Name                               | Middleware                                 | Controller                                      |
| ------ | ----------------------------------- | ---------------------------------- | ------------------------------------------ | ----------------------------------------------- |
| GET    | `/forgot-password`                  | `password.request`                 | `guest`                                    | `PasswordResetLinkController@create`            |
| POST   | `/forgot-password`                  | `password.email`                   | `guest`                                    | `PasswordResetLinkController@store`             |
| GET    | `/reset-password/{token}`           | `password.reset`                   | `guest`                                    | `NewPasswordController@create`                  |
| POST   | `/reset-password`                   | `password.store`                   | `guest`                                    | `NewPasswordController@store`                   |
| GET    | `/verify-email/{id}/{hash}`         | `verification.verify`              | `signed`, `throttle:6,1`                   | `VerifyEmailController`                         |
| GET    | `/verify-email`                     | `verification.notice`              | `auth`                                     | `EmailVerificationPromptController`             |
| POST   | `/email/verification-notification`  | `verification.send`                | `auth`, `throttle:6,1`                     | `EmailVerificationNotificationController@store` |
| PUT    | `/settings/password`                | `tenant.settings.password.update`  | `auth`                                     | `Settings\PasswordController@update`            |
| POST   | `/users/{user}/resend-verification` | `tenant.users.resend-verification` | `feature:users`, `permission:users-update` | `Users\UserController@resendVerification`       |

`verification.verify` sits **outside** the `auth` group on purpose - see
[Why verification is session-independent](#why-verification-is-session-independent).

---

## 1. Password reset ("przypomnienie hasła") - guest

Standard Laravel `Password` broker flow. The tenant `User` already has the
`CanResetPassword` trait (via `Illuminate\Foundation\Auth\User`), and the tenant
DB ships a `password_reset_tokens` table (`migrations/tenant/..._create_users_table.php`).

**Flow:**

```
1. Login page → link "Nie pamiętasz hasła?" → GET /forgot-password
   → ForgotPassword.vue (email field)
   ↓
2. POST /forgot-password (PasswordResetLinkRequest: email required|email)
   → Password::sendResetLink(['email'])
   → on RESET_LINK_SENT: back()->with('success', trans($status))
   → otherwise: ValidationException on `email` (trans($status))
   ↓
3. User's mailbox: Laravel ResetPassword notification. Link is
   url(route('password.reset', ['token'=>…, 'email'=>…], absolute:false)),
   i.e. on the current tenant host.
   ↓
4. GET /reset-password/{token}?email=… → ResetPassword.vue
   (token + email seeded into the form from controller props)
   ↓
5. POST /reset-password (NewPasswordRequest: token, email, password confirmed + Password::defaults())
   → Password::reset(...): re-hashes password, new remember_token, fires PasswordReset
   → on PASSWORD_RESET: redirect()->route('login')->with('success', …)
   → otherwise: ValidationException on `email`
```

**Files:** `PasswordResetLinkController`, `NewPasswordController` (both
`app/Http/Controllers/Tenant/Auth/`), requests in
`app/Http/Requests/Tenant/Auth/{PasswordResetLinkRequest,NewPasswordRequest}.php`,
pages `resources/js/Pages/Tenant/Auth/{ForgotPassword,ResetPassword}.vue` (both
use the shared `layout/Tenant/AuthLayout.vue`).

> **Broker status strings are localized** in `lang/en/passwords.php` (Polish
> copy). `APP_LOCALE=en` and there was no `lang/` dir before, so without this
> file `trans('passwords.sent')` would render the raw key.

---

## 2. Password change ("zmiana hasła") - authenticated, on the Profile page

**Flow:**

```
Settings → Profil (/settings/profile) → "Zmiana hasła" form
   ↓
PUT /settings/password (UpdatePasswordRequest)
   rules: current_password => ['required','current_password'],
          password => ['required','confirmed', Password::defaults()]
   ↓
PasswordController@update → $request->user()->update(['password' => Hash::make(...)])
   → back()->with('success', 'Hasło zostało zmienione.')
```

The `current_password` rule verifies the supplied password against the logged-in
user's hash via the `web` guard. The page (`Profile.vue`) keeps a separate
`useForm` for the password section and resets it on success/error.

**Files:** `app/Http/Controllers/Tenant/Settings/PasswordController.php`,
`app/Http/Requests/Tenant/Settings/UpdatePasswordRequest.php`,
`resources/js/Pages/Tenant/Settings/Profile.vue`.

---

## 3. E-mail verification

`App\Models\Tenant\User implements MustVerifyEmail`. The whole authenticated
application (dashboard + every feature module) is wrapped in a
`Route::middleware('verified')` group, so an unverified user is redirected to
`verification.notice`. Account-level routes (`logout`, the verification routes
themselves, `settings/password`) sit **before** that group and stay reachable
while unverified.

### How a user becomes unverified

- **Registration / admin-created users:** `UserService::create()` does **not**
  set `email_verified_at` and fires `event(new Registered($user))`. Laravel's
  auto-registered `SendEmailVerificationNotification` listener (confirm with
  `artisan event:list`) then sends the verification e-mail because the model is
  `MustVerifyEmail` and unverified.
- The `UserFactory` still seeds users as **verified** (`email_verified_at = now()`),
  with an `unverified()` state - so seeded/dev accounts (e.g. the owner) are not
  forced through verification.

### Verifying

```
E-mail "Verify Email Address" → GET /verify-email/{id}/{hash}?expires=…&signature=…
   (Laravel's VerifyEmail notification builds a temporarySignedRoute)
   ↓
`signed` middleware validates the signature + expiry (403 on tamper/expiry)
   ↓
VerifyEmailController:
   - User::findOrFail($id)
   - hash_equals($hash, sha1($user->getEmailForVerification()))  → else abort(403)
   - if !hasVerifiedEmail(): markEmailAsVerified() + event(new Verified($user))
   - Auth::login($user); session()->regenerate()
   - redirect()->route('tenant.dashboard')->with('success', …)
```

### Why verification is session-independent

Laravel's default `verification.verify` requires `auth` + the
`EmailVerificationRequest`, which checks the **currently logged-in** user's id
against the link's `{id}`. That breaks the **admin-creates-account** flow: the
new user isn't logged in (or an admin testing in the same browser is logged in
as someone else), producing a **403**.

So this route is **outside** `auth`. Security is preserved by the **signed URL**
(tamper-proof, expiring) plus the `hash_equals` check on `sha1(email)` - the same
proof Laravel uses. Possession of the link (delivered only to the address being
verified) is sufficient. After verifying, the controller **logs the user in** and
regenerates the session, so clicking the link lands them straight in the panel.

> Caveat: if you click the link in a browser already logged in as another user,
> that session is **replaced** by the verified user (by design).

### Resending

- **Self (logged-in, unverified):** the `verification.notice` page
  (`VerifyEmail.vue`) has a "Wyślij ponownie link" button → POST
  `verification.send` → `EmailVerificationNotificationController@store` →
  `$user->sendEmailVerificationNotification()` → `back()->with('status','verification-link-sent')`.
- **Admin, for another user:** Users → Show page shows a "Wyślij ponownie link"
  button **only when `email_verified_at === null`** (and the admin has
  `users-update`). POST `tenant.users.resend-verification` →
  `UserController@resendVerification` (early-returns an `error` flash if already
  verified, otherwise sends and flashes `success`).

**Files:** `app/Http/Controllers/Tenant/Auth/{EmailVerificationPromptController,
VerifyEmailController,EmailVerificationNotificationController}.php`,
`UserController@resendVerification`, pages `Auth/VerifyEmail.vue` &
`Users/Show.vue`.

---

## Profile settings page

`/settings/profile` (`tenant.settings.profile`) is gated by
`permission:setting-profile` (seeded for **all** roles in
`SettingsPermissionsSeeder`) and lives **outside** the `feature:settings` group -
account self-management must not depend on a paid feature flag (the dev tenant
doesn't even have `settings`). `organization` / `billing` remain under
`feature:settings`.

- GET `/settings/profile` → `SettingController@profile` → `SettingService::profile()`
  → `UserService::show()` (transformed user, incl. decrypted PII).
- PUT `/settings/profile` (`profile.update`, `permission:setting-profile`) →
  `UpdateProfileRequest` → `SettingController@updateProfile` →
  `SettingService::updateProfile()` → `UserService::update()`.

`SettingController` is thin (constructor-injected `SettingService`); the service
delegates to `UserService` rather than touching the model directly.
`UpdateProfileRequest` covers name/email/phone/address/PESEL (no role, no
`is_active`); email uniqueness + `PeselRule` ignore the current user's id.

> **Note:** changing the e-mail here does **not** reset `email_verified_at` - the
> user stays "verified" with the old timestamp. A Breeze-style re-verification on
> e-mail change is not implemented.

---

## Key files

| Concern                       | File                                                                                    |
| ----------------------------- | --------------------------------------------------------------------------------------- |
| Password reset (request link) | `app/Http/Controllers/Tenant/Auth/PasswordResetLinkController.php`                      |
| Password reset (set new)      | `app/Http/Controllers/Tenant/Auth/NewPasswordController.php`                            |
| Password change               | `app/Http/Controllers/Tenant/Settings/PasswordController.php`                           |
| Verify (signed link)          | `app/Http/Controllers/Tenant/Auth/VerifyEmailController.php`                            |
| Verify notice / resend (self) | `app/Http/Controllers/Tenant/Auth/EmailVerification{Prompt,NotificationController}.php` |
| Resend (admin)                | `app/Http/Controllers/Tenant/Users/UserController.php@resendVerification`               |
| New-user verification trigger | `app/Services/Tenant/Users/UserService.php@create` (`event(new Registered(...))`)       |
| Broker lang strings           | `lang/en/passwords.php`                                                                 |
| Guest pages                   | `resources/js/Pages/Tenant/Auth/{ForgotPassword,ResetPassword,VerifyEmail}.vue`         |
| Shared guest shell            | `resources/js/layout/Tenant/AuthLayout.vue`                                             |
| Profile + password change UI  | `resources/js/Pages/Tenant/Settings/Profile.vue`                                        |
