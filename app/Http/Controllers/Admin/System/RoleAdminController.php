<?php

namespace App\Http\Controllers\Admin\System;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleAdminController extends Controller
{
    public function index(Event $event)
    {
        $roles = Role::query()
            ->orderBy('name')
            ->withCount('users')
            ->get();

        return view('admin.roles.index', compact('event', 'roles'));
    }

    public function create(Event $event)
    {
        $permissions = \App\Models\Permission::query()
            ->orderByRaw("COALESCE(perm_group,'') asc")
            ->orderByRaw("COALESCE(perm_label,name) asc")
            ->get();

        return view('admin.roles.form', [
            'mode'        => 'create',
            'event'       => $event,
            'role'        => null,
            'permissions' => $permissions,
            'selected'    => [],
        ]);
    }

    public function store(Request $request, Event $event)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80', 'unique:' . config('permission.table_names.roles') . ',name'],
            'permissions' => ['array'],
            'permissions.*' => ['string'],
        ]);

        $role = Role::create([
            'name'       => $data['name'],
            'guard_name' => config('auth.defaults.guard', 'web'),
        ]);

        $perms = Permission::whereIn('name', $data['permissions'] ?? [])->get();
        $role->syncPermissions($perms);

        return redirect()
            ->route('admin.system.roles.index', $event)
            ->with('success', 'Grupo criado com sucesso.');
    }

    public function edit(Event $event, Role $role)
    {
        $permissions = Permission::query()
            ->orderBy('name')
            ->get();

        $selected = $role->permissions()->pluck('name')->toArray();

        return view('admin.roles.form', [
            'mode'        => 'edit',
            'event'       => $event,
            'role'        => $role,
            'permissions' => $permissions,
            'selected'    => $selected,
        ]);
    }

    public function update(Request $request, Event $event, Role $role)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80', 'unique:' . config('permission.table_names.roles') . ',name,' . $role->id],
            'permissions' => ['array'],
            'permissions.*' => ['string'],
        ]);

        // Segurança: evita renomear "Admin"
        if ($role->name === 'Admin' && $data['name'] !== 'Admin') {
            return back()->with('error', 'Não é permitido renomear o grupo Admin.');
        }

        $role->update(['name' => $data['name']]);

        $perms = Permission::whereIn('name', $data['permissions'] ?? [])->get();
        $role->syncPermissions($perms);

        return redirect()
            ->route('admin.system.roles.index', $event)
            ->with('success', 'Grupo atualizado com sucesso.');
    }

    public function destroy(Event $event, Role $role)
    {
        if ($role->name === 'Admin') {
            return back()->with('error', 'Não é permitido excluir o grupo Admin.');
        }

        $role->delete();

        return redirect()
            ->route('admin.system.roles.index', $event)
            ->with('success', 'Grupo excluído.');
    }
}
