# Base Components - Dokumentacja

Globalne komponenty input z pełną kontrolą nad stylem i funkcjonalnością.

## 📦 Komponenty

### 1. **BaseInput** - Uniwersalny input

### 2. **BaseInputNumber** - Input numeryczny ze spinnerami

### 3. **BasePassword** - Input hasła z togglem i wskaźnikiem siły

### 4. **BaseCheckbox** - Checkbox z animacjami

### 5. **BaseDropdown** - Dropdown/Select z gradientem

### 6. **BaseTextArea** - Wielowierszowe pole tekstowe

### 7. **BaseMaskedInput** - Input z maską wzorcową (telefon, kod itp.)

### 8. **BaseDialog** - Modal (rozmiar + kolumny zarządzane Tailwindem)

> **Komponenty towarzyszące** (nie `Base*`, ale używane w tych samych miejscach):
> `FileUpload`, `DatePicker`, `Tooltip`, `AppToast`. Opisane na końcu pliku.

---

## 🌐 Rejestracja globalna

Wszystkie `Base*` są rejestrowane globalnie w
`resources/js/plugins/base-components.ts` (`app.component(...)`), więc **nie
importujesz ich** w komponentach - używasz `<BaseInput ... />` bezpośrednio.
`FileUpload`, `DatePicker`, `Tooltip` importujesz normalnie (`@/Components/...`).
Dodając nowy `Base*` komponent, dopisz go do `base-components.ts`.

---

## 🎯 BaseInput

Uniwersalny input z opcjami prefix/suffix (ikony lub tekst).

### Props

| Prop          | Typ                | Default  | Opis                                           |
| ------------- | ------------------ | -------- | ---------------------------------------------- |
| `modelValue`  | `string \| number` | `""`     | Wartość inputu (v-model)                       |
| `type`        | `string`           | `"text"` | Typ inputu (text, email, url, etc.)            |
| `placeholder` | `string`           | `""`     | Placeholder                                    |
| `disabled`    | `boolean`          | `false`  | Wyłączony                                      |
| `readonly`    | `boolean`          | `false`  | Tylko do odczytu                               |
| `prefix`      | `string`           | -        | Tekst przed inputem                            |
| `suffix`      | `string`           | -        | Tekst po inpucie                               |
| `prefixIcon`  | `string`           | -        | Klasa ikony przed inputem (np. `pi pi-search`) |
| `suffixIcon`  | `string`           | -        | Klasa ikony po inpucie (np. `pi pi-times`)     |
| `error`       | `boolean`          | `false`  | Stan błędu (czerwony border)                   |
| `id`          | `string`           | -        | ID dla label                                   |

### Events

- `@update:modelValue` - Zmiana wartości
- `@focus` - Focus na input
- `@blur` - Blur z inputu
- `@prefixClick` - Kliknięcie w prefix
- `@suffixClick` - Kliknięcie w suffix

### Slots

- `prefix` - Custom prefix content
- `suffix` - Custom suffix content

### Przykłady użycia

```vue
<!-- Prosty input -->
<BaseInput v-model="name" placeholder="Wpisz imię" />

<!-- Z ikoną wyszukiwania -->
<BaseInput
    v-model="search"
    placeholder="Szukaj..."
    prefix-icon="pi pi-search"
/>

<!-- Z suffixem tekstowym -->
<BaseInput v-model="price" type="number" suffix="PLN" placeholder="0.00" />

<!-- Email z ikoną -->
<BaseInput
    v-model="email"
    type="email"
    prefix-icon="pi pi-envelope"
    placeholder="twoj@email.pl"
/>

<!-- URL z prefiksem i suffixem -->
<BaseInput
    v-model="website"
    type="url"
    prefix="https://"
    suffix-icon="pi pi-external-link"
    placeholder="example.com"
/>

<!-- Custom slot dla złożonych prefix/suffix -->
<BaseInput v-model="query">
    <template #prefix>
        <button class="custom-btn">
            <i class="pi pi-filter" />
        </button>
    </template>
    <template #suffix>
        <span class="badge">5</span>
    </template>
</BaseInput>

<!-- Stan błędu -->
<BaseInput
    v-model="username"
    :error="!!errors.username"
    placeholder="Nazwa użytkownika"
/>
```

