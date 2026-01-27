<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;

class AppServiceProvider extends ServiceProvider
{

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Validator::extend('cpf', function ($attribute, $value) {
            $cpf = preg_replace('/\D+/', '', (string)$value);

            if (strlen($cpf) !== 11) return false;
            if (preg_match('/^(\d)\1{10}$/', $cpf)) return false; // 00000000000 etc

            // dígito 1
            $sum = 0;
            for ($i = 0, $w = 10; $i < 9; $i++, $w--) $sum += (int)$cpf[$i] * $w;
            $d1 = (11 - ($sum % 11));
            $d1 = ($d1 >= 10) ? 0 : $d1;

            // dígito 2
            $sum = 0;
            for ($i = 0, $w = 11; $i < 10; $i++, $w--) $sum += (int)$cpf[$i] * $w;
            $d2 = (11 - ($sum % 11));
            $d2 = ($d2 >= 10) ? 0 : $d2;

            return ((int)$cpf[9] === $d1) && ((int)$cpf[10] === $d2);
        });

        Validator::extend('cnpj', function ($attribute, $value) {
            $cnpj = preg_replace('/\D+/', '', (string)$value);

            if (strlen($cnpj) !== 14) return false;
            if (preg_match('/^(\d)\1{13}$/', $cnpj)) return false;

            $calc = function ($base) {
                $weights = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
                if (strlen($base) === 13) array_unshift($weights, 6);

                $sum = 0;
                for ($i = 0; $i < count($weights); $i++) {
                    $sum += (int)$base[$i] * $weights[$i];
                }
                $r = $sum % 11;
                return ($r < 2) ? 0 : (11 - $r);
            };

            $base12 = substr($cnpj, 0, 12);
            $d1 = $calc($base12);
            $base13 = $base12 . $d1;
            $d2 = $calc($base13);

            return ((int)$cnpj[12] === $d1) && ((int)$cnpj[13] === $d2);
        });

        // Mensagens (se você não quiser mexer em lang/pt_BR/validation.php)
        Validator::replacer('cpf', fn() => 'CPF inválido.');
        Validator::replacer('cnpj', fn() => 'CNPJ inválido.');
    }
}
