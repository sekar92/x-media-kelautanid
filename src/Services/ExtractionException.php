<?php
declare(strict_types=1);

namespace App\Services;

/**
 * Galat ekstraksi dengan pesan ramah pengguna.
 * $final = true berarti tier fallback berikutnya tidak perlu dicoba
 * (mis. akun privat atau unggahan tidak ditemukan).
 */
final class ExtractionException extends \RuntimeException
{
    public function __construct(string $message, private bool $final = false)
    {
        parent::__construct($message);
    }

    public function isFinal(): bool
    {
        return $this->final;
    }
}