---

## 🔢 BaseInputNumber

Input numeryczny z opcjonalnymi spinnerami (góra/dół).

### Props

| Prop                | Typ                       | Default   | Opis                                                                                                                                                                                  |
| ------------------- | ------------------------- | --------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `modelValue`        | `number \| null`          | `null`    | Wartość (v-model)                                                                                                                                                                     |
| `placeholder`       | `string`                  | `""`      | Placeholder                                                                                                                                                                           |
| `disabled`          | `boolean`                 | `false`   | Wyłączony                                                                                                                                                                             |
| `readonly`          | `boolean`                 | `false`   | Tylko do odczytu                                                                                                                                                                      |
| `min`               | `number`                  | -         | Minimalna wartość                                                                                                                                                                     |
| `max`               | `number`                  | -         | Maksymalna wartość                                                                                                                                                                    |
| `step`              | `number`                  | `1`       | Krok zmiany                                                                                                                                                                           |
| `prefix`            | `string`                  | -         | Tekst przed inputem                                                                                                                                                                   |
| `suffix`            | `string`                  | -         | Tekst po inpucie                                                                                                                                                                      |
| `prefixIcon`        | `string`                  | -         | Ikona przed inputem                                                                                                                                                                   |
| `suffixIcon`        | `string`                  | -         | Ikona po inpucie                                                                                                                                                                      |
| `error`             | `boolean`                 | `false`   | Stan błędu                                                                                                                                                                            |
| `showButtons`       | `boolean`                 | `false`   | Pokaż przyciski +/-                                                                                                                                                                   |
| `format`            | `"decimal" \| "currency"` | -         | Tryb formatowania. Bez niego input działa jak zwykły `type="number"`. Z nim wyświetla sformatowaną liczbę (przecinek, grupowanie tysięcy) po utracie focusa, a model zostaje `number` |
| `locale`            | `string`                  | `"pl-PL"` | Locale do formatowania (separatory)                                                                                                                                                   |
| `grouping`          | `boolean`                 | `true`    | Grupowanie tysięcy (np. `1 234,50`)                                                                                                                                                   |
| `minFractionDigits` | `number`                  | `0`       | Min cyfr po przecinku (dla `format="decimal"`; `currency` wymusza 2)                                                                                                                  |
| `maxFractionDigits` | `number`                  | `2`       | Max cyfr po przecinku (dla `format="decimal"`; `currency` wymusza 2)                                                                                                                  |
| `id`                | `string`                  | -         | ID dla label                                                                                                                                                                          |

> **Formatowanie (`format`)**: bez tego propa komponent zachowuje się jak natywny `type="number"` (model `number | null`). Z `format="currency"` lub `format="decimal"` przełącza się na `type="text"` z `inputmode="decimal"`: podczas edycji pozwala wpisywać z przecinkiem lub kropką, a po blur pokazuje sformatowaną wartość (np. `1 234,50`). Model nadal jest liczbą (`1234.5`). `format="currency"` wymusza zawsze 2 miejsca po przecinku.

### Events

- `@update:modelValue` - Zmiana wartości
- `@focus` - Focus
- `@blur` - Blur

### Slots

- `prefix` - Custom prefix
- `suffix` - Custom suffix

### Przykłady użycia

