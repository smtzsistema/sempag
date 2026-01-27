<?php

if (! function_exists('sanitize_public_text')) {
    function sanitize_public_text(?string $text): ?string
    {
        if ($text === null) return null;

        $text = str_replace(["\r\n", "\r"], "\n", $text);

        if (class_exists('\Normalizer')) {
            $text = \Normalizer::normalize($text, \Normalizer::FORM_KC) ?? $text;
        }

        $text = preg_replace('/[\p{Cc}&&[^\n\t]]/u', '', $text);
        $text = preg_replace('/\p{Cf}/u', '', $text);
        $text = preg_replace('/\p{M}+/u', '', $text);

        $text = preg_replace('/[ \t]+/u', ' ', $text);
        $text = preg_replace("/\n{3,}/u", "\n\n", $text);

        return trim($text);
    }
}
