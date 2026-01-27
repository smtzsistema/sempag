<?php
    namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CnpjController extends Controller
{
    public function lookup(Request $request)
    {
        $cnpj = preg_replace('/\D+/', '', (string) $request->query('cnpj'));
        if (strlen($cnpj) !== 14) {
            return response()->json(['code'=>'error','message'=>'CNPJ inválido'], 422);
        }

        // TODO: integrar com sua api_cnpj.php / serviço real
        // Por enquanto retorna error pra não quebrar:
        return response()->json(['code'=>'error','message'=>'Serviço CNPJ não configurado ainda'], 501);
    }
}