```vue
<!-- Podstawowy number input -->
<BaseInputNumber v-model="quantity" placeholder="0" />

<!-- Z przyciskami spinner -->
<BaseInputNumber
    v-model="quantity"
    :min="0"
    :max="100"
    :show-buttons="true"
    placeholder="Ilość"
/>

<!-- Cena z suffixem -->
<BaseInputNumber
    v-model="price"
    :min="0"
    :step="0.01"
    suffix="PLN"
    placeholder="0.00"
/>

<!-- Kwota z formatowaniem walutowym (99,99 zł, model = number) -->
<BaseInputNumber
    v-model="price"
    :min="0"
    format="currency"
    suffix="zł"
    placeholder="0,00"
/>

<!-- Liczba dziesiętna z grupowaniem (1 234,5) -->
<BaseInputNumber v-model="amount" format="decimal" :max-fraction-digits="3" />

<!-- Procent z ikoną -->
<BaseInputNumber
    v-model="discount"
    :min="0"
    :max="100"
    suffix-icon="pi pi-percentage"
    placeholder="0"
/>

<!-- Rok produkcji -->
<BaseInputNumber
    v-model="year"
    :min="1900"
    :max="new Date().getFullYear()"
    placeholder="RRRR"
/>

<!-- Waga z prefiksem i suffixem -->
<BaseInputNumber
    v-model="weight"
    :step="0.1"
    prefix-icon="pi pi-box"
    suffix="kg"
    :show-buttons="true"
/>

<!-- Custom slot dla waluty -->
<BaseInputNumber v-model="amount">
    <template #prefix>
        <select class="currency-select">
            <option>PLN</option>
            <option>EUR</option>
            <option>USD</option>
        </select>
    </template>
</BaseInputNumber>
```

---

## 🔒 BasePassword

Input hasła z togglem widoczności i wskaźnikiem siły.

### Props

| Prop           | Typ       | Default | Opis                      |
| -------------- | --------- | ------- | ------------------------- |
| `modelValue`   | `string`  | `""`    | Wartość (v-model)         |
| `placeholder`  | `string`  | `""`    | Placeholder               |
| `disabled`     | `boolean` | `false` | Wyłączony                 |
| `readonly`     | `boolean` | `false` | Tylko do odczytu          |
| `error`        | `boolean` | `false` | Stan błędu                |
| `showToggle`   | `boolean` | `true`  | Pokaż przycisk show/hide  |
| `showStrength` | `boolean` | `false` | Pokaż wskaźnik siły hasła |
| `id`           | `string`  | -       | ID dla label              |

### Events

- `@update:modelValue` - Zmiana wartości
- `@focus` - Focus
- `@blur` - Blur

### Siła hasła

Wskaźnik siły ocenia hasło na podstawie:

- Długość (≥8, ≥12 znaków)
- Małe i wielkie litery
- Cyfry
- Znaki specjalne

**Poziomy:**

- 🔴 **Słabe** (0-2 punkty) - czerwony
- 🟠 **Średnie** (3 punkty) - pomarańczowy
- 🟢 **Silne** (4-5 punktów) - zielony

### Przykłady użycia

```vue
<!-- Podstawowe hasło -->
<BasePassword v-model="password" placeholder="Hasło" />

<!-- Z wskaźnikiem siły -->
<BasePassword
    v-model="newPassword"
    placeholder="Nowe hasło"
    :show-strength="true"
/>

<!-- Bez toggle (zawsze ukryte) -->
<BasePassword
    v-model="confirmPassword"
    placeholder="Potwierdź hasło"
    :show-toggle="false"
/>

<!-- Stan błędu -->
<BasePassword
    v-model="password"
    :error="!!errors.password"
    placeholder="Hasło"
/>

<!-- Formularz rejestracji -->
<div>
    <label for="new-pass">Nowe hasło</label>
    <BasePassword
        id="new-pass"
        v-model="form.password"
        :show-strength="true"
        :error="!!form.errors.password"
        placeholder="Min. 8 znaków"
    />
    <p v-if="form.errors.password" class="error">
        {{ form.errors.password }}
    </p>
</div>
```

---

## ☑️ BaseCheckbox

Checkbox z custom designem i animacjami.

### Props

| Prop         | Typ       | Default | Opis                 |
| ------------ | --------- | ------- | -------------------- |
| `modelValue` | `boolean` | `false` | Wartość (v-model)    |
| `label`      | `string`  | `""`    | Tekst obok checkboxa |
| `disabled`   | `boolean` | `false` | Wyłączony            |
| `error`      | `boolean` | `false` | Stan błędu           |
| `id`         | `string`  | auto    | ID dla label         |

### Events

- `@update:modelValue` - Zmiana wartości

### Slots

- `default` - Custom label content (zamiast prop `label`)

### Przykłady użycia

