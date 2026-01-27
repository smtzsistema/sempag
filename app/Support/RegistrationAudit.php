<?php

namespace App\Support;

use App\Models\Registration;
use App\Models\RegistrationLog;
use Illuminate\Http\Request;

class RegistrationAudit
{
    /**
     * Registra mudanças ...
     *
     * @param array<string,array{from:mixed,to:mixed}> $changes
     */
    public static function log(Registration $registration, Request $request, string $actorType, ?int $actorUsuId, array $changes): void
    {
        if (empty($changes)) {
            return;
        }

        RegistrationLog::create([
            'ins_id' => $registration->ins_id,
            'eve_id' => $registration->eve_id,
            'actor_type' => $actorType,
            'actor_usu_id' => $actorUsuId,
            'changes' => $changes,
            'ip' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 1000),
        ]);
    }

    /**
     * Monta um diff simples entre original e novos valores.
     *
     * @param array<string,mixed> $original
     * @param array<string,mixed> $new
     * @return array<string,array{from:mixed,to:mixed}>
     */
    public static function diff(array $original, array $new): array
    {
        $changes = [];

        foreach ($new as $k => $to) {
            $from = $original[$k] ?? null;

            // normaliza arrays para comparação
            $fromNorm = is_array($from) ? $from : (is_string($from) && self::looksJsonArray($from) ? json_decode($from, true) : $from);
            $toNorm = $to;

            if ($fromNorm !== $toNorm) {
                $changes[$k] = ['from' => $fromNorm, 'to' => $toNorm];
            }
        }

        return $changes;
    }

    private static function looksJsonArray(string $value): bool
    {
        $v = trim($value);
        return $v !== '' && ($v[0] === '[' || $v[0] === '{');
    }
}
