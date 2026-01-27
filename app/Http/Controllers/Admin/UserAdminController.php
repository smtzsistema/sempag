<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class UserAdminController extends Controller
{
    public function index(Event $event)
    {
        $users = User::query()
            ->whereHas('events', fn($q) => $q->where('tbl_eventos.eve_id', $event->id))
            ->with('roles')
            ->orderBy('usu_nome')
            ->get();

        // Garante que o dono do evento aparece na lista mesmo que não esteja no pivot
        $owner = $event->organizer?->user;
        if ($owner && !$users->contains(fn($u) => (int)$u->id === (int)$owner->id)) {
            $owner->load('roles');
            $users->prepend($owner);
        }

        return view('admin.users.index', compact('event', 'users'));
    }

    public function create(Event $event)
    {
        $roles = Role::query()->where('guard_name', 'web')->orderBy('name')->get();
        return view('admin.users.create', compact('event', 'roles'));
    }

    public function store(Request $request, Event $event)
    {
        $data = $request->validate([
            'usu_nome' => ['required', 'string', 'min:3', 'max:120'],
            'usu_email' => ['required', 'email', 'max:190', 'unique:tbl_usuarios,usu_email'],
            'usu_password' => ['required', 'confirmed', Password::min(6)],
            'role_id' => ['required', 'integer', 'exists:tbl_roles,id'],
        ]);

        $user = User::create([
            'usu_nome' => $data['usu_nome'],
            'usu_email' => $data['usu_email'],
            'usu_password' => Hash::make($data['usu_password']),
        ]);
        // $user->password = $data['usu_password'];
        $user->save();

        // Vincula ao evento
        $event->users()->syncWithoutDetaching([$user->id]);

        // Aplica o grupo
        $role = Role::find($data['role_id']);
        $user->assignRole($role);

        return redirect()->route('admin.users.index', $event)
            ->with('success', 'Usuário criado e vinculado ao evento.');
    }

    public function edit(Event $event, User $user)
    {
        $this->assertUserInEvent($event, $user);

        $roles = Role::orderBy('name')->get();
        $currentRole = $user->roles()->pluck('name')->first();

        return view('admin.users.form', [
            'mode' => 'edit',
            'event' => $event,
            'user' => $user,
            'roles' => $roles,
            'currentRole' => $currentRole,
        ]);
    }

    public function update(Request $request, Event $event, User $user)
    {
        $this->assertUserInEvent($event, $user);

        $data = $request->validate([
            'usu_nome' => ['required', 'string', 'max:120'],
            'usu_email' => ['required', 'email', 'max:180'],
            'role' => ['nullable', 'string'],
        ]);

        $user->update([
            'usu_nome' => $data['usu_nome'],
            'usu_email' => $data['usu_email'],
        ]);

        if (!empty($data['role'])) {
            $user->syncRoles([$data['role']]);
        } else {
            $user->syncRoles([]);
        }

        return redirect()->route('admin.users.index', $event)
            ->with('success', 'Usuário atualizado.');
    }

    public function resetPassword(Request $request, Event $event, User $user)
    {
        $this->assertUserInEvent($event, $user);

        $data = $request->validate([
            'password' => ['nullable', 'string', 'min:6', 'max:80'],
        ]);

        $newPass = $data['password'] ?: Str::random(10);

        $user->usu_password = Hash::make($newPass);
        $user->save();

        // mostra a senha gerada uma vez na tela
        return back()->with('success', 'Senha redefinida. Nova senha: ' . $newPass);
    }

    /**
     * Garante que o usuário pertence ao evento (tbl_evento_usuarios)
     */
    private function assertUserInEvent(Event $event, User $user): void
    {
        $exists = DB::table('tbl_evento_usuarios')
            ->where('eve_id', $event->id)
            ->where('usu_id', $user->id)
            ->exists();

        if (!$exists) {
            abort(404);
        }
    }

}
