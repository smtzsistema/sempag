<?php

return [
    // Se precisar de multi-tenant por time/equipe no futuro, dá pra ligar.
    'teams' => false,

    'models' => [
        'permission' => App\Models\Permission::class,
        'role' => App\Models\Role::class,
    ],

    // Tabelas no padrão tbl_*
    'table_names' => [
        'roles'                 => 'tbl_roles',
        'permissions'           => 'tbl_permissions',
        'model_has_permissions' => 'tbl_model_has_permissions',
        'model_has_roles'       => 'tbl_model_has_roles',
        'role_has_permissions'  => 'tbl_role_has_permissions',
    ],

    'column_names' => [
        // Caso você mude os nomes padrões, ajuste aqui.
        'role_pivot_key'       => null,
        'permission_pivot_key' => null,
        'model_morph_key'      => 'model_id',
        'team_foreign_key'     => 'team_id',
    ],

    // Registra o método hasPermissionTo no Gate automaticamente
    'register_permission_check_method' => true,

    'register_octane_reset_listener' => false,

    // Wildcards tipo "posts.*" (não precisamos agora)
    'enable_wildcard_permission' => false,

    'display_permission_in_exception' => false,
    'display_role_in_exception'       => false,

    'cache' => [
        'expiration_time' => \DateInterval::createFromDateString('24 hours'),
        'key'             => 'spatie.permission.cache',
        'store'           => 'default',
    ],
];
