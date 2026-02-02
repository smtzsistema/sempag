<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;
use App\Models\Permission;
use App\Models\Role;

class RbacSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Permissions base do admin
        $perms = [
            // Geral
            ['name' => 'dashboard.view', 'perm_label' => 'Ver dashboard', 'perm_group' => 'Geral'],

            // Inscrições
            ['name' => 'registrations.view', 'perm_label' => 'Ver inscrições', 'perm_group' => 'Inscrições'],
            ['name' => 'registrations.edit', 'perm_label' => 'Editar inscrição', 'perm_group' => 'Inscrições'],
            ['name' => 'registrations.approve', 'perm_label' => 'Aprovar/Reprovar inscrição', 'perm_group' => 'Inscrições'],
            ['name' => 'registrations.delete', 'perm_label' => 'Excluir inscrição', 'perm_group' => 'Inscrições'],
            ['name' => 'registrations.export', 'perm_label' => 'Exportar relatórios', 'perm_group' => 'Inscrições'],
            ['name' => 'registrations.salas', 'perm_label' => 'Lista por sala', 'perm_group' => 'Inscrições'],
            ['name' => 'fotos.view', 'perm_label' => 'Ver Fotos', 'perm_group' => 'Inscrições'],
            ['name' => 'fotos.edit', 'perm_label' => 'Editar/Excluir Fotos', 'perm_group' => 'Inscrições'],




            // Estatísticas
            ['name' => 'stats.view', 'perm_label' => 'Ver estatísticas', 'perm_group' => 'Estatísticas'],

            // Sistema / Integrações / Usuários
            ['name' => 'system.manage', 'perm_label' => 'Configurações do sistema', 'perm_group' => 'Sistema'],
            ['name' => 'sync.manage', 'perm_label' => 'Sincronização/Importação', 'perm_group' => 'Integrações'],
            ['name' => 'users.manage', 'perm_label' => 'Gerenciar usuários', 'perm_group' => 'Usuários'],
        ];

        foreach ($perms as $p) {
            Permission::updateOrCreate(
                ['name' => $p['name'], 'guard_name' => 'web'],
                [
                    'perm_label' => $p['perm_label'] ?? null,
                    'perm_group' => $p['perm_group'] ?? null,
                    'perm_desc'  => $p['perm_desc'] ?? null,
                ]
            );
        }

        // Roles (grupos)
        $admin = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $dados = Role::firstOrCreate(['name' => 'Dados', 'guard_name' => 'web']);

        // Admin = tudo (pega sempre pelos NAMES)
        $allPermissionNames = Permission::query()
            ->where('guard_name', 'web')
            ->pluck('name')
            ->toArray();

        $admin->syncPermissions($allPermissionNames);

        // Dados = só o que ele precisa (NAMES)
        $dados->syncPermissions([
            'dashboard.view',
            'registrations.view',
            'registrations.export',
            'stats.view',
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
