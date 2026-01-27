<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LetterDefaultSeeder extends Seeder
{
    public function run(): void
    {
        // tenta pegar um evento (prioriza id 1)
        $eventId = DB::table('tbl_eventos')->where('eve_id', 1)->value('eve_id')
            ?? DB::table('tbl_eventos')->min('eve_id');

        if (!$eventId) {
            // sem evento, não faz nada
            return;
        }

        // tenta pegar uma categoria do evento (prioriza id 1)
        $catId = DB::table('tbl_categorias')
            ->where('eve_id', $eventId)
            ->where('cat_id', 1)
            ->value('cat_id')
            ?? DB::table('tbl_categorias')->where('eve_id', $eventId)->min('cat_id');

        if (!$catId) {
            // sem categoria, não faz nada
            return;
        }

        // evita duplicar: uma carta padrão por evento + tipo + idioma + descricao
        $exists = DB::table('tbl_cartas')
            ->where('eve_id', $eventId)
            ->where('car_tipo', 'S')
            ->where('car_trad', 'pt')
            ->where('car_descricao', 'Confirmação de Inscrição')
            ->exists();

        if ($exists) {
            return;
        }

        $html = <<<HTML
<p><img src="http://127.0.0.1:8000/storage/demo2025/banner/zUJfTv2NGMouVKxrLPWqNWOjO5vkcq9I8GUChVG0.png" alt="" width="798" height="243"></p>
<p>Parabens {{nome}} você foi cadastrado com sucesso e pode participar do evento {{evento}}</p>
<p>&nbsp;</p>
<p><a title="Para acessar sua area clique aqui" href="http://127.0.0.1:8000/e/demo2025/ja-sou-inscrito" target="_blank" rel="noopener">Para acessar sua area clique aqui</a></p>
HTML;


        DB::table('tbl_cartas')->insert([
            'eve_id' => $eventId,
            'car_descricao' => 'Confirmação de Inscrição',
            'car_assunto' => 'Confirmação de Inscrição',
            'car_texto' => $html,
            // JSON array (cat_id agora é json)
            'cat_id' => json_encode([(int)$catId]),
            'car_copia' => null,
            'car_copiac' => null,
            'car_responderto' => null,
            'car_tipo' => 'S',
            'car_trad' => 'pt',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
