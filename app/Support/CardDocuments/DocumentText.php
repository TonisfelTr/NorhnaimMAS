<?php

namespace App\Support\CardDocuments;

final class DocumentText
{
    /** Текст из старых HTML-редакторов. Выводить только через {{ ... }}. */
    public static function plain(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if (!is_scalar($value) && !$value instanceof \Stringable) {
            return '';
        }

        $text = (string) $value;
        $text = preg_replace('~<(script|style)\b[^>]*>.*?</\1\s*>~is', '', $text) ?? '';
        $text = preg_replace('~<br\s*/?>|</(?:p|div|li|h[1-6]|tr|section)>~i', "\n", $text) ?? '';
        $text = preg_replace('~</(?:td|th)>~i', "\t", $text) ?? '';
        $text = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = str_replace(["\r\n", "\r", "\xc2\xa0"], ["\n", "\n", ' '], $text);

        return trim(preg_replace('/\n[ \t]*\n(?:[ \t]*\n)+/', "\n\n", $text) ?? '');
    }
}
