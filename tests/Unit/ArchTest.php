<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Presets
|--------------------------------------------------------------------------
*/

arch()->preset()->php();

// Weak crypto / randomness guard. VerifyEmailController intentionally uses
// sha1 to match Laravel's signed e-mail verification hash, so it is exempt.
arch()->preset()->security()
    ->ignoring('App\Http\Controllers\Tenant\Auth\VerifyEmailController');

/*
|--------------------------------------------------------------------------
| Global conventions
|--------------------------------------------------------------------------
*/

arch('every class declares strict types')
    ->expect('App')
    ->toUseStrictTypes();

arch('no debugging helpers are left behind')
    ->expect(['dd', 'dump', 'ray', 'var_dump', 'vd', 'die', 'exit'])
    ->not->toBeUsed();

arch('env() is only read from configuration files')
    ->expect('env')
    ->not->toBeUsed();

/*
|--------------------------------------------------------------------------
| Enums
|--------------------------------------------------------------------------
*/

arch('enums live in the Enums namespace')
    ->expect('App\Enums')
    ->toBeEnums();

/*
|--------------------------------------------------------------------------
| Models
|--------------------------------------------------------------------------
*/

arch('models are final classes')
    ->expect('App\Models')
    ->toBeClasses()
    ->toBeFinal();

arch('models are only used outside the HTTP request layer entry points')
    ->expect('App\Models')
    ->toOnlyBeUsedIn([
        'App\Models',
        'App\Http\Controllers',
        'App\Http\Requests',
        'App\Http\Middleware',
        'App\Http\Resources',
        'App\Services',
        'App\Transformers',
        'App\Jobs',
        'App\Mail',
        'App\Rules',
        'App\Policies',
        'App\Observers',
        'App\Providers',
        'App\Imports',
        'App\Exports',
        'App\Console',
        'Database\Factories',
        'Database\Seeders',
    ]);

/*
|--------------------------------------------------------------------------
| Controllers
|--------------------------------------------------------------------------
*/

arch('controllers')
    ->expect('App\Http\Controllers')
    ->toHaveSuffix('Controller')
    ->toExtend('App\Http\Controllers\Controller')
    ->toBeFinal()
    ->ignoring('App\Http\Controllers\Controller');

arch('controllers do not depend on the request facade')
    ->expect('Illuminate\Support\Facades\Request')
    ->not->toBeUsed();

/*
|--------------------------------------------------------------------------
| Form requests
|--------------------------------------------------------------------------
*/

arch('form requests')
    ->expect('App\Http\Requests')
    ->toHaveSuffix('Request')
    ->toExtend('Illuminate\Foundation\Http\FormRequest')
    ->toBeFinal();

/*
|--------------------------------------------------------------------------
| Middleware
|--------------------------------------------------------------------------
*/

arch('middleware are final')
    ->expect('App\Http\Middleware')
    ->toBeFinal();

/*
|--------------------------------------------------------------------------
| Services
|--------------------------------------------------------------------------
*/

arch('services')
    ->expect('App\Services')
    ->toHaveSuffix('Service')
    ->toBeFinal()
    ->ignoring('App\Services\BaseService');

/*
|--------------------------------------------------------------------------
| Transformers
|--------------------------------------------------------------------------
*/

arch('transformers')
    ->expect('App\Transformers')
    ->toHaveSuffix('Transformer')
    ->toBeFinal()
    ->ignoring('App\Transformers\Transformer');

/*
|--------------------------------------------------------------------------
| Jobs & Mail
|--------------------------------------------------------------------------
*/

arch('jobs are final')
    ->expect('App\Jobs')
    ->toBeFinal();

arch('mailables')
    ->expect('App\Mail')
    ->toHaveSuffix('Mail')
    ->toBeFinal();