```vue
<!-- Prosty checkbox -->
<BaseCheckbox v-model="accepted" label="Akceptuję regulamin" />

<!-- Bez labela (tylko checkbox) -->
<BaseCheckbox v-model="isActive" />

<!-- Custom label przez slot -->
<BaseCheckbox v-model="marketing">
    Zgadzam się na otrzymywanie newslettera
    <a href="/privacy" class="link">Polityka prywatności</a>
</BaseCheckbox>

<!-- Stan błędu -->
<BaseCheckbox
    v-model="terms"
    :error="!terms && submitted"
    label="Wymagana zgoda"
/>

<!-- Lista checkboxów -->
<div class="checkbox-group">
    <BaseCheckbox
        v-for="option in options"
        :key="option.id"
        v-model="option.selected"
        :label="option.name"
    />
</div>

<!-- Z disabled -->
<BaseCheckbox v-model="readonly" label="Opcja zablokowana" disabled />

<!-- Formularz z walidacją -->
<div>
    <BaseCheckbox
        v-model="form.terms"
        :error="!!form.errors.terms"
    >
        Akceptuję <a href="/terms">regulamin</a>
    </BaseCheckbox>
    <p v-if="form.errors.terms" class="error">
        {{ form.errors.terms }}
    </p>
</div>
```

---

## 📋 BaseDropdown

Dropdown/Select z gradientowym tłem i animacjami.

### Props

| Prop          | Typ                        | Default           | Opis                   |
| ------------- | -------------------------- | ----------------- | ---------------------- |
| `modelValue`  | `string \| number \| null` | `null`            | Wartość (v-model)      |
| `options`     | `DropdownOption[]`         | `[]`              | Lista opcji            |
| `placeholder` | `string`                   | `""`              | Placeholder            |
| `disabled`    | `boolean`                  | `false`           | Wyłączony              |
| `error`       | `boolean`                  | `false`           | Stan błędu             |
| `prefixIcon`  | `string`                   | -                 | Ikona przed selectem   |
| `suffixIcon`  | `string`                   | -                 | Ikona po selecie       |
| `emptyLabel`  | `string`                   | `"Select option"` | Label dla pustej opcji |
| `id`          | `string`                   | -                 | ID dla label           |

### DropdownOption Interface

```typescript
interface DropdownOption {
    value: string | number;
    label: string;
    disabled?: boolean;
}
```

### Events

- `@update:modelValue` - Zmiana wartości
- `@change` - Zmiana wartości (alternatywny event)

### Slots

- `prefix` - Custom prefix content
- `suffix` - Custom suffix content
- `option` - Custom option rendering (otrzymuje `{ option }`)

### Przykłady użycia

```vue
<!-- Prosty dropdown -->
<BaseDropdown
    v-model="selectedFormat"
    :options="[
        { value: 'vinyl', label: 'Vinyl' },
        { value: 'cd', label: 'CD' },
        { value: 'cassette', label: 'Cassette' },
    ]"
    placeholder="Select format"
/>

<!-- Z ikoną prefix -->
<BaseDropdown
    v-model="selectedGenre"
    :options="genreOptions"
    prefix-icon="pi pi-music"
    placeholder="Select genre"
/>

<!-- Z disabled opcjami -->
<BaseDropdown
    v-model="selectedOption"
    :options="[
        { value: 'option1', label: 'Available Option' },
        { value: 'option2', label: 'Disabled Option', disabled: true },
        { value: 'option3', label: 'Another Available' },
    ]"
/>

<!-- Stan błędu -->
<BaseDropdown
    v-model="form.format"
    :options="formatOptions"
    :error="!!form.errors.format"
    placeholder="Format"
/>
<p v-if="form.errors.format" class="error">
    {{ form.errors.format }}
</p>

<!-- Custom slot dla opcji -->
<BaseDropdown v-model="selectedUser" :options="users">
    <template #option="{ option }">
        <i class="pi pi-user" />
        {{ option.label }}
    </template>
</BaseDropdown>

<!-- Z custom prefix slot -->
<BaseDropdown v-model="selectedFilter" :options="filterOptions">
    <template #prefix>
        <span class="badge">{{ selectedCount }}</span>
    </template>
</BaseDropdown>

<!-- Disabled state -->
<BaseDropdown v-model="lockedValue" :options="options" disabled />

<!-- W formularzu -->
<div class="field">
    <label for="condition">Condition</label>
    <BaseDropdown
        id="condition"
        v-model="form.condition"
        :options="conditionOptions"
        :error="!!form.errors.condition"
        placeholder="Select condition"
    />
    <p v-if="form.errors.condition" class="error">
        {{ form.errors.condition }}
    </p>
</div>
```

