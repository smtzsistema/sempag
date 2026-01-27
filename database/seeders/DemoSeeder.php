<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Organizer;
use App\Models\Event;
use App\Models\Category;
use App\Models\Form;
use App\Models\FormField;
use App\Models\User;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        // Usuário admin (se quiser depois)
        $admin = User::firstOrCreate(
            ['usu_email' => 'admin@demo.com'],
            ['usu_nome' => 'Admin Demo', 'password' => bcrypt('123456')]
        );

        // Garante role Admin para o usuário demo
        if (method_exists($admin, 'assignRole') && !$admin->hasRole('Admin')) {
            $admin->assignRole('Admin');
        }

        $organizer = Organizer::firstOrCreate(
            ['usu_id' => $admin->id],
            ['org_nome' => 'Organizadora Demo']
        );

        $event = Event::firstOrCreate(
            ['eve_token' => 'demo20252'],
            [
                'org_id' => $organizer->id,
                'eve_nome' => 'Evento Demo 2025',
                'eve_local' => 'Joaquin Norberto',
                'eve_settings' => [],
                'eve_banner' => 'demo20252/banner/zUJfTv2NGMouVKxrLPWqNWOjO5vkcq9I8GUChVG0.png',
            ]
        );

        // Vínculo do dono ao evento (tbl_evento_usuarios)
        try {
            $event->users()->syncWithoutDetaching([$admin->id]);
        } catch (\Throwable $e) {
            // se migrations do pivot ainda não rodaram, ignora
        }

        // Categorias (colunas reais)
        $cat1 = Category::firstOrCreate(
            ['eve_id' => $event->id, 'cat_nome' => 'Visitante'],
            ['cat_ativo' => true, 'cat_aprova' => false, 'cat_banner_path' => 'demo20252/categorias/1/banner/DZea9fU9g3dI5thTFabFd4cigKQsvaBRDlHcUzlH.png'],
        );

        // Forms (colunas reais)
        $form1 = Form::firstOrCreate(
            ['cat_id' => $cat1->id, 'form_versao' => 1],
            ['eve_id' => $event->id, 'form_nome' => 'Ficha Visitante', 'form_ativo' => true]
        );

        // Campos do form visitante
        $this->fields($form1->id, [
            ['fic_nome' => 'ins_nome', 'fic_label' => 'Nome', 'fic_tipo' => 'text', 'fic_obrigatorio' => true, 'fic_validacoes' => 'required|string|min:3|max:120', 'fic_ordem' => 1, 'ficg_id' => 1],
            ['fic_nome' => 'ins_sobrenome', 'fic_label' => 'Sobrenome', 'fic_tipo' => 'text', 'fic_obrigatorio' => true, 'fic_validacoes' => 'required|string|min:3|max:120', 'fic_ordem' => 2, 'ficg_id' => 2],
            ['fic_nome' => 'ins_nomecracha', 'fic_label' => 'Nome Credencial', 'fic_tipo' => 'text', 'fic_obrigatorio' => true, 'fic_validacoes' => 'required|string|min:3|max:35', 'fic_ordem' => 3, 'ficg_id' => 3],
            ['fic_nome' => 'ins_cpf', 'fic_label' => 'CPF', 'fic_tipo' => 'cpf', 'fic_obrigatorio' => true, 'fic_validacoes' => 'nullable|string|min:11|max:14', 'fic_ordem' => 4, 'ficg_id' => 6],
            ['fic_nome' => 'ins_instituicao', 'fic_label' => 'Empresa', 'fic_tipo' => 'text', 'fic_obrigatorio' => true, 'fic_validacoes' => 'nullable|string|max:120', 'fic_ordem' => 5, 'ficg_id' => 7],
            ['fic_nome' => 'ins_siglainstituicao', 'fic_label' => 'Fantasia', 'fic_tipo' => 'text', 'fic_obrigatorio' => true, 'fic_validacoes' => 'nullable|string|max:120', 'fic_ordem' => 6, 'ficg_id' => 8],
            ['fic_nome' => 'ins_cargo', 'fic_label' => 'Cargo', 'fic_tipo' => 'text', 'fic_obrigatorio' => true, 'fic_validacoes' => 'nullable|string|max:120', 'fic_ordem' => 7, 'ficg_id' => 10],
        ]);
    }


    private function fields(int $formId, array $items): void
    {
        foreach ($items as $f) {
            FormField::updateOrCreate(
                ['form_id' => $formId, 'fic_nome' => $f['fic_nome']],
                [
                    'fic_label' => $f['fic_label'],
                    'fic_tipo' => $f['fic_tipo'],
                    'fic_obrigatorio' => (bool)($f['fic_obrigatorio'] ?? false),
                    'fic_validacoes' => $f['fic_validacoes'] ?? null,
                    'fic_ordem' => $f['fic_ordem'] ?? 0,
                    'ficg_id' => $f['ficg_id'] ?? null, // <<< AQUI
                    'fic_opcoes' => isset($f['fic_opcoes']) ? json_encode($f['fic_opcoes']) : null,
                ]
            );
        }
    }
}
