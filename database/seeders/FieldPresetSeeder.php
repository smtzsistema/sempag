<?php

namespace Database\Seeders;

use App\Models\FieldPreset;
use Illuminate\Database\Seeder;

class FieldPresetSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            // -------------------------
            // Dados pessoais (NOVO PADRÃO)
            // -------------------------
            ['group'=>'Dados pessoais','key'=>'ins_nome','label'=>'Nome','type'=>'text','required'=>true,  'validation_rules'=>'required|string|min:3|max:120'],
            ['group'=>'Dados pessoais','key'=>'ins_sobrenome','label'=>'Sobrenome','type'=>'text','required'=>true,'validation_rules'=>'required|string|min:1|max:120'],
            ['group'=>'Dados pessoais','key'=>'ins_nomecracha','label'=>'Nome para Credencial','type'=>'text','required'=>true,'validation_rules'=>'required|string|min:1|max:120'],
            ['group'=>'Dados pessoais','key'=>'ins_tel_celular','label'=>'Celular','type'=>'text','required'=>true,'validation_rules'=>'required|string|min:8|max:30'],
            ['group'=>'Dados pessoais','key'=>'ins_tel_comercial','label'=>'Telefone','type'=>'text','required'=>true,'validation_rules'=>'required|string|min:8|max:30'],
            ['group'=>'Dados pessoais','key'=>'ins_cpf','label'=>'CPF','type'=>'cpf','fic_obrigatorio'=>true, 'fic_validacoes'=>'required|cpf'],



            // -------------------------
            // Empresa
            // -------------------------
            ['group'=>'Empresa','key'=>'ins_instituicao','label'=>'Empresa / Instituição','type'=>'text','required'=>true,'validation_rules'=>'required|string|max:255'],
            ['group'=>'Empresa','key'=>'ins_siglainstituicao','label'=>'Nome Fantasia','type'=>'text','required'=>true,'validation_rules'=>'required|string|max:255'],
            ['group'=>'Empresa','key'=>'ins_cnpj','label'=>'CNPJ','type'=>'cnpj','fic_obrigatorio'=>true, 'fic_validacoes'=>'required|cnpj'],
            ['group'=>'Empresa','key'=>'ins_cargo','label'=>'Cargo','type'=>'text','required'=>true,'validation_rules'=>'required|string|max:255'],
            ['group'=>'Empresa','key'=>'ins_cargo_cred','label'=>'Cargo para Credencial','type'=>'text','required'=>true,'validation_rules'=>'required|string|max:255'],


            // -------------------------
            // Endereço (NOVO PADRÃO)
            // -------------------------
            ['group'=>'Endereço','key'=>'ins_cep','label'=>'CEP','type'=>'cep','required'=>true,'validation_rules'=>'required|string|min:8|max:10'],
            ['group'=>'Endereço','key'=>'ins_pais','label'=>'País','type'=>'text','required'=>true,'validation_rules'=>'required|string|max:120'],
            ['group'=>'Endereço','key'=>'ins_estado','label'=>'Estado','type'=>'text','required'=>true,'validation_rules'=>'required|string|max:120'],
            ['group'=>'Endereço','key'=>'ins_cidade','label'=>'Cidade','type'=>'text','required'=>true,'validation_rules'=>'required|string|max:120'],
            ['group'=>'Endereço','key'=>'ins_bairro','label'=>'Bairro','type'=>'text','required'=>true,'validation_rules'=>'required|string|max:120'],
            ['group'=>'Endereço','key'=>'ins_endereco','label'=>'Endereço','type'=>'text','required'=>true,'validation_rules'=>'required|string|max:255'],
            ['group'=>'Endereço','key'=>'ins_numero','label'=>'Número','type'=>'text','required'=>true,'validation_rules'=>'required|string|max:30'],
            ['group'=>'Endereço','key'=>'ins_complemento','label'=>'Complemento','type'=>'text','required'=>false,'validation_rules'=>'nullable|string|max:120'],


        ];

        // Extras / adicionais (NOVO PADRÃO: ins_adicional1..30)
        for ($i = 1; $i <= 30; $i++) {
            $items[] = [
                'group' => 'Extras',
                'key' => "ins_adicional{$i}",
                'label' => "Extra {$i}",
                'type' => 'text',
                'required' => true,
                'validation_rules' => 'required|string|max:5000',
            ];
        }

        foreach ($items as $it) {
            $row = FieldPreset::query()
                ->where('fic_nome', $it['key'])
                ->first();

            if (!$row) {
                FieldPreset::create($it);
            } else {
                $row->fill($it)->save();
            }
        }
    }
}