### TypeScript Example

```typescript
import type { DropdownOption } from "@/Components/BaseDropdown.vue";

const formatOptions: DropdownOption[] = [
    { value: "vinyl", label: "Vinyl" },
    { value: "cd", label: "CD" },
    { value: "cassette", label: "Cassette" },
    { value: "digital", label: "Digital" },
];

const selectedFormat = ref<string | null>(null);
```

### Features

✨ **Gradient Background** - Smooth gradient z dark theme
🎨 **Animated Chevron** - Chevron rotuje przy focus
🔵 **Focus States** - Border i shadow przy focus
🚫 **Disabled Support** - Pełne wsparcie disabled state
❌ **Error States** - Czerwony border dla błędów
🎯 **Custom Slots** - Prefix, suffix, option slots
📱 **Responsive** - Działa na wszystkich rozmiarach

---

## 📝 BaseTextArea

Wielowierszowe pole tekstowe ze spójnym designem (taki sam wygląd i stany jak `BaseInput`).

### Props

| Prop          | Typ       | Default | Opis                         |
| ------------- | --------- | ------- | ---------------------------- |
| `modelValue`  | `string`  | `""`    | Wartość (v-model)            |
| `placeholder` | `string`  | `""`    | Placeholder                  |
| `disabled`    | `boolean` | `false` | Wyłączony                    |
| `readonly`    | `boolean` | `false` | Tylko do odczytu             |
| `error`       | `boolean` | `false` | Stan błędu (czerwony border) |
| `rows`        | `number`  | `3`     | Liczba widocznych wierszy    |
| `id`          | `string`  | -       | ID dla label                 |

### Events

- `@update:modelValue` - Zmiana wartości
- `@focus` - Focus
- `@blur` - Blur

### Przykłady użycia

```vue
<!-- Podstawowy textarea -->
<BaseTextArea v-model="notes" placeholder="Dodatkowe informacje..." />

<!-- Z większą liczbą wierszy -->
<BaseTextArea v-model="description" :rows="6" placeholder="Opis produktu" />

<!-- Stan błędu -->
<BaseTextArea
    v-model="form.notes"
    :error="!!form.errors.notes"
    placeholder="Notatki"
/>
<p v-if="form.errors.notes" class="error">
    {{ form.errors.notes }}
</p>

<!-- W formularzu z label i ID -->
<div class="field">
    <label for="bio">Bio</label>
    <BaseTextArea
        id="bio"
        v-model="form.bio"
        :rows="4"
        :error="!!form.errors.bio"
        placeholder="Napisz coś o sobie..."
    />
    <p v-if="form.errors.bio" class="error">
        {{ form.errors.bio }}
    </p>
</div>
```

### Features

✏️ **Resize pionowy** - `resize: vertical` (min-height 76px)
🔵 **Focus States** - Border i shadow przy focus
❌ **Error States** - Czerwony border dla błędów
🚫 **Disabled / Readonly** - Pełne wsparcie obu stanów

---

## 📞 BaseMaskedInput

Input tekstowy z **maską wzorcową** - telefon, kod pocztowy, nr karty itp. Model to **string** (sformatowana, zamaskowana wartość).

### Tokeny maski

| Token     | Znaczenie                                               |
| --------- | ------------------------------------------------------- |
| `#`       | cyfra `[0-9]`                                           |
| `A`       | litera `[A-Za-z]`                                       |
| `*`       | znak alfanumeryczny `[A-Za-z0-9]`                       |
| inny znak | literał (wstawiany automatycznie, np. spacja, `+`, `-`) |

### Props

