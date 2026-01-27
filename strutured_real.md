Estrutura de pastas
sempag/
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
│     │  │  ├─System/
│     │  │  │   ├─ CategoryAdminController.php
│     │  │  │   ├─ CredentialsAdminController.php
│     │  │  │   ├─ EventAdminController.php
│     │  │  │   ├─ FormAdminController.php
│     │  │  │   ├─ FormFieldAdminController.php
│     │  │  │   ├─ LettersAdminController.php
│     │  │  │   ├─ LetterTemplateController.php
│     │  │  │   └─ RoleAdminController.php
│     │  │  ├─ AdminAuthController.php
│     │  │  ├─ AttendanceController.php
│     │  │  ├─ DashboardController.php
│     │  │  ├─ RegistrationAdminController.php
│     │  │  ├─ RegistrationExportController.php
│     │  │  ├─ StatsController.php
│     │  │  ├─ SyncController.php
│     │  │  └─ UserAdminController.php
│     │  ├─Api/
│     │  │  ├─ api_cnpj.php
│     │  │  └─ CnpjController.php
│     │  ├─Public/
│     │  │  ├─ AttendeeAccessController.php
│     │  │  ├─ AttendeeAreaController.php
│     │  │  ├─ AttendeeAuthController.php
│     │  │  ├─ AttendeeCredentialController.php
│     │  │  ├─ EventPublicController.php
│     │  │  └─ RegistrationController.php
│     │  └─ Controller.php
│     ├─Middleware/
│     │  ├─ AdminEventAuth.php
│     │  └─ AttendeeAuth.php
│     ├─Models/
│     │  ├─ Category.php
│     │  ├─ Credential.php
│     │  ├─ Event.php
│     │  ├─ FieldPreset.php
│     │  ├─ Form.php
│     │  ├─ FormField.php
│     │  ├─ Letter.php
│     │  ├─ Organizer.php
│     │  ├─ Permission.php
│     │  ├─ Registration.php
│     │  ├─ RegistrationAnswer.php
│     │  ├─ RegistrationLog.php
│     │  ├─ Role.php
│     │  └─ User.php
│     ├─Providers/
│     │  └─ AppServiceProvider.php
│     └─Support/
│        ├─ helpers.php
│        └─ RegistrationAudit.php
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
│  ├─ permission.php
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
│  │  ├─ 2025_12_29_185842_create_field_presets_table.php
│  │  ├─ 2025_12_29_185843_create_form_fields_table.php
│  │  ├─ 2025_12_29_185846_create_registrations_table.php
│  │  ├─ 2025_12_29_185849_create_registration_answers_table.php
│  │  ├─ 2026_01_05_150411_add_attendee_auth_and_confirmation_to_registrations_table.php
│  │  ├─ 2026_01_09_170704_add_fic_validacoes_to_tbl_ficha_campos_table.php
│  │  ├─ 2026_01_19_170000_create_registration_logs_table.php
│  │  ├─ 2026_01_20_220000_create_letters_table.php
│  │  ├─ 2026_01_23_000000_create_permission_tables.php
│  │  ├─ 2026_01_23_000010_create_event_users_table.php
│  │  ├─ 2026_01_23_170123_add_meta_to_permissions_table.php
│  │  └─ 2026_01_26_160000_create_credentials_table.php
│  ├─seeders/
│  │  ├─ CredentialDefaultSeeder.php
│  │  ├─ DatabaseSeeder.php
│  │  ├─ DemoSeeder.php
│  │  ├─ FieldPresetSeeder.php
│  │  ├─ LetterDefaultSeeder.php
│  │  ├─ RbacSeeder.php
│  │  └─ StartAssetsSeeder.php
│  └─ .gitignore
├─public/
│  ├─storage/
│  │  ├─Aqui ficam salvo com o token por evento (demo2025)
│  │  │  ├─banner/
│  │  │  │   └─ Aqui ficam salvos os banners por evento
│  │  │  ├─categorias/
│  │  │  │   └─Aqui ficam salvos as pastas dos banners separados por categorias/
│  │  │  │        └─ Aqui ficam salvos os banners por categoria
│  │  │  └─credenciais/
│  │  │      └─Aqui ficam salvos as credencias
│  │  ├─ .gitnore 
│  │  └─ .gitkeep
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
│  ├─start/
│  │  └─demo2025/
│  │     ├─banner/
│  │     │  ├─ mmm0nwZfmJ83AhlScz3JmT26T3oeiP9IjIjEfY8W.png
│  │     │  └─ zUJfTv2NGMouVKxrLPWqNWOjO5vkcq9I8GUChVG0.png
│  │     ├─categorias/
│  │     │  └─1/
│  │     │      └─banner/
│  │     │          └─ DZea9fU9g3dI5thTFabFd4cigKQsvaBRDlHcUzlH.png
│  │     └─credenciais/
│  │        └─ uhEp5nvONKxk26Vrn1TXAnMMUSUcSbhfhBeWF3eY.jpg
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
│     │  │      ├─ filtered.blade.php
│     │  │      └─ index.blade.php
│     │  ├─layouts/
│     │  │  └─ app.blade.php
│     │  ├─partials/
│     │  │  └─ flash.blade.php
│     │  ├─registrations/
│     │  │  ├─ edit.blade.php
│     │  │  ├─ export_modal.blade.php
│     │  │  ├─ index.blade.php
│     │  │  ├─ search.blade.php
│     │  │  └─ show.blade.php
│     │  ├─roles/
│     │  │  ├─ form.blade.php
│     │  │  └─ index.blade.php
│     │  ├─stats/
│     │  │  └─ index.blade.php
│     │  ├─sync/
│     │  │  └─ index.blade.php
│     │  ├─system/
│     │  │  ├─categories/
│     │  │  │  ├─ create.blade.php
│     │  │  │  ├─ edit.blade.php
│     │  │  │  └─ index.blade.php
│     │  │  ├─credentials/
│     │  │  │  ├─ a4.blade.php
│     │  │  │  ├─ create.blade.php
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
│     │  │  ├─letters/
│     │  │  │   ├─partials/
│     │  │  │   │   └─ form.blade.php
│     │  │  │   ├─ create.blade.php
│     │  │  │   ├─ edit.blade.php
│     │  │  │   └─ index.blade.php
│     │  │  ├─users/
│     │  │  ├─ create.blade.php
│     │  │  ├─ form.blade.php
│     │  │  └─ index.blade.php
│     │  └─ dashboard.blade.php
│     ├─emails/
│     │  └─ registration_confirmation.blade.php
│     ├─public/
│     │  ├─attendee/
│     │  │   ├─credentials/
│     │  │   │  ├─ choose.blade.php
│     │  │   │  └─ print.blade.php
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
│  ├─ api.php
│  ├─ console.php
│  └─ web.php
├─storage/
│  ├─app/
│  │  ├─public/
│  │  ├─demo2025
│  │  │  ├─banner/
│  │  │  │   └─ Aqui ficam salvos os banners por evento
│  │  │  ├─categorias/
│  │  │  │    └─Aqui ficam salvos as pastas dos banners separados por categorias/
│  │  │  │         └─ Aqui ficam salvos os banners por categoria
│  │  │  └─credenciais/
│  │  │     └─ Aqui ficam salvas as credenciais por evento
│  │  ├─ .gitignore
│  │  └─ .gitkeep
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
│     └─ .gitignore
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
│  ├─spatie/
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
├─ README.txt
├─ strutured_real.md
└─ vite.config.js











estutura sql
└─ ainda em desenvolvimento
