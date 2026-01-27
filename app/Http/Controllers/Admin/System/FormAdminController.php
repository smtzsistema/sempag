<?php

namespace App\Http\Controllers\Admin\System;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Event;
use App\Models\Form;
use App\Models\FormField;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FormAdminController extends Controller
{
    public function index(Event $event)
    {
        $forms = Form::where('eve_id', $event->eve_id)
            ->with('category')
            ->orderByDesc('form_id')
            ->get();

        return view('admin.system.forms.index', compact('event', 'forms'));
    }

    public function create(Request $request, Event $event)
    {
        $categories = Category::where('eve_id', $event->eve_id)
            ->orderBy('cat_nome')
            ->get();

        // opcional: clonar a partir de outra ficha
        $cloneFormId = $request->query('clone_form_id');
        $cloneForm = null;
        if (!empty($cloneFormId)) {
            $cloneForm = Form::where('eve_id', $event->eve_id)
                ->where('form_id', (int) $cloneFormId)
                ->with('category')
                ->first();
        }

        return view('admin.system.forms.create', compact('event', 'categories', 'cloneForm'));
    }

    public function store(Request $request, Event $event)
    {
        $data = $request->validate([
            'cat_id'     => ['required', 'integer'],
            'form_nome'  => ['required', 'string', 'min:2', 'max:120'],
            'form_ativo' => ['nullable'], // checkbox
            'clone_form_id' => ['nullable', 'integer'],
        ]);

        $category = Category::where('eve_id', $event->eve_id)
            ->where('cat_id', $data['cat_id'])
            ->firstOrFail();

        // próxima versão por categoria (padrão novo)
        $nextVersion = ((int) Form::where('eve_id', $event->eve_id)
                ->where('cat_id', $category->cat_id)
                ->max('form_versao')) + 1;

        $form = DB::transaction(function () use ($event, $category, $data, $nextVersion) {

            $newForm = Form::create([
                'eve_id'     => $event->eve_id,
                'cat_id'     => $category->cat_id,
                'form_nome'  => $data['form_nome'],
                'form_versao'=> $nextVersion,
                'form_ativo' => (bool) ($data['form_ativo'] ?? false),
            ]);

            // Clonar campos (tbl_ficha) se veio clone_form_id
            $cloneId = (int) ($data['clone_form_id'] ?? 0);
            if ($cloneId > 0) {
                $cloneForm = Form::where('eve_id', $event->eve_id)
                    ->where('form_id', $cloneId)
                    ->first();

                if ($cloneForm) {
                    $fields = FormField::where('form_id', $cloneForm->form_id)
                        ->whereNull('deleted_at')
                        ->orderBy('fic_ordem')
                        ->get();

                    foreach ($fields as $field) {
                        // Usa a relação pra garantir o form_id mesmo se não estiver em $fillable
                        $newForm->fields()->create([
                            'fic_nome'        => $field->fic_nome,
                            'fic_label'       => $field->fic_label,
                            'fic_tipo'        => $field->fic_tipo,
                            'fic_obrigatorio' => (bool) $field->fic_obrigatorio,
                            'fic_ordem'       => (int) $field->fic_ordem,
                            'fic_opcoes'      => $field->fic_opcoes,
                            'fic_validacoes'  => $field->fic_validacoes,
                            'fic_placeholder' => $field->fic_placeholder,
                            'fic_help_text'   => $field->fic_help_text,
                            'fic_visible_if'  => $field->fic_visible_if,
                            'fic_edita'       => $field->fic_edita,
                            'ficg_id'         => $field->ficg_id,
                        ]);
                    }
                }
            }

            return $newForm;
        });

        return redirect()
            ->route('admin.system.forms.edit', [$event, $form])
            ->with('ok', 'Ficha criada. Agora configure os campos.');
    }

    public function edit(Event $event, Form $form)
    {
        abort_unless((int) $form->eve_id === (int) $event->eve_id, 404);

        $form->load(['category', 'fields']);

        return view('admin.system.forms.edit', compact('event', 'form'));
    }

    public function update(Request $request, Event $event, Form $form)
    {
        abort_unless((int) $form->eve_id === (int) $event->eve_id, 404);

        $data = $request->validate([
            'form_nome'  => ['required', 'string', 'min:2', 'max:120'],
            'form_ativo' => ['nullable'],
        ]);

        $form->update([
            'form_nome'  => $data['form_nome'],
            'form_ativo' => (bool) ($data['form_ativo'] ?? false),
        ]);

        return back()->with('ok', 'Ficha atualizada.');
    }
}