| Prop          | Typ       | Default      | Opis                               |
| ------------- | --------- | ------------ | ---------------------------------- |
| `modelValue`  | `string`  | `""`         | Wartość (v-model)                  |
| `mask`        | `string`  | **wymagane** | Wzorzec maski, np. `"### ### ###"` |
| `placeholder` | `string`  | `""`         | Placeholder                        |
| `disabled`    | `boolean` | `false`      | Wyłączony                          |
| `readonly`    | `boolean` | `false`      | Tylko do odczytu                   |
| `prefix`      | `string`  | -            | Tekst przed inputem                |
| `suffix`      | `string`  | -            | Tekst po inpucie                   |
| `prefixIcon`  | `string`  | -            | Ikona przed inputem                |
| `suffixIcon`  | `string`  | -            | Ikona po inpucie                   |
| `error`       | `boolean` | `false`      | Stan błędu                         |
| `id`          | `string`  | -            | ID dla label                       |

### Events

- `@update:modelValue` - Zmiana wartości (zamaskowany string)
- `@focus` - Focus
- `@blur` - Blur

### Slots

- `prefix` - Custom prefix
- `suffix` - Custom suffix

### Przykłady użycia

```vue
<!-- Telefon -->
<BaseMaskedInput v-model="phone" mask="### ### ###" placeholder="123 456 789" />

<!-- Telefon z kierunkowym -->
<BaseMaskedInput
    v-model="phone"
    mask="+48 ### ### ###"
    prefix-icon="pi pi-phone"
/>

<!-- Kod pocztowy -->
<BaseMaskedInput v-model="postcode" mask="##-###" placeholder="00-000" />

<!-- Nr karty -->
<BaseMaskedInput v-model="card" mask="#### #### #### ####" />

<!-- W formularzu z walidacją -->
<div class="field">
    <label for="phone">Telefon</label>
    <BaseMaskedInput
        id="phone"
        v-model="form.phone"
        mask="### ### ###"
        :error="!!form.errors.phone"
    />
    <p v-if="form.errors.phone" class="error">{{ form.errors.phone }}</p>
</div>
```

---

## 🎨 Styling

Wszystkie komponenty używają spójnego designu:

**Kolory:**

- Główny: `#38bdf8` (niebieski)
- Błąd: `#f87171` (czerwony)
- Sukces: `#4ade80` (zielony)
- Ostrzeżenie: `#fb923c` (pomarańczowy)
- Tło: `rgba(10, 16, 32, 0.7)`
- Border: `rgba(56, 189, 248, 0.1)`

**Stany:**

- `:focus` - niebieski border + shadow ring
- `:error` - czerwony border + shadow ring
- `:disabled` - opacity 0.5
- `:readonly` - ciemniejsze tło

**Transitions:**

- Wszystkie: `0.2s ease`
- Hover: `0.15s ease`

---

## 💡 Best Practices

### 1. Używaj v-model

```vue
<!-- ✅ Dobrze -->
<BaseInput v-model="name" />

<!-- ❌ Źle -->
<BaseInput :value="name" @input="name = $event" />
```

### 2. Łącz z walidacją

```vue
<BaseInput v-model="form.email" :error="!!form.errors.email" type="email" />
<p v-if="form.errors.email" class="error">
    {{ form.errors.email }}
</p>
```

### 3. Używaj ID dla accessibility

```vue
<label for="user-email">Email</label>
<BaseInput id="user-email" v-model="email" type="email" />
```

### 4. Prefix/Suffix dla kontekstu

```vue
<!-- Waluta -->
<BaseInputNumber v-model="price" suffix="PLN" />

<!-- Wyszukiwanie -->
<BaseInput v-model="query" prefix-icon="pi pi-search" />

<!-- URL -->
<BaseInput v-model="domain" prefix="https://" />
```

### 5. Pokazuj siłę hasła przy rejestracji

```vue
<!-- Login - bez wskaźnika -->
<BasePassword v-model="password" />

<!-- Rejestracja - ze wskaźnikiem -->
<BasePassword v-model="newPassword" :show-strength="true" />
```

---

## 🔧 Integracja z formularzami

### Laravel + Inertia

