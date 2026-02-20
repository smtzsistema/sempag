<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Event;
use App\Models\Registration;
use App\Models\RegistrationAnswer;
use App\Support\RegistrationPhoto;

class AttendeeCertificateController extends Controller
{
    private function currentRegistration(Event $event): Registration
    {
        $insId = (int) session('attendee.ins_id');

        return Registration::where('ins_id', $insId)
            ->where('eve_id', $event->eve_id)
            ->firstOrFail();
    }

    private function certificatesForRegistration(Event $event, Registration $registration)
    {
        $catId = (int) ($registration->cat_id ?? 0);

        return Certificate::query()
            ->where('eve_id', $event->eve_id)
            ->where('cer_tipo', 'A4')
            ->whereJsonContains('cat_id', $catId)
            ->orderByDesc('cer_id')
            ->get();
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'S' => 'Aprovada',
            'E' => 'Em análise',
            'R' => 'Reprovada',
            'N' => 'Excluída',
            default => $status,
        };
    }

    public function entry(Event $event)
    {
        $registration = $this->currentRegistration($event);
        $status = (string) ($registration->ins_aprovado ?? '');

        if ($status !== 'S') {
            return redirect()
                ->route('public.attendee.area', $event)
                ->with('error', 'Certificado indisponível. Status da inscrição: ' . $this->statusLabel($status) . '.');
        }

        $certs = $this->certificatesForRegistration($event, $registration);

        if ($certs->count() === 1) {
            return redirect()->route('public.attendee.certificate.print', [$event, $certs->first()->cer_id]);
        }

        if ($certs->count() > 1) {
            return redirect()->route('public.attendee.certificate.choose', $event);
        }

        return redirect()
            ->route('public.attendee.area', $event)
            ->with('error', 'Nenhum certificado disponível pra sua categoria.');
    }

    public function choose(Event $event)
    {
        $registration = $this->currentRegistration($event);
        $status = (string) ($registration->ins_aprovado ?? '');

        if ($status !== 'S') {
            return redirect()
                ->route('public.attendee.area', $event)
                ->with('error', 'Certificado indisponível. Status da inscrição: ' . $this->statusLabel($status) . '.');
        }

        $certificates = $this->certificatesForRegistration($event, $registration);

        if ($certificates->count() === 1) {
            return redirect()->route('public.attendee.certificate.print', [$event, $certificates->first()->cer_id]);
        }

        if ($certificates->isEmpty()) {
            return redirect()
                ->route('public.attendee.area', $event)
                ->with('error', 'Nenhum certificado disponível pra sua categoria.');
        }

        return view('public.attendee.certificate.choose', compact('event', 'registration', 'certificates'));
    }

    public function print(Event $event, int $cer_id)
    {
        $certificate = Certificate::where('cer_id', $cer_id)->firstOrFail();
        $registration = $this->currentRegistration($event);
        $status = (string) ($registration->ins_aprovado ?? '');

        if ($status !== 'S') {
            return redirect()
                ->route('public.attendee.area', $event)
                ->with('error', 'Certificado indisponível. Status da inscrição: ' . $this->statusLabel($status) . '.');
        }

        // segurança: certificado tem que ser do evento
        if ((int) $certificate->eve_id !== (int) $event->eve_id) {
            return redirect()
                ->route('public.attendee.area', $event)
                ->with('error', 'Esse certificado não pertence a este evento.');
        }

        // segurança: categoria tem que bater
        $catId = (int) ($registration->cat_id ?? 0);
        $cats = is_array($certificate->cat_id)
            ? $certificate->cat_id
            : (is_string($certificate->cat_id) ? json_decode($certificate->cat_id, true) : []);

        $cats = is_array($cats) ? array_map('intval', $cats) : [];

        if (!in_array($catId, $cats, true)) {
            return redirect()
                ->route('public.attendee.area', $event)
                ->with('error', 'Esse certificado não está disponível pra sua categoria.');
        }

        $cfg = is_array($certificate->cer_config)
            ? $certificate->cer_config
            : (is_string($certificate->cer_config) ? json_decode($certificate->cer_config, true) : []);

        if (!is_array($cfg)) $cfg = [];

        // ✅ Certificado A4 deitado (default). Se tiver salvo errado (1920x1080), ainda imprime,
        // mas o ideal é corrigir no admin depois.
        $pageW = (int) (($cfg['page']['w'] ?? 1920));
        $pageH = (int) (($cfg['page']['h'] ?? 1080));
        $elements = is_array($cfg['elements'] ?? null) ? $cfg['elements'] : [];

        $answers = RegistrationAnswer::query()
            ->where('eve_id', $event->eve_id)
            ->where('ins_id', $registration->ins_id)
            ->get()
            ->keyBy('fic_id');

        $resolved = array_map(function ($el) use ($registration, $answers) {
            if (!is_array($el)) return $el;

            $src = (string) ($el['source'] ?? '');
            $raw = $this->resolveSource($src, $registration, $answers);

            if (($el['type'] ?? '') === 'photo') {
                $el['value'] = trim((string) $raw);
                return $el;
            }

            [$val, $effectiveFont] = $this->applyCertificateRules((string) $raw, $el);

            $el['value'] = $val;
            if ($effectiveFont !== null) {
                $el['effectiveFontSize'] = $effectiveFont;
            }

            return $el;
        }, $elements);

        $bgUrl = null;
        if (!empty($certificate->cer_fundo)) {
            $bgUrl = asset('storage/' . $certificate->cer_fundo);
        }

        $mirror = ((string) ($certificate->cer_espelhar ?? 'N')) === 'S';

        return view('public.attendee.certificate.print', compact(
            'event',
            'registration',
            'certificate',
            'pageW',
            'pageH',
            'resolved',
            'bgUrl',
            'mirror'
        ));
    }

    private function applyCertificateRules(string $value, array $el): array
    {
        $v = trim($value);

        $formatMode = (string) ($el['formatMode'] ?? 'none');
        $validationMode = (string) ($el['validationMode'] ?? 'none');

        $limit = (int) ($el['limit'] ?? 0);
        $fontWhenOver = (int) ($el['fontWhenOver'] ?? 0);

        $effectiveFont = null;

        if ($limit > 0 && $v !== '') {
            $len = mb_strlen($v, 'UTF-8');

            if ($validationMode === 'first_last' && $len > $limit) {
                $words = preg_split('/\s+/u', $v, -1, PREG_SPLIT_NO_EMPTY);
                if ($words && count($words) >= 2) {
                    $v = $words[0] . ' ' . $words[count($words) - 1];
                } elseif ($words && count($words) === 1) {
                    $v = $words[0];
                }
            }

            if ($validationMode === 'limit' && $len > $limit) {
                $v = mb_substr($v, 0, $limit, 'UTF-8');
            }

            if ($validationMode === 'limit_font' && $len > $limit && $fontWhenOver > 0) {
                $effectiveFont = $fontWhenOver;
            }
        }

        if ($v !== '') {
            if ($formatMode === 'upper') {
                $v = mb_strtoupper($v, 'UTF-8');
            } elseif ($formatMode === 'title') {
                $v = mb_convert_case($v, MB_CASE_TITLE, 'UTF-8');
            }
        }

        return [$v, $effectiveFont];
    }

    private function resolveSource(string $source, Registration $reg, $answers): string
    {
        $source = trim($source);
        if ($source === '') return '';

        if (str_starts_with($source, 'reg:')) {
            $key = substr($source, 4);

            if ($key === 'ins_id') return (string) ($reg->ins_id ?? '');
            if ($key === 'ins_foto') return (string) (RegistrationPhoto::activeUrl($reg) ?? '');
            if ($key === 'ins_token') return (string) ($reg->ins_token ?? '');

            $v = $reg->getAttribute($key);
            if (is_null($v)) return '';
            if (is_array($v)) return implode('; ', array_map('strval', $v));
            return (string) $v;
        }

        if (str_starts_with($source, 'ans:')) {
            $id = (int) substr($source, 4);
            if ($id <= 0) return '';

            $a = $answers->get($id);
            if (!$a) return '';

            $txt = (string) ($a->res_valor_texto ?? '');
            if (trim($txt) !== '') return $txt;

            $j = $a->res_valor_json;
            if (is_array($j)) {
                $isList = array_keys($j) === range(0, count($j) - 1);
                if ($isList) {
                    $vals = array_values(array_filter(array_map(fn($x) => trim((string) $x), $j), fn($x) => $x !== ''));
                    return implode('; ', $vals);
                }
                return json_encode($j, JSON_UNESCAPED_UNICODE);
            }

            return '';
        }

        return '';
    }
}
