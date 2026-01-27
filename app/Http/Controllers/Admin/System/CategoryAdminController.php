<?php

namespace App\Http\Controllers\Admin\System;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CategoryAdminController extends Controller
{
    public function index(Event $event)
    {
        $categories = Category::where('eve_id', $event->eve_id)
            ->orderBy('cat_id')
            ->get();

        return view('admin.system.categories.index', compact('event', 'categories'));
    }

    public function create(Event $event)
    {
        $category = new Category([
            'cat_ativo' => true,
            'cat_aprova' => false,
        ]);

        return view('admin.system.categories.create', compact('event', 'category'));
    }

    public function store(Request $request, Event $event)
    {
        $data = $request->validate([
            'cat_nome' => ['required', 'string', 'min:2', 'max:120'],
            'cat_descricao' => ['nullable', 'string', 'max:5000'],
            'cat_ativo' => ['nullable'],
            'cat_aprova' => ['nullable'],

            // visibilidade agendada
            'cat_date_start' => ['nullable', 'date'],
            'cat_date_end' => ['nullable', 'date', 'after_or_equal:cat_date_start'],

            // upload banner (input deve chamar cat_banner_path)
            'cat_banner_path' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png', 'max:5120'], // 5MB
        ]);

        $category = Category::create([
            'eve_id' => $event->eve_id,
            'cat_nome' => $data['cat_nome'],
            'cat_descricao' => $data['cat_descricao'] ?? null,
            'cat_ativo' => (bool) ($data['cat_ativo'] ?? false),
            'cat_aprova' => (bool) ($data['cat_aprova'] ?? false),
            'cat_date_start' => $data['cat_date_start'] ?? null,
            'cat_date_end' => $data['cat_date_end'] ?? null,
        ]);

        // Banner (opcional)
        if ($request->hasFile('cat_banner_path')) {
            $path = $this->storeBanner($request, $event, $category);
            $category->update(['cat_banner_path' => $path]);
        }

        return redirect()
            ->route('admin.system.categories.edit', [$event, $category])
            ->with('ok', 'Categoria criada.');
    }

    public function edit(Event $event, Category $category)
    {
        abort_unless((int) $category->eve_id === (int) $event->eve_id, 404);

        return view('admin.system.categories.edit', compact('event', 'category'));
    }

    public function update(Request $request, Event $event, Category $category)
    {
        abort_unless((int) $category->eve_id === (int) $event->eve_id, 404);

        $data = $request->validate([
            'cat_nome' => ['required', 'string', 'min:2', 'max:120'],
            'cat_descricao' => ['nullable', 'string', 'max:5000'],
            'cat_ativo' => ['nullable'],
            'cat_aprova' => ['nullable'],

            // visibilidade agendada
            'cat_date_start' => ['nullable', 'date'],
            'cat_date_end' => ['nullable', 'date', 'after_or_equal:cat_date_start'],

            // banner
            'cat_banner_path' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png', 'max:5120'], // 5MB
            'banner_remove' => ['nullable'],
        ]);

        // Remover banner
        if (!empty($data['banner_remove']) && !empty($category->cat_banner_path)) {
            if (Storage::disk('public')->exists($category->cat_banner_path)) {
                Storage::disk('public')->delete($category->cat_banner_path);
            }
            $category->cat_banner_path = null;
        }

        // Upload novo banner
        if ($request->hasFile('cat_banner_path')) {
            // apaga o anterior
            if (!empty($category->cat_banner_path) && Storage::disk('public')->exists($category->cat_banner_path)) {
                Storage::disk('public')->delete($category->cat_banner_path);
            }

            $category->cat_banner_path = $this->storeBanner($request, $event, $category);
        }

        $category->fill([
            'cat_nome' => $data['cat_nome'],
            'cat_descricao' => $data['cat_descricao'] ?? null,
            'cat_ativo' => (bool) ($data['cat_ativo'] ?? false),
            'cat_aprova' => (bool) ($data['cat_aprova'] ?? false),
            'cat_date_start' => $data['cat_date_start'] ?? null,
            'cat_date_end' => $data['cat_date_end'] ?? null,
        ]);

        $category->save();

        return back()->with('ok', 'Categoria atualizada.');
    }

    private function storeBanner(Request $request, Event $event, Category $category): string
    {
        $file = $request->file('cat_banner_path');

        $token = $event->eve_token ?? $event->eve_slug ?? (string) $event->eve_id;
        $folder = $token . '/categorias/' . $category->cat_id . '/banner';

        $filename = $file->hashName();

        return $file->storeAs($folder, $filename, 'public');
    }
}
