<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CnpjController extends Controller
{
    public function show(Request $request)
    {
        $cnpj = preg_replace('/\D+/', '', (string) $request->query('cnpj'));

        if (strlen($cnpj) !== 14) {
            return response()->json(['code' => 'error', 'message' => 'CNPJ inválido'], 422);
        }

        // ⚠️ Aqui você decide de onde vem os dados do CNPJ
        // Exemplo usando ReceitaWS (pode ter limite/instabilidade)
        // Você pode trocar depois por BrasilAPI, CNPJa, etc.
        $url = "https://receitaws.com.br/v1/cnpj/{$cnpj}";

        try {
            $json = @file_get_contents($url);
            if (!$json) {
                return response()->json(['code' => 'error', 'message' => 'Falha ao consultar CNPJ'], 502);
            }

            $data = json_decode($json, true);

            if (!is_array($data) || ($data['status'] ?? '') === 'ERROR') {
                return response()->json(['code' => 'error', 'message' => $data['message'] ?? 'CNPJ não encontrado'], 404);
            }

            // Normaliza pro formato que seu JS espera (j.data.*)
            return response()->json([
                'code' => 'success',
                'data' => [
                    'razao_social' => $data['nome'] ?? null,
                    'logradouro'   => $data['logradouro'] ?? null,
                    'numero'       => $data['numero'] ?? null,
                    'bairro'       => $data['bairro'] ?? null,
                    'cidade'       => $data['municipio'] ?? null,
                    'estado'       => $data['uf'] ?? null,
                    'cep'          => $data['cep'] ?? null,
                    'pais'         => 'Brasil',
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json(['code' => 'error', 'message' => 'Erro interno'], 500);
        }
    }
}
