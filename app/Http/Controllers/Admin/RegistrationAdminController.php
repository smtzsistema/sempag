<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Registration;
use App\Models\Category;
use App\Support\RegistrationAudit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RegistrationAdminController extends Controller
{
    public function index(Request $request, Event $event)
    {
        $q = $request->get('q', '');
        $status = $request->get('status', '');
        $catId = $request->get('cat_id', '');

        $query = Registration::where('eve_id', $event->eve_id)
            ->with(['category']); // relacionamento singular

        if (!empty($status)) {
            $query->where('ins_aprovado', $status);
        } else {
            // padrão: não mostra excluídos
            $query->where('ins_aprovado', '!=', 'N');
        }

        if (!empty($catId)) {
            $query->where('cat_id', $catId);
        }

        if (!empty($q)) {
            $query->where(function ($qq) use ($q) {
                $qq->where('ins_nome', 'like', "%{$q}%")
                    ->orWhere('ins_sobrenome', 'like', "%{$q}%")
                    ->orWhere('ins_email', 'like', "%{$q}%")
                    ->orWhere('ins_instituicao', 'like', "%{$q}%")
                    ->orWhere('ins_cpf', 'like', "%{$q}%")
                    ->orWhere('ins_id', 'like', "%{$q}%");
            });
        }

        $registrations = $query->orderByDesc('ins_id')->paginate(20)->withQueryString();

        $categories = Category::where('eve_id', $event->eve_id)
            ->orderBy('cat_nome')
            ->get();

        return view('admin.registrations.index', compact(
            'event',
            'registrations',
            'categories',
            'q',
            'status',
            'catId'
        ));
    }

    public function show(Event $event, Registration $registration)
    {
        abort_unless((int)$registration->eve_id === (int)$event->eve_id, 404);

        // carrega a categoria e a ficha/campos
        $registration->load(['category', 'form.fields']);

        $fields = $registration->form?->fields
            ?->sortBy('fic_ordem')
            ?->values()
            ?? collect();

        $dados = is_array($registration->ins_dados) ? $registration->ins_dados : [];

        $valuesByFieldId = [];
        foreach ($fields as $field) {
            $fid = (int)$field->fic_id;
            $key = (string)($field->fic_nome ?? '');

            if ($key !== '' && array_key_exists($key, $registration->getAttributes())) {
                $valuesByFieldId[$fid] = $registration->{$key};
            } else {
                $valuesByFieldId[$fid] = $key !== '' ? ($dados[$key] ?? null) : null;
            }
        }

        return view('admin.registrations.show', compact(
            'event',
            'registration',
            'fields',
            'valuesByFieldId'
        ));
    }


    public function edit(Event $event, Registration $registration)
    {
        abort_unless((int)$registration->eve_id === (int)$event->eve_id, 404);

        $registration->load(['form.fields']);

        $fields = $registration->form?->fields
            ?->sortBy('fic_ordem')
            ?->values()
            ?? collect();

        // ins_dados já vem como array por causa do cast
        $dados = is_array($registration->ins_dados) ? $registration->ins_dados : [];

        // valores do form, mas vindos da tbl_inscricao
        $valuesByFieldId = [];

        foreach ($fields as $field) {
            $fid = (int)$field->fic_id;
            $key = (string)($field->fic_nome ?? '');

            if ($key !== '' && array_key_exists($key, $registration->getAttributes())) {
                // 1) se existe como COLUNA (ins_*)
                $valuesByFieldId[$fid] = $registration->{$key};
            } else {
                // 2) senão, cai pro JSON ins_dados
                $valuesByFieldId[$fid] = $key !== '' ? ($dados[$key] ?? null) : null;
            }
        }

        return view('admin.registrations.edit', compact(
            'event',
            'registration',
            'fields',
            'valuesByFieldId'
        ));
    }

    public function update(Request $request, Event $event, Registration $registration)
    {
        abort_unless((int)$registration->eve_id === (int)$event->eve_id, 404);

        $canApprove = $request->user()->can('registrations.approve');

        // Regras base (edição de dados)
        $rules = [
            // "core" (padrão novo)
            'ins_nome' => ['nullable', 'string', 'max:255'],
            'ins_sobrenome' => ['nullable', 'string', 'max:255'],
            'ins_nomecracha' => ['nullable', 'string', 'max:255'],
            'ins_email' => ['nullable', 'email', 'max:255'],
            'ins_cpf' => ['nullable', 'string', 'max:40'],
            'ins_tel_celular' => ['nullable', 'string', 'max:60'],
            'ins_instituicao' => ['nullable', 'string', 'max:255'],
            'ins_cargo' => ['nullable', 'string', 'max:255'],

            'f' => ['nullable', 'array'],
        ];

        // Só quem aprova pode mudar status/motivo
        if ($canApprove) {
            $rules['ins_aprovado'] = ['required', 'string', 'in:S,E,R,N'];
            $rules['ins_motivo'] = ['nullable', 'string', 'max:5000'];
        }

        $data = $request->validate($rules);

        $registration->load(['form.fields']);
        $fields = $registration->form?->fields ?? collect();

        $original = $registration->getOriginal();
        $origDados = is_array($registration->ins_dados) ? $registration->ins_dados : [];

        // Atualiza colunas fixas (SEM status por padrão)
        $update = [
            'ins_nome' => $data['ins_nome'] ?? $registration->ins_nome,
            'ins_sobrenome' => $data['ins_sobrenome'] ?? $registration->ins_sobrenome,
            'ins_nomecracha' => $data['ins_nomecracha'] ?? $registration->ins_nomecracha,
            'ins_email' => $data['ins_email'] ?? $registration->ins_email,
            'ins_cpf' => $data['ins_cpf'] ?? $registration->ins_cpf,
            'ins_tel_celular' => $data['ins_tel_celular'] ?? $registration->ins_tel_celular,
            'ins_instituicao' => $data['ins_instituicao'] ?? $registration->ins_instituicao,
            'ins_cargo' => $data['ins_cargo'] ?? $registration->ins_cargo,
        ];

        // Aplica status/motivo só se tiver permissão
        if ($canApprove) {
            $update['ins_aprovado'] = $data['ins_aprovado'];
            $update['ins_motivo'] = $data['ins_motivo'] ?? null;

            if ($data['ins_aprovado'] === 'S') {
                $update['ins_aprovado_data'] = now();
                $update['ins_motivo'] = null;
            } else {
                $update['ins_aprovado_data'] = null;
            }
        }

        // Atualiza campos do formulário SEM usar tbl_inscricao_respostas
        $payload = $request->input('f', []);
        if (!is_array($payload)) $payload = [];

        $insDados = $origDados;
        $fillable = (new Registration())->getFillable();

        foreach ($fields as $field) {
            $fid = (int)$field->fic_id;
            $key = (string)($field->fic_nome ?? '');
            if ($key === '') continue;

            $incoming = $payload[$fid] ?? null;
            $val = $this->normalizeIncoming($incoming, (string)($field->fic_tipo ?? 'text'));

            // Arrays sempre vão para ins_dados
            if (is_array($val)) {
                $insDados[$key] = $val;
                continue;
            }

            // Se a key corresponde a uma coluna física, salva na própria tbl_inscricao.
            if (in_array($key, $fillable, true)) {
                $update[$key] = $val;
            } else {
                $insDados[$key] = $val;
            }
        }

        // Remove chaves vazias do ins_dados
        foreach ($insDados as $k => $v) {
            if ($v === null || $v === '' || $v === []) unset($insDados[$k]);
        }

        $update['ins_dados'] = $insDados ?: null;

        $registration->forceFill($update);
        $registration->save();

        // ===== Audit log
        $changes = $this->diffChanges($original, $update, $origDados, $insDados);
        RegistrationAudit::log(
            $registration,
            $request,
            'admin',
            Auth::id(),
            $changes
        );

        return redirect()
            ->route('admin.registrations.show', [$event, $registration])
            ->with('ok', 'Inscrição atualizada.');
    }


    public function approve(Event $event, Registration $registration)
    {
        abort_unless((int)$registration->eve_id === (int)$event->eve_id, 404);

        $original = $registration->getOriginal();
        $registration->update([
            'ins_aprovado' => 'S',
            'ins_aprovado_data' => now(),
            'ins_motivo' => null,
        ]);

        RegistrationAudit::log($registration, request(), 'admin', Auth::id(), [
            'ins_aprovado' => ['from' => $original['ins_aprovado'] ?? null, 'to' => 'S'],
        ]);

        return back()->with('ok', 'Inscrição aprovada.');
    }

    public function reject(Request $request, Event $event, Registration $registration)
    {
        abort_unless((int)$registration->eve_id === (int)$event->eve_id, 404);

        $data = $request->validate([
            'ins_motivo' => ['nullable', 'string', 'max:5000'],
        ]);

        $original = $registration->getOriginal();
        $registration->update([
            'ins_aprovado' => 'R',
            'ins_motivo' => $data['ins_motivo'] ?? null,
            'ins_aprovado_data' => null,
        ]);

        RegistrationAudit::log($registration, $request, 'admin', Auth::id(), [
            'ins_aprovado' => ['from' => $original['ins_aprovado'] ?? null, 'to' => 'R'],
            'ins_motivo' => ['from' => $original['ins_motivo'] ?? null, 'to' => $data['ins_motivo'] ?? null],
        ]);

        return back()->with('ok', 'Inscrição reprovada.');
    }

    public function destroy(Event $event, Registration $registration)
    {
        abort_unless((int)$registration->eve_id === (int)$event->eve_id, 404);

        $original = $registration->getOriginal();
        $registration->update([
            'ins_aprovado' => 'N',
            'ins_aprovado_data' => null,
        ]);

        RegistrationAudit::log($registration, request(), 'admin', Auth::id(), [
            'ins_aprovado' => ['from' => $original['ins_aprovado'] ?? null, 'to' => 'N'],
        ]);

        return redirect()->route('admin.registrations.index', $event)->with('ok', 'Inscrição excluída.');
    }

    public function restore(Event $event, Registration $registration)
    {
        abort_unless((int)$registration->eve_id === (int)$event->eve_id, 404);

        $original = $registration->getOriginal();
        $registration->update([
            'ins_aprovado' => 'E',
            'ins_aprovado_data' => null,
        ]);

        RegistrationAudit::log($registration, request(), 'admin', Auth::id(), [
            'ins_aprovado' => ['from' => $original['ins_aprovado'] ?? null, 'to' => 'E'],
        ]);

        return back()->with('ok', 'Inscrição restaurada (Em análise).');
    }

    /**
     * @return array{0: array<int,mixed>, 1: array<string,mixed>}
     */
    private function valuesFromRegistration(Registration $registration, $fields): array
    {
        $insDados = is_array($registration->ins_dados) ? $registration->ins_dados : [];
        $fillable = (new Registration())->getFillable();

        $byId = [];
        $byKey = [];

        foreach ($fields as $field) {
            $key = (string)($field->fic_nome ?? '');
            $val = null;

            if ($key !== '' && in_array($key, $fillable, true)) {
                $val = $registration->{$key};
            } elseif ($key !== '' && array_key_exists($key, $insDados)) {
                $val = $insDados[$key];
            }

            $byId[(int)$field->fic_id] = $val;
            if ($key !== '') $byKey[$key] = $val;
        }

        return [$byId, $byKey];
    }

    /**
     * Normaliza o valor que chegou do formulário.
     * @return mixed
     */
    private function normalizeIncoming($incoming, string $type)
    {
        $type = strtolower(trim($type));

        if ($type === 'multiselect') {
            $arr = $incoming;
            if (!is_array($arr)) {
                $arr = ($arr === null || $arr === '') ? [] : [$arr];
            }
            $arr = array_values(array_filter(array_map(fn($x) => trim((string)$x), $arr), fn($x) => $x !== ''));
            return $arr;
        }

        if ($type === 'checkbox') {
            return $incoming ? '1' : null;
        }

        $txt = is_array($incoming)
            ? json_encode($incoming, JSON_UNESCAPED_UNICODE)
            : (string)($incoming ?? '');

        $txt = trim($txt);
        return $txt !== '' ? $txt : null;
    }

    /**
     * @param array<string,mixed> $original
     * @param array<string,mixed> $update
     * @param array<string,mixed> $origDados
     * @param array<string,mixed> $newDados
     * @return array<string,array{from:mixed,to:mixed}>
     */
    private function diffChanges(array $original, array $update, array $origDados, array $newDados): array
    {
        $changes = [];

        foreach ($update as $k => $to) {
            if ($k === 'ins_dados') continue;
            $from = $original[$k] ?? null;
            if ($from != $to) {
                $changes[$k] = ['from' => $from, 'to' => $to];
            }
        }

        // diff ins_dados por chave
        $allKeys = array_values(array_unique(array_merge(array_keys($origDados), array_keys($newDados))));
        foreach ($allKeys as $k) {
            $from = $origDados[$k] ?? null;
            $to = $newDados[$k] ?? null;
            if ($from != $to) {
                $changes["ins_dados." . $k] = ["from" => $from, "to" => $to];
            }
        }
        return $changes;
    }
}
