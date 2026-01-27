Estrutura de pastas
inscricoes/
├─.github/
│  └─workflows/
│     ├─ issues.yml
│     ├─ pull-requests.yml
│     ├─ tests.yml
│     └─ update-changelog.yml
├─ app/
│  └─Http/
│     ├─Controllers/
│     │  ├─Admin/
│     │  │  ├─Registrations/
│     │  │  ├─System/
│     │  │  │   ├─ CategoryAdminController.php
│     │  │  │   ├─ EventAdminController.php
│     │  │  │   ├─ FormAdminController.php
│     │  │  │   ├─ FormFieldAdminController.php
│     │  │  │   └─ LetterTemplateController.php
│     │  │  ├─ AdminAuthController.php
│     │  │  ├─ AttendanceController.php
│     │  │  ├─ DashboardController.php
│     │  │  ├─ RegistrationAdminController.php
│     │  │  ├─ RegistrationExportController.php
│     │  │  ├─ StatsController.php
│     │  │  ├─ SyncController.php
│     │  │  └─ UserAdminController.php
│     │  ├─Api/
│     │  │  └─ api_cnpj.php
│     │  ├─Public/
│     │  │  ├─ AttendeeAccessController.php
│     │  │  ├─ AttendeeAreaController.php
│     │  │  ├─ AttendeeAuthController.php
│     │  │  ├─ EventPublicController.php
│     │  │  └─ RegistrationController.php
│     │  └─ Controller.php
│     ├─Middleware/
│     │  ├─ AdminEventAuth.php
│     │  └─ AttendeeAuth.php
│     ├─Models/
│     │  ├─ Category.php
│     │  ├─ Event.php
│     │  ├─ FieldPreset.php
│     │  ├─ Form.php
│     │  ├─ FormField.php
│     │  ├─ Organizer.php
│     │  ├─ Registration.php
│     │  ├─ RegistrationAnswer.php
│     │  └─ User.php
│     └─Providers/
│        └─ AppServiceProvider.php
├─bootstrap/
│  ├─cache/
│  │  ├─ .gitignore
│  │  ├─ packages.php
│  │  └─ services.php
│  ├─ app.php
│  └─ providers.php
├─config/
│  ├─ app.php
│  ├─ auth.php
│  ├─ cache.php
│  ├─ database.php
│  ├─ filesystems.php
│  ├─ logging.php
│  ├─ mail.php
│  ├─ queue.php
│  ├─ services.php
│  └─ session.php
├─database/
│  ├─factories/
│  │  └─ UserFactory.php
│  ├─migrations/
│  │  ├─ 0001_01_01_000000_create_users_table.php
│  │  ├─ 0001_01_01_000001_create_cache_table.php
│  │  ├─ 0001_01_01_000002_create_jobs_table.php
│  │  ├─ 2025_12_29_185819_create_organizers_table.php
│  │  ├─ 2025_12_29_185829_create_events_table.php
│  │  ├─ 2025_12_29_185835_create_categories_table.php
│  │  ├─ 2025_12_29_185839_create_forms_table.php
│  │  ├─ 2025_12_29_185843_create_form_fields_table.php
│  │  ├─ 2025_12_29_185846_create_registrations_table.php
│  │  ├─ 2025_12_29_185849_create_registration_answers_table.php
│  │  ├─ 2026_01_05_150411_add_attendee_auth_and_confirmation_to_registrations_table.php
│  │  ├─ 2026_01_05_154443_add_password_and_confirmation_to_registrations_table.php
│  │  ├─ 2026_01_05_174838_add_editable_by_attendee_to_form_fields_table.php
│  │  ├─ 2026_01_06_194818_create_field_presets_table.php
│  │  ├─ 2026_01_06_200152_alter_form_fields_add_preset_and_options.php
│  │  ├─ 2026_01_06_200320_alter_registrations_add_address_and_data.php
│  │  ├─ 2026_01_06_203850_add_deleted_at_to_form_fields.php
│  │  ├─ 2026_01_07_145145_add_description_to_events_table.php
│  │  ├─ 2026_01_07_172337_add_profile_fields_and_extras_to_registrations_table.php
│  │  ├─ 2026_01_07_174329_add_new_registration_fields_to_field_presets_table.php
│  │  └─ 2026_01_07_200001_add_visibility_and_banner_to_categories_table.php
│  ├─seeders/
│  │  ├─ DatabaseSeeder.php
│  │  ├─ DemoSeeder.php
│  │  └─ FieldPresetSeeder.php
│  ├─ .gitignore
│  └─ database.sqlite
├─public/
│  ├─storage/
│  │  ├─demo2025
│  │  │  ├─banner/
│  │  │  │   └─ Aqui ficam salvos os banners por evento
│  │  │  └─categorias/
│  │  │      └─Aqui ficam salvos as pastas dos banners separados por categorias/
│  │  │           └─ Aqui ficam salvos os banners por categoria
│  │  └─ .gitnore 
│  ├─ .htaccess
│  ├─ favicon.ico
│  ├─ index.php
│  └─ robots.txt
├─resources/
│  ├─css/
│  │  └─ app.css
│  ├─js/
│  │  ├─ app.js
│  │  └─ bootstrap.js
│  └─views/
│     ├─admin/
│     │  ├─attendance/
│     │  │  └─ index.blade.php
│     │  ├─auth/
│     │  │  └─ login.blade.php
│     │  ├─event/
│     │  │  ├─ edit.blade.php
│     │  │  └─ index.blade.php
│     │  ├─inscricoes/
│     │  │  └─exports/
│     │  │      ├─ by_category.blade.php
│     │  │      ├─ by_status.blade.php
│     │  │      └─ index.blade.php
│     │  ├─layouts/
│     │  │  └─ app.blade.php
│     │  ├─partials/
│     │  │  └─ flash.blade.php
│     │  ├─registrations/
│     │  │  ├─ edit.blade.php
│     │  │  ├─ index.blade.php
│     │  │  ├─ search.blade.php
│     │  │  └─ show.blade.php
│     │  ├─stats/
│     │  │  └─ index.blade.php
│     │  ├─sync/
│     │  │  └─ index.blade.php
│     │  ├─system/
│     │  │  ├─categories/
│     │  │  │  ├─ create.blade.php
│     │  │  │  ├─ edit.blade.php
│     │  │  │  └─ index.blade.php
│     │  │  ├─form_fields/
│     │  │  │   ├─partials/
│     │  │  │   │   ├─ form.blade.php
│     │  │  │   │   └─ preview.blade.php
│     │  │  │   ├─ create.blade.php
│     │  │  │   ├─ edit.blade.php
│     │  │  │   └─ index.blade.php
│     │  │  ├─forms/
│     │  │  │  ├─ create.blade.php
│     │  │  │  ├─ edit.blade.php
│     │  │  │  └─ index.blade.php
│     │  │  └─letters/
│     │  │     └─ edit.blade.php
│     │  ├─users/
│     │  │  ├─ create.blade.php
│     │  │  └─ index.blade.php
│     │  └─ dashboard.blade.php
│     ├─emails/
│     │  └─ registration_confirmation.blade.php
│     ├─public/
│     │  ├─attendee/
│     │  │   ├─ area.blade.php
│     │  │   ├─ edit.blade.php
│     │  │   ├─ forgot.blade.php
│     │  │   ├─ letter.blade.php
│     │  │   ├─ login.blade.php
│     │  │   ├─ request.blade.php
│     │  │   ├─ reset.blade.php
│     │  │   └─ show.blade.php
│     │  ├─event/
│     │  │   ├─ landing.blade.php
│     │  │   └─ show.blade.php
│     │  └─registration/
│     │      └─ form.blade.php
│     └─ welcome.blade.php
├─routes/
│  ├─ console.php
│  └─ web.php
├─storage/
│  ├─app/
│  │  ├─private/
│  │  │  └─ .gitignore
│  │  ├─public/
│  │  ├─demo2025
│  │  │  ├─banner/
│  │  │  │   └─ Aqui ficam salvos os banners por evento
│  │  │  └─categorias/
│  │  │      └─Aqui ficam salvos as pastas dos banners separados por categorias/
│  │  │           └─ Aqui ficam salvos os banners por categoria
│  │  │  └─ .gitignore
│  │  └─ .gitignore
│  ├─framework/
│  │  ├─cache/
│  │  │  ├─data/
│  │  │  │  └─ .gitignore
│  │  │  └─ .gitignore
│  │  ├─sessions/
│  │  │  └─ .gitignore
│  │  ├─testing/
│  │  │  └─ .gitignore
│  │  ├─views/
│  │  │  ├─ .gitignore
│  │  │  └─ {{nome_das_views_criptografadas}}.php
│  │  └─.gitignore
│  └─logs/
│     ├─ .gitignore
│     └─ laravel.log
├─tests/
│  ├─Feature/
│  │  └─ ExampleTest.php
│  ├─Unit/
│  │  └─ ExampleTest.php
│  └─ TestCase.php
├─vendor/
│  ├─bin/
│  ├─brick/
│  ├─carbonphp/
│  ├─composer/
│  ├─dflydev/
│  ├─doctrine/
│  ├─dragonmantank/
│  ├─egulias/
│  ├─fakerphp/
│  ├─filp/
│  ├─fruitcake/
│  ├─graham-campbell/
│  ├─guzzlehttp/
│  ├─hamcrest/
│  ├─laravel/
│  ├─league/
│  ├─mockery/
│  ├─monolog/
│  ├─myclabs/
│  ├─nesbot/
│  ├─nette/
│  ├─nikic/
│  ├─nunomaduro/
│  ├─phar-io/
│  ├─phpoption/
│  ├─phpunit/
│  ├─psr/
│  ├─psy/
│  ├─ralouphie/
│  ├─ramsey/
│  ├─sebastian/
│  ├─staabm/
│  ├─symfony/
│  ├─theseer/
│  ├─tijsverkoyen/
│  ├─vlucas/
│  ├─voku/
│  └─ autoload.php
├─ .editorconfig
├─ .env
├─ .env.example
├─ .gitattributes
├─ .gitignore
├─ .styleci.yml
├─ artisan
├─ CHANGELOG.md
├─ composer.json
├─ composer.lock
├─ package.json
├─ phpunit.xml
├─ README.md
├─ strutured_real.md 
├─ strutured_real - Sistema de inscrição sem pagamento.md (esse arquivo)
└─ vite.config.js








estutura sql
└─ ainda em desenvolvimento
