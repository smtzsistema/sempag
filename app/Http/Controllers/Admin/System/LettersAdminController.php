<?php

namespace App\Http\Controllers\Admin\System;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Event;
use App\Models\Letter;
use Illuminate\Http\Request;

class LettersAdminController extends Controller
{
    public function index(Event $event)
    {
        $letters = Letter::where('eve_id', $event->eve_id)
            ->orderByDesc('car_id')
            ->paginate(20);

        // opcional: mapear nomes de categorias pra exibir na listagem sem pivot
        $catMap = Category::where('eve_id', $event->eve_id)
            ->orderBy('cat_nome')
            ->get()
            ->keyBy('cat_id');

        // injeta uma prop auxiliar em cada carta (pra view)
        $letters->getCollection()->transform(function ($letter) use ($catMap) {
            $ids = $this->normalizeCategoryIds($letter->cat_id);
            $letter->category_names = collect($ids)
                ->map(fn($id) => $catMap[$id]->cat_nome ?? null)
                ->filter()
                ->values()
                ->all();
            return $letter;
        });

        return view('admin.system.letters.index', compact('event', 'letters'));
    }

    public function create(Event $event)
    {
        $categories = Category::where('eve_id', $event->eve_id)
            ->orderBy('cat_nome')
            ->get();

        $selectedCategories = []; // create vazio
        return view('admin.system.letters.create', compact('event', 'categories', 'selectedCategories'));
    }

    public function store(Request $request, Event $event)
    {
        $data = $this->validateData($request);

        $categoryIds = $this->normalizeCategoryIds($data['category_ids'] ?? []);

        Letter::create([
            'eve_id'          => $event->eve_id,
            'car_descricao'   => $data['car_descricao'],
            'car_assunto'     => $data['car_assunto'],
            'car_texto'       => $data['car_texto'],
            'car_tipo'        => $data['car_tipo'] ?? null,
            'car_trad'        => $data['car_trad'] ?? null,
            'car_copia'       => $data['car_copia'] ?? null,
            'car_copiac'      => $data['car_copiac'] ?? null,
            'car_responderto' => $data['car_responderto'] ?? null,

            // agora é JSON array
            'cat_id'          => $categoryIds,
        ]);

        return redirect()
            ->route('admin.system.letters.index', $event)
            ->with('ok', 'Carta cadastrada.');
    }

    public function edit(Event $event, Letter $letter)
    {
        abort_unless((int) $letter->eve_id === (int) $event->eve_id, 404);

        $categories = Category::where('eve_id', $event->eve_id)
            ->orderBy('cat_nome')
            ->get();

        $selectedCategories = $this->normalizeCategoryIds($letter->cat_id);

        return view('admin.system.letters.edit', compact('event', 'letter', 'categories', 'selectedCategories'));
    }

    public function update(Request $request, Event $event, Letter $letter)
    {
        abort_unless((int) $letter->eve_id === (int) $event->eve_id, 404);

        $data = $this->validateData($request);

        $categoryIds = $this->normalizeCategoryIds($data['category_ids'] ?? []);

        $letter->update([
            'car_descricao'   => $data['car_descricao'],
            'car_assunto'     => $data['car_assunto'],
            'car_texto'       => $data['car_texto'],
            'car_tipo'        => $data['car_tipo'] ?? null,
            'car_trad'        => $data['car_trad'] ?? null,
            'car_copia'       => $data['car_copia'] ?? null,
            'car_copiac'      => $data['car_copiac'] ?? null,
            'car_responderto' => $data['car_responderto'] ?? null,

            // JSON array
            'cat_id'          => $categoryIds,
        ]);

        return back()->with('ok', 'Carta atualizada.');
    }

    public function destroy(Event $event, Letter $letter)
    {
        abort_unless((int) $letter->eve_id === (int) $event->eve_id, 404);

        $letter->delete();

        return redirect()
            ->route('admin.system.letters.index', $event)
            ->with('ok', 'Carta excluída.');
    }

    private function validateData(Request $request): array
    {
        // se vier "" do select, transforma em null
        if ($request->input('car_tipo') === '') {
            $request->merge(['car_tipo' => null]);
        }
        if ($request->input('car_trad') === '') {
            $request->merge(['car_trad' => null]);
        }

        return $request->validate([
            'car_descricao'   => ['required', 'string', 'min:2', 'max:200'],
            'car_assunto'     => ['required', 'string', 'min:2', 'max:200'],
            'car_texto'       => ['required', 'string', 'min:10', 'max:200000'],

            'car_copia'       => ['nullable', 'string', 'max:500'],
            'car_copiac'      => ['nullable', 'string', 'max:500'],
            'car_responderto' => ['nullable', 'string', 'max:200'],

            'car_tipo'        => ['nullable', 'in:S,E,R,N'],
            'car_trad'        => ['nullable', 'in:pt,en,es'],

            // categorias agora são obrigatórias (se você quer obrigatório)
            'category_ids'    => ['required', 'array', 'min:1'],
            'category_ids.*'  => ['integer', 'exists:tbl_categorias,cat_id'],
        ], [
            'car_tipo.in'         => 'Status inválido.',
            'category_ids.required'=> 'Selecione pelo menos 1 categoria.',
        ]);
    }

    /**
     * Normaliza ids (aceita array, json string, null) => array<int>
     */
    private function normalizeCategoryIds($value): array
    {
        if (is_null($value) || $value === '') return [];

        // se já veio array do request
        if (is_array($value)) {
            return array_values(array_unique(array_map('intval', $value)));
        }

        // se veio JSON do banco como string
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return array_values(array_unique(array_map('intval', $decoded)));
            }
        }

        // fallback: se veio um número solto
        if (is_numeric($value)) {
            return [(int) $value];
        }

        return [];
    }
}
