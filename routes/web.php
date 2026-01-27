<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Public\EventPublicController;
use App\Http\Controllers\Public\RegistrationController;
use App\Http\Controllers\Public\AttendeeAccessController;
use App\Http\Controllers\Public\AttendeeAuthController;
use App\Http\Controllers\Public\AttendeeAreaController;
use App\Http\Controllers\Public\AttendeeCredentialController;

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\RegistrationAdminController;
use App\Http\Controllers\Admin\RegistrationExportController;
use App\Http\Controllers\Admin\StatsController;
use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\SyncController;
use App\Http\Controllers\Admin\UserAdminController;

use App\Http\Controllers\Admin\System\CategoryAdminController;
use App\Http\Controllers\Admin\System\EventAdminController;
use App\Http\Controllers\Admin\System\FormAdminController;
use App\Http\Controllers\Admin\System\FormFieldAdminController;
use App\Http\Controllers\Admin\System\LetterTemplateController;
use App\Http\Controllers\Admin\System\LettersAdminController;
use App\Http\Controllers\Admin\System\RoleAdminController;

use App\Http\Middleware\AdminEventAuth;


// Landing (botões: Nova inscrição / Já sou inscrito)
Route::get('/e/{event}', [EventPublicController::class, 'landing'])
    ->name('public.event.landing');

// Lista categorias
Route::get('/e/{event}/categorias', [EventPublicController::class, 'show'])
    ->name('public.event.show');

// Form inscrição por categoria
Route::get('/e/{event}/c/{category}', [RegistrationController::class, 'create'])
    ->name('public.registration.create');

Route::post('/e/{event}/c/{category}', [RegistrationController::class, 'store'])
    ->name('public.registration.store');


// ======================
// INSCRITO (registrations)
// ======================

// Login do inscrito (email + senha)
Route::get('/e/{event}/ja-sou-inscrito', [AttendeeAuthController::class, 'loginForm'])
    ->name('public.attendee.login');

Route::post('/e/{event}/ja-sou-inscrito', [AttendeeAuthController::class, 'login'])
    ->name('public.attendee.login.post');

Route::post('/e/{event}/sair', [AttendeeAuthController::class, 'logout'])
    ->name('public.attendee.logout');


// Esqueci minha senha
Route::get('/e/{event}/esqueci-minha-senha', [AttendeeAccessController::class, 'form'])
    ->name('public.attendee.forgot');

Route::post('/e/{event}/esqueci-minha-senha', [AttendeeAccessController::class, 'send'])
    ->name('public.attendee.forgot.send');

// Reset
Route::get('/e/{event}/redefinir-senha/{registration:ins_token}', [AttendeeAccessController::class, 'resetForm'])
    ->middleware('signed')
    ->name('public.attendee.reset.form');

Route::post('/e/{event}/redefinir-senha/{registration:ins_token}', [AttendeeAccessController::class, 'resetStore'])
    ->middleware('signed')
    ->name('public.attendee.reset.store');


// Link mágico assinado
Route::get('/e/{event}/r/{registration}', [AttendeeAccessController::class, 'magic'])
    ->middleware('signed')
    ->name('public.attendee.magic');


// Área do inscrito (protegida)
Route::middleware('attendee')->group(function () {

    Route::get('/e/{event}/minha-area', [AttendeeAreaController::class, 'index'])
        ->name('public.attendee.area');

    Route::get('/e/{event}/minha-area/carta', [AttendeeAreaController::class, 'letter'])
        ->name('public.attendee.letter');

    Route::get('/e/{event}/minha-area/editar', [AttendeeAreaController::class, 'edit'])
        ->name('public.attendee.edit');

    Route::post('/e/{event}/minha-area/editar', [AttendeeAreaController::class, 'update'])
        ->name('public.attendee.update');

    // Credenciais do inscrito
    Route::get('/e/{event}/minha-area/credencial', [AttendeeCredentialController::class, 'entry'])
        ->name('public.attendee.credential.entry');

    Route::get('/e/{event}/minha-area/credenciais', [AttendeeCredentialController::class, 'choose'])
        ->name('public.attendee.credentials.choose');

    Route::get('/e/{event}/minha-area/credenciais/{credential:cre_id}', [AttendeeCredentialController::class, 'print'])
        ->name('public.attendee.credentials.print');

});


