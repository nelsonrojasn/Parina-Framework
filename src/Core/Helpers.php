<?php
declare(strict_types=1);
if (!function_exists('h')) {
    /**
     * Escapes HTML characters in a string to prevent XSS vulnerability.
     */
    function h(?string $value): string
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }
}
