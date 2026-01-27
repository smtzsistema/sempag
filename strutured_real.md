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
│     │  │  ├─ AdminAuthController.php
│     │  │  ├─ DashboardController.php
│     │  │  └─ RegistrationAdminController.php
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
│     └─Models/
│        ├─ Category.php
│        ├─ Event.php
│        ├─ Form.php
│        ├─ FormField.php
│        ├─ Organizer.php
│        ├─ Registration.php
│        ├─ RegistrationAnswer.php
│        ├─ User.php
│        ├─Providers/
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
│  │  └─ 2026_01_05_174838_add_editable_by_attendee_to_form_fields_table.php
│  ├─seeders/
│  │  ├─ DatabaseSeeder.php
│  │  └─ DemoSeeder.php
│  ├─ .gitignore
│  └─ database.sqlite
├─public/
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
│     │  ├─auth/
│     │  │  └─ login.blade.php
│     │  │  ├─registrations/
│     │  │  │  ├─ index.blade.php
│     │  │  │  └─ show.blade.php
│     │  │  └─ dashboard.blade.php
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
│  │  │  ├─ 48cabacea05c7773bbf9b046b0ac906e.php
│  │  │  ├─ 52447ca9d460e175c164d071f420019b.php
│  │  │  ├─ a5b68fa7647190f56e3e6435719d6520.php
│  │  │  ├─ b9da71d19480ab9a9e2c338068d7d028.php
│  │  │  └─ dd47164ffd771e988d4bb4a1d083d26d.php
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
├─ strutured_real.md (esse arquivo)
└─ vite.config.js








estutura sql
└─ ainda em desenvolvimento