// ======================
// ADMIN (protegido)
// ======================
Route::prefix('/e/{event}/admin')->name('admin.')->group(function () {

    Route::get('/login', [AdminAuthController::class, 'loginForm'])
        ->name('login');

    Route::post('/login', [AdminAuthController::class, 'login'])
        ->name('login.post');

    // Área protegida (sem link no público)
    Route::middleware([AdminEventAuth::class])->group(function () {

        Route::get('/', [DashboardController::class, 'index'])
            //->middleware('permission:dashboard.view')
            ->name('dashboard');

        Route::post('/sair', [AdminAuthController::class, 'logout'])
            ->name('logout');

        // --------------------------
        // INSCRIÇÕES
        // --------------------------
        Route::get('/inscricoes', [RegistrationAdminController::class, 'index'])
            ->middleware('permission:registrations.view')
            ->name('registrations.index');

        Route::get('/inscricoes/export-modal', [RegistrationExportController::class, 'modal'])
            ->middleware('permission:registrations.export')
            ->name('registrations.export_modal');

        Route::get('/inscricoes/busca', [RegistrationAdminController::class, 'search'])
            ->middleware('permission:registrations.view')
            ->name('registrations.search');

        Route::get('/inscricoes/{registration:ins_token}', [RegistrationAdminController::class, 'show'])
            ->middleware('permission:registrations.view')
            ->name('registrations.show');

        Route::get('/inscricoes/{registration:ins_token}/editar', [RegistrationAdminController::class, 'edit'])
            ->middleware('permission:registrations.edit')
            ->name('registrations.edit');

        Route::post('/inscricoes/{registration:ins_token}/editar', [RegistrationAdminController::class, 'update'])
            ->middleware('permission:registrations.edit')
            ->name('registrations.update');

        Route::post('/inscricoes/{registration:ins_token}/excluir', [RegistrationAdminController::class, 'destroy'])
            ->middleware('permission:registrations.delete')
            ->name('registrations.destroy');

        Route::post('/inscricoes/{registration:ins_token}/aprovar', [RegistrationAdminController::class, 'approve'])
            ->middleware('permission:registrations.approve')
            ->name('registrations.approve');

        Route::post('/inscricoes/{registration:ins_token}/reprovar', [RegistrationAdminController::class, 'reject'])
            ->middleware('permission:registrations.approve')
            ->name('registrations.reject');

        // Exportações
        Route::prefix('/inscricoes/exportar')->name('registrations.exports.')->group(function () {

            Route::get('/', [RegistrationExportController::class, 'index'])
                ->middleware('permission:registrations.export')
                ->name('index');

            // Export com filtros (multi-seleção)
            Route::get('/filtros', [RegistrationExportController::class, 'filteredForm'])
                ->middleware('permission:registrations.export')
                ->name('filteredForm');

            Route::get('/filtros/csv', [RegistrationExportController::class, 'filtered'])
                ->middleware('permission:registrations.export')
                ->name('filtered');

            Route::get('/todas', [RegistrationExportController::class, 'all'])
                ->middleware('permission:registrations.export')
                ->name('all');

            Route::get('/por-categoria', [RegistrationExportController::class, 'byCategoryForm'])
                ->middleware('permission:registrations.export')
                ->name('byCategoryForm');

            Route::get('/por-categoria/csv', [RegistrationExportController::class, 'byCategory'])
                ->middleware('permission:registrations.export')
                ->name('byCategory');

            Route::get('/por-status', [RegistrationExportController::class, 'byStatusForm'])
                ->middleware('permission:registrations.export')
                ->name('byStatusForm');

            Route::get('/por-status/csv', [RegistrationExportController::class, 'byStatus'])
                ->middleware('permission:registrations.export')
                ->name('byStatus');
        });

        // --------------------------
        // CONFIGURAÇÕES DE SISTEMA
        // --------------------------
        Route::prefix('/sistema')->name('system.')->middleware('permission:system.manage')->group(function () {

            // Evento
            Route::get('/event', [EventAdminController::class, 'index'])
                ->name('event.index');

            Route::get('/event/editar', [EventAdminController::class, 'edit'])
                ->name('event.edit');

            Route::post('/event', [EventAdminController::class, 'update'])
                ->name('event.update');

            // Categorias
            Route::get('/categorias', [CategoryAdminController::class, 'index'])
                ->name('categories.index');

            Route::get('/categorias/nova', [CategoryAdminController::class, 'create'])
                ->name('categories.create');

            Route::post('/categorias/nova', [CategoryAdminController::class, 'store'])
                ->name('categories.store');

            Route::get('/categorias/{category}/editar', [CategoryAdminController::class, 'edit'])
                ->name('categories.edit');

            Route::post('/categorias/{category}/editar', [CategoryAdminController::class, 'update'])
                ->name('categories.update');

            // Fichas
            Route::get('/fichas', [FormAdminController::class, 'index'])
                ->middleware('permission:system.manage')
                ->name('forms.index');

            Route::get('/fichas/nova', [FormAdminController::class, 'create'])
                ->middleware('permission:system.manage')
                ->name('forms.create');

            Route::post('/fichas/nova', [FormAdminController::class, 'store'])
                ->middleware('permission:system.manage')
                ->name('forms.store');

            Route::get('/fichas/{form}/editar', [FormAdminController::class, 'edit'])
                ->middleware('permission:system.manage')
                ->name('forms.edit');

            Route::post('/fichas/{form}/editar', [FormAdminController::class, 'update'])
                ->middleware('permission:system.manage')
                ->name('forms.update');

            // Campos da ficha
            Route::get('/fichas/{form}/campos', [FormFieldAdminController::class, 'index'])
                ->middleware('permission:system.manage')
                ->name('forms.fields.index');

            Route::get('/fichas/{form}/campos/novo', [FormFieldAdminController::class, 'create'])
                ->middleware('permission:system.manage')
                ->name('forms.fields.create');

            Route::post('/fichas/{form}/campos/novo', [FormFieldAdminController::class, 'store'])
                ->middleware('permission:system.manage')
                ->name('forms.fields.store');

            Route::get('/fichas/{form}/campos/{field}/editar', [FormFieldAdminController::class, 'edit'])
                ->middleware('permission:system.manage')
                ->name('forms.fields.edit');

            Route::post('/fichas/{form}/campos/{field}/editar', [FormFieldAdminController::class, 'update'])
                ->middleware('permission:system.manage')
                ->name('forms.fields.update');

            Route::post('/fichas/{form}/campos/preset/{preset}', [FormFieldAdminController::class, 'addPreset'])
                ->middleware('permission:system.manage')
                ->name('forms.fields.addPreset');

            Route::delete('/fichas/{form}/campos/{field}', [FormFieldAdminController::class, 'destroy'])
                ->middleware('permission:system.manage')
                ->name('forms.fields.destroy');

            Route::post('/fichas/{form}/campos/reorder', [FormFieldAdminController::class, 'reorder'])
                ->middleware('permission:system.manage')
                ->name('forms.fields.reorder');

            // Cartas de confirmação (módulo)
            Route::get('/cartas', [LettersAdminController::class, 'index'])
                ->middleware('permission:system.manage')
                ->name('letters.index');

            Route::get('/cartas/nova', [LettersAdminController::class, 'create'])
                ->middleware('permission:system.manage')
                ->name('letters.create');

            Route::post('/cartas/nova', [LettersAdminController::class, 'store'])
                ->middleware('permission:system.manage')
                ->name('letters.store');

            Route::get('/cartas/{letter}/editar', [LettersAdminController::class, 'edit'])
                ->middleware('permission:system.manage')
                ->name('letters.edit');

            Route::post('/cartas/{letter}/editar', [LettersAdminController::class, 'update'])
                ->middleware('permission:system.manage')
                ->name('letters.update');

            Route::post('/cartas/{letter}/excluir', [LettersAdminController::class, 'destroy'])
                ->middleware('permission:system.manage')
                ->name('letters.destroy');

            // Credenciais (módulo)
            Route::get('/credenciais', [\App\Http\Controllers\Admin\System\CredentialsAdminController::class, 'index'])
                ->middleware('permission:system.manage')
                ->name('credentials.index');

            Route::get('/credenciais/nova', [\App\Http\Controllers\Admin\System\CredentialsAdminController::class, 'create'])
                ->middleware('permission:system.manage')
                ->name('credentials.create');

            // A4 (ativo)
            Route::get('/credenciais/nova/a4', [\App\Http\Controllers\Admin\System\CredentialsAdminController::class, 'createA4'])
                ->middleware('permission:system.manage')
                ->name('credentials.createA4');

            Route::post('/credenciais/nova/a4', [\App\Http\Controllers\Admin\System\CredentialsAdminController::class, 'storeA4'])
                ->middleware('permission:system.manage')
                ->name('credentials.storeA4');

            Route::get('/credenciais/{credential}/editar', [\App\Http\Controllers\Admin\System\CredentialsAdminController::class, 'edit'])
                ->middleware('permission:system.manage')
                ->name('credentials.edit');

            Route::post('/credenciais/{credential}/editar', [\App\Http\Controllers\Admin\System\CredentialsAdminController::class, 'updateA4'])
                ->middleware('permission:system.manage')
                ->name('credentials.update');

            Route::post('/credenciais/{credential}/excluir', [\App\Http\Controllers\Admin\System\CredentialsAdminController::class, 'destroy'])
                ->middleware('permission:system.manage')
                ->name('credentials.destroy');


            Route::prefix('/grupos')->name('roles.')->group(function () {

                Route::get('/', [RoleAdminController::class, 'index'])
                    ->name('index');

                Route::get('/novo', [RoleAdminController::class, 'create'])
                    ->name('create');

                Route::post('/novo', [RoleAdminController::class, 'store'])
                    ->name('store');

                Route::get('/{role}/editar', [RoleAdminController::class, 'edit'])
                    ->name('edit');

                Route::post('/{role}/editar', [RoleAdminController::class, 'update'])
                    ->name('update');

                Route::post('/{role}/excluir', [RoleAdminController::class, 'destroy'])
                    ->name('destroy');
            });
        });
        // --------------------------
        // OUTROS
        // --------------------------
        Route::get('/estatisticas', [StatsController::class, 'index'])
            ->middleware('permission:stats.view')
            ->name('stats.index');

        Route::get('/presenca', [AttendanceController::class, 'index'])
            ->middleware('permission:registrations.salas')
            ->name('attendance.index');

        Route::get('/sync', [SyncController::class, 'index'])
            ->middleware('permission:sync.manage')
            ->name('sync.index');

        // --------------------------
        // USUÁRIOS
        // --------------------------
        Route::get('/usuarios', [UserAdminController::class, 'index'])
            ->middleware('permission:users.manage')
            ->name('users.index');

        Route::get('/usuarios/novo', [UserAdminController::class, 'create'])
            ->middleware('permission:users.manage')
            ->name('users.create');

        Route::post('/usuarios', [UserAdminController::class, 'store'])
            ->middleware('permission:users.manage')
            ->name('users.store');

        Route::get('/usuarios/{user}/editar', [UserAdminController::class, 'edit'])
            ->middleware('permission:users.manage')
            ->name('users.edit');

        Route::post('/usuarios/{user}/editar', [UserAdminController::class, 'update'])
            ->middleware('permission:users.manage')
            ->name('users.update');

        Route::post('/usuarios/{user}/reset-senha', [UserAdminController::class, 'resetPassword'])
            ->middleware('permission:users.manage')
            ->name('users.reset_password');
    });
});