```vue
<script setup lang="ts">
import { useForm } from "@inertiajs/vue3";

const form = useForm({
    name: "",
    email: "",
    price: null,
    password: "",
});

function submit() {
    form.post("/register");
}
</script>

<template>
    <form @submit.prevent="submit">
        <div class="field">
            <label for="name">Nazwa</label>
            <BaseInput
                id="name"
                v-model="form.name"
                :error="!!form.errors.name"
            />
            <p v-if="form.errors.name" class="error">
                {{ form.errors.name }}
            </p>
        </div>

        <div class="field">
            <label for="email">Email</label>
            <BaseInput
                id="email"
                v-model="form.email"
                type="email"
                prefix-icon="pi pi-envelope"
                :error="!!form.errors.email"
            />
            <p v-if="form.errors.email" class="error">
                {{ form.errors.email }}
            </p>
        </div>

        <div class="field">
            <label for="price">Cena</label>
            <BaseInputNumber
                id="price"
                v-model="form.price"
                :min="0"
                :step="0.01"
                suffix="PLN"
                :show-buttons="true"
                :error="!!form.errors.price"
            />
            <p v-if="form.errors.price" class="error">
                {{ form.errors.price }}
            </p>
        </div>

        <div class="field">
            <label for="password">Hasło</label>
            <BasePassword
                id="password"
                v-model="form.password"
                :show-strength="true"
                :error="!!form.errors.password"
            />
            <p v-if="form.errors.password" class="error">
                {{ form.errors.password }}
            </p>
        </div>

        <button type="submit" :disabled="form.processing">
            {{ form.processing ? "Zapisywanie..." : "Zapisz" }}
        </button>
    </form>
</template>
```

---

## ✨ Przykłady zaawansowane

### Custom filter w DataTable

```vue
<BaseInput
    v-model="filters.search"
    prefix-icon="pi pi-search"
    placeholder="Szukaj płyt..."
    @update:model-value="applyFilters"
>
    <template #suffix>
        <button
            v-if="filters.search"
            @click="clearSearch"
            class="clear-btn"
        >
            <i class="pi pi-times" />
        </button>
    </template>
</BaseInput>
```

### Kalkulator rabatu

```vue
<div class="calculator">
    <BaseInputNumber
        v-model="originalPrice"
        prefix="Cena:"
        suffix="PLN"
        :show-buttons="true"
    />
    <BaseInputNumber
        v-model="discount"
        prefix-icon="pi pi-percentage"
        :min="0"
        :max="100"
        :show-buttons="true"
    />
    <BaseInputNumber
        :model-value="finalPrice"
        prefix="Razem:"
        suffix="PLN"
        readonly
    />
</div>
```

### Wyszukiwanie z auto-complete

```vue
<BaseInput
    v-model="searchQuery"
    prefix-icon="pi pi-search"
    placeholder="Szukaj artystów..."
    @update:model-value="fetchSuggestions"
>
    <template #suffix>
        <i
            v-if="loading"
            class="pi pi-spin pi-spinner"
        />
        <span v-else-if="results.length" class="badge">
            {{ results.length }}
        </span>
    </template>
</BaseInput>
```

---

## 📚 TypeScript Support

Wszystkie komponenty są w pełni typowane:

```typescript
import type { Ref } from "vue";

const name: Ref<string> = ref("");
const quantity: Ref<number | null> = ref(null);
const password: Ref<string> = ref("");

// Auto-complete działa!
```

---

## 🪟 BaseDialog

Powłoka modala (Teleport + backdrop + panel + animacja `modal-fade`) w ciemnym
stylu projektu. **Rozmiar i układ kolumn zarządzasz Tailwindem w miejscu
wywołania** - komponent nie narzuca szerokości ani siatki.

### Props

| Prop               | Typ                 | Default              | Opis                                                                        |
| ------------------ | ------------------- | -------------------- | --------------------------------------------------------------------------- |
| `visible`          | `boolean`           | -                    | `v-model:visible`                                                           |
| `title`            | `string`            | `""`                 | Tytuł w nagłówku (albo użyj slotu `header`)                                 |
| `panelClass`       | `string`            | `"w-full max-w-2xl"` | Klasy Tailwind sterujące szerokością panelu (`w-…`, `max-w-…`, breakpointy) |
| `align`            | `"top" \| "center"` | `"top"`              | Pionowe ułożenie panelu (formularze: `top`, potwierdzenia: `center`)        |
| `mobileFullscreen` | `boolean`           | `true`               | Pełny ekran na mobile (`false` dla małych dialogów potwierdzeń)             |
| `closeOnBackdrop`  | `boolean`           | `true`               | Zamknięcie po kliknięciu tła                                                |
| `showClose`        | `boolean`           | `true`               | Pokaż przycisk ×                                                            |

