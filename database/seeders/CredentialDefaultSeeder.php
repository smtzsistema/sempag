<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CredentialDefaultSeeder extends Seeder
{
    public function run(): void
    {
        // tenta pegar um evento (prioriza id 1)
        $eventId = DB::table('tbl_eventos')->where('eve_id', 1)->value('eve_id')
            ?? DB::table('tbl_eventos')->min('eve_id');

        if (!$eventId) {
            return;
        }

        // tenta pegar uma categoria do evento (prioriza id 1)
        $catId = DB::table('tbl_categorias')
            ->where('eve_id', $eventId)
            ->where('cat_id', 1)
            ->value('cat_id')
            ?? DB::table('tbl_categorias')->where('eve_id', $eventId)->min('cat_id');

        if (!$catId) {
            return;
        }

        // evita duplicar
        $exists = DB::table('tbl_credencial')
            ->where('eve_id', $eventId)
            ->where('cre_tipo', 'A4')
            ->where('cre_nome', 'credencial visitante')
            ->exists();

        if ($exists) {
            return;
        }

        $cfgJson = <<<JSON
{"page": {"h": 1123, "w": 794}, "elements": [{"h": 44, "w": 320, "x": 40, "y": 180, "id": "e984ad68adb46719bfbd3c1a0", "type": "text", "align": "center", "color": "#050505", "limit": 16, "source": "reg:ins_nomecracha", "fontSize": 24, "showLabel": false, "fontFamily": "Arial", "fontWeight": "700", "formatMode": "upper", "fontWhenOver": 0, "barcodeFormat": "CODE39", "validationMode": "first_last"}, {"h": 80, "w": 80, "x": 42, "y": 336, "id": "e37ed96a9cf335819bfbd3e2a8", "type": "qrcode", "align": "left", "color": "#ffffff", "limit": 0, "source": "reg:ins_id", "fontSize": 24, "showLabel": false, "fontFamily": "Arial", "fontWeight": "700", "formatMode": "none", "fontWhenOver": 0, "barcodeFormat": "CODE39", "validationMode": "none"}, {"h": 20, "w": 120, "x": 226, "y": 350, "id": "e03d69a40b6c2c19bfbd3ed1e", "type": "barcode", "align": "left", "color": "#ffffff", "limit": 0, "source": "reg:ins_id", "fontSize": 24, "showLabel": true, "fontFamily": "Arial", "fontWeight": "700", "formatMode": "none", "fontWhenOver": 0, "barcodeFormat": "CODE39", "validationMode": "none"}, {"h": 44, "w": 320, "x": 40, "y": 228, "id": "ebdf334e9cacea19bfbd6527f", "type": "text", "align": "center", "color": "#050505", "limit": 16, "source": "reg:ins_cargo", "fontSize": 24, "showLabel": false, "fontFamily": "Arial", "fontWeight": "700", "formatMode": "upper", "fontWhenOver": 0, "barcodeFormat": "CODE39", "validationMode": "first_last"}, {"h": 44, "w": 320, "x": 39, "y": 278, "id": "e35372f4f178af19bfbd6bdc6", "type": "text", "align": "center", "color": "#030303", "limit": 16, "source": "reg:ins_siglainstituicao", "fontSize": 24, "showLabel": false, "fontFamily": "Arial", "fontWeight": "700", "formatMode": "upper", "fontWhenOver": 12, "barcodeFormat": "CODE39", "validationMode": "first_last"}]}
JSON;

        $cfg = json_decode($cfgJson, true);
        if (!is_array($cfg)) {
            $cfg = [];
        }

        DB::table('tbl_credencial')->insert([
            'eve_id' => $eventId,
            'cre_nome' => 'credencial visitante',
            'cre_tipo' => 'A4',
            'cat_id' => json_encode([(int) $catId]),
            'cre_fundo' => 'demo2025/credenciais/uhEp5nvONKxk26Vrn1TXAnMMUSUcSbhfhBeWF3eY.jpg',
            'cre_espelhar' => 'S',
            'cre_config' => json_encode($cfg, JSON_UNESCAPED_UNICODE),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
