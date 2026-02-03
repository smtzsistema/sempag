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
│     │  ├─ Presence.php
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
│  │  ├─ 2026_01_26_160000_create_credentials_table.php
│  │  ├─ 2026_01_27_000000_create_presenca_table.php
│  │  ├─ 2026_02_02_000010_add_form_foto_to_forms_table.php
│  │  └─ 2026_02_02_000020_create_gallery_table.php
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
│     │  │   ├─ photo.blade.php
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
sempag
├─ cache
│  ├─ key
│  ├─ value
│  └─ expiration
├─ cache_locks
│  ├─ key
│  ├─ owner
│  └─ expiration
├─ failed_jobs
│  ├─ id
│  ├─ uuid
│  ├─ connection
│  ├─ queue
│  ├─ payload
│  ├─ exception
│  └─ failed_at
├─ jobs
│  ├─ id
│  ├─ queue
│  ├─ payload
│  ├─ attempts
│  ├─ reserved_at
│  ├─ available_at
│  └─ created_at
├─ jobs_batches
│  ├─ id
│  ├─ name
│  ├─ total_jobs
│  ├─ pending_jobs
│  ├─ failed_jobs
│  ├─ failed_job_ids
│  ├─ options
│  ├─ cancelled_at
│  ├─ created_at
│  └─ finished_at
├─ migrations
│  ├─ id
│  ├─ migration
│  └─ batch
├─ password_reset_tokens
│  ├─ email
│  ├─ token
│  └─ created_at
├─ tbl_cartas
│  ├─ car_id
│  ├─ eve_id
│  ├─ car_descricao
│  ├─ car_assunto
│  ├─ car_texto
│  ├─ cat_id
│  ├─ car_copiac
│  ├─ car_responderto
│  ├─ car_copia
│  ├─ car_tipo
│  ├─ car_trad
│  ├─ created_at
│  └─ updated_at
├─ tbl_categorias
│  ├─ cat_id
│  ├─ eve_id
│  ├─ cat_nome
│  ├─ cat_descricao
│  ├─ cat_date_start
│  ├─ cat_date_end
│  ├─ cat_banner_path
│  ├─ cat_ativo
│  ├─ cat_aprova
│  ├─ cat_settings
│  ├─ created_at
│  └─ updated_at
├─ tbl_credencial
│  ├─ cre_id
│  ├─ eve_id
│  ├─ cre_nome
│  ├─ cre_tipo
│  ├─ cat_id
│  ├─ cre_fundo
│  ├─ cre_espelhar
│  ├─ cre_config
│  ├─ created_at
│  └─ updated_at
├─ tbl_eventos
│  ├─ eve_id
│  ├─ org_id
│  ├─ eve_nome
│  ├─ eve_slug
│  ├─ eve_descricao
│  ├─ eve_token
│  ├─ eve_data_inicio
│  ├─ eve_data_fim
│  ├─ eve_local
│  ├─ eve_banner
│  ├─ eve_fundo
│  ├─ created_at
│  └─ updated_at
├─ tbl_eventos_usuarios
│  ├─ eusu_id
│  ├─ eve_id
│  ├─ usu_id
│  ├─ created_at
│  └─ updated_at
├─ tbl_ficha
│  ├─ fic_id
│  ├─ form_id
│  ├─ fic_nome
│  ├─ fic_label
│  ├─ fic_tipo
│  ├─ fic_obrigatorio
│  ├─ fic_ordem
│  ├─ fic_opcoes
│  ├─ fic_validacoes
│  ├─ fic_placeholder
│  ├─ fic_help_text
│  ├─ fic_visible_if
│  ├─ fic_edita
│  ├─ ficg_id
│  ├─ created_at
│  ├─ updated_at
│  └─ deleted_at
├─ tbl_ficha_campos
│  ├─ ficg_id
│  ├─ ficg_group
│  ├─ fic_nome
│  ├─ fic_label
│  ├─ fic_tipo
│  ├─ fic_opcoes
│  ├─ fic_validacoes
│  ├─ fic_placeholder
│  ├─ fic_help_text
│  ├─ fic_obrigatorio
│  ├─ created_at
│  └─ updated_at
├─ tbl_formularios
│  ├─ form_id
│  ├─ eve_id
│  ├─ cat_id
│  ├─ form_nome
│  ├─ form_versao
│  ├─ form_ativo
│  ├─ created_at
│  └─ updated_at
├─ tbl_galeria
│  ├─ gal_id
│  ├─ ins_id
│  ├─ gal_url
│  ├─ gal_date
│  ├─ gal_status
│  ├─ gal_ativo
│  ├─ gal_rotate
│  ├─ gal_date_status
│  ├─ gal_atualizado
│  └─ gal_local
├─ tbl_isncricao
│  ├─ ins_id
│  ├─ eve_id
│  ├─ cat_id
│  ├─ form_id
│  ├─ usu_id
│  ├─ ins_nome
│  ├─ ins_sobrenome
│  ├─ ins_nomecracha
│  ├─ ins_email
│  ├─ ins_senha
│  ├─ ins_cpf
│  ├─ ins_cnpj
│  ├─ ins_tel_celular
│  ├─ ins_tel_comercial
│  ├─ ins_instituicao
│  ├─ ins_siglainstituicao
│  ├─ ins_cargo
│  ├─ ins_cargo_cred
│  ├─ ins_observacao
│  ├─ ins_cep
│  ├─ ins_endereco
│  ├─ ins_numero
│  ├─ ins_complemento
│  ├─ ins_bairro
│  ├─ ins_cidade
│  ├─ ins_estado
│  ├─ ins_pais
│  ├─ ins_adicional1
│  ├─ ...
│  ├─ ins_adicional30
│  ├─ ins_dados
│  ├─ ins_token
│  ├─ ins_aprovado
│  ├─ ins_aprovado_data
│  ├─ ins_motivo
│  ├─ ins_confirmacao_assunto
│  ├─ ins_confirmacao_html
│  ├─ created_at
│  └─ updated_at
├─ tbl_isncricao_logs
│  ├─ log_id
│  ├─ ins_id
│  ├─ eve_id
│  ├─ actor_type
│  ├─ actor_usu_id
│  ├─ changes
│  ├─ ip
│  ├─ user_agent
│  ├─ created_at
│  └─ updated_at
├─ tbl_isncricao_respostas
│  ├─ res_id
│  ├─ ins_id
│  ├─ fic_id
│  ├─ eve_id
│  ├─ res_valor_texto
│  ├─ res_valor_json
│  ├─ created_at
│  └─ updated_at
├─tbl_model_has_permissions
│  ├─ permission_id
│  ├─ model_type
│  └─ model_id
├─tbl_model_has_role
│  ├─ role_id
│  ├─ model_type
│  └─ model_id
├─ tbl_organizadoras
│  ├─ org_id
│  ├─ usu_id
│  ├─ org_nome
│  ├─ created_at
│  └─ updated_at
├─ tbl_permissions
│  ├─ id
│  ├─ name
│  ├─ perm_label
│  ├─ perm_desc
│  ├─ guard_name
│  ├─ created_at
│  └─ updated_at

├─ tbl_roles
│  ├─ id
│  ├─ name
│  ├─ guard_name
│  ├─ created_at
│  └─ updated_at
├─ tbl_role_has_permissions
│  ├─ permission_id
│  └─ role_id
├─ tbl_sessions
│  ├─ id
│  ├─ user_id
│  ├─ ip_address
│  ├─ user_agent
│  ├─ payload
│  └─ last_activity
└─ tbl_usuarios
   ├─ usu_id
   ├─ usu_nome
   ├─ usu_email
   ├─ usu_email_verified_at
   ├─ usu_password
   ├─ remember_token
   ├─ created_at
   └─ updated_at