### Events / Slots

- `@update:visible` - zmiana widoczności (zamknięcie emituje `false`)
- Sloty: `default` (treść/body), `header` (zamiast `title`), `footer` (przyciski)
- Zamyka się na `Escape`, kliknięcie tła i `×`; blokuje scroll `body`.

### Przykłady

```vue
<!-- Formularz dwukolumnowy: kolumny przez Tailwind flex, szerokość przez panel-class -->
<BaseDialog
    :visible="show"
    title="Edytuj płytę"
    panel-class="w-11/12 max-w-5xl"
    @update:visible="
        (v) => {
            if (!v) emit('close');
        }
    "
>
    <form class="flex w-full flex-col gap-5 md:flex-row" @submit.prevent="emit('submit')">
        <section class="flex flex-1 flex-col gap-3.5 rounded-xl border border-sky-400/10 bg-slate-950/40 p-4">
            <!-- pola kolumny 1 (BaseInput / BaseDropdown / BaseInputNumber ...) -->
        </section>
        <section class="flex flex-1 flex-col gap-3.5 rounded-xl border border-sky-400/10 bg-slate-950/40 p-4">
            <!-- pola kolumny 2 -->
        </section>
    </form>

    <template #footer>
        <button type="button" @click="emit('close')">Anuluj</button>
        <button type="submit" form="...">Zapisz</button>
    </template>
</BaseDialog>

<!-- Małe okno potwierdzenia (wyśrodkowane, nie fullscreen na mobile) -->
<BaseDialog
    :visible="show"
    panel-class="w-full max-w-md"
    align="center"
    :mobile-fullscreen="false"
    :show-close="false"
    @update:visible="
        (v) => {
            if (!v) emit('cancel');
        }
    "
>
    <div class="text-center"><!-- ikona + tytuł + treść + przyciski --></div>
</BaseDialog>
```

> **Wzorzec modali w projekcie:** komponent-modal (np. `InventoryRecordModal.vue`)
> wystawia własne API (`show` / `@close` / `@submit`) i wewnątrz mapuje je na
> `BaseDialog` (`:visible="show"`, `@update:visible`). Dzięki temu strony
> (Index/Show) nie zmieniają się przy refaktorach modala.

---

## 🧩 Komponenty towarzyszące

- **`FileUpload`** - pole wyboru pliku (dropzone + chip z nazwą/rozmiarem),
  walidacja `maxFileSize`, prop `accept`, event `@select="{ files }"`,
  `defineExpose({ clearFile, openFilePicker })`. Można go ukryć i sterować
  programowo (`ref.openFilePicker()`), np. przy uploadzie okładki.
- **`DatePicker`** - owijka na PrimeVue DatePicker, ostylowana pod motyw
  (panel teleportowany do `body`, style globalne). `v-model` jako `string`
  `yyyy-mm-dd`.
- **`Tooltip`** - prosty tooltip (`content`, `position`).
- **`AppToast`** - renderer powiadomień; treść dodajesz przez composable
  `useToast()`.

---

## 🧰 Composables UI

```ts
import { useToast } from "@/composables/useToast";
import { useMoney } from "@/composables/useMoney";
import { useDate } from "@/composables/useDate";

const { success, error } = useToast(); // success("Zapisano"); error("Błąd")
const { formatPrice } = useMoney(); // formatPrice("240.00") → "240,00 zł" (PLN, null-safe)
const { formatDate } = useDate(); // formatDate("2026-06-04") → "04.06.2026" (pl-PL, null-safe)
```

`useToast` trzyma listę toastów w module-level singletonie, więc powiadomienia
przeżywają nawigację Inertia (np. toast po usunięciu pokazuje się na liście).

---

**Pytania? Sprawdź kod źródłowy komponentów w `resources/js/Components/`**
