<?php
// © Atia Hegazy — atiaeno.com

namespace App\Services;

use App\Models\Link;

class ShortCodeService
{
    private const CODE_LENGTH = 6;

    private const CHARACTERS = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

    /**
     * Generate a unique short code.
     */
    public function generate(int $length = self::CODE_LENGTH): string
    {
        $maxAttempts = 10;
        $attempts = 0;

        do {
            $code = $this->randomString($length);
            $attempts++;
        } while ($this->exists($code) && $attempts < $maxAttempts);

        if ($attempts >= $maxAttempts) {
            // Increase length if collisions persist
            return $this->generate($length + 1);
        }

        return $code;
    }

    /**
     * Check if code already exists.
     */
    private function exists(string $code): bool
    {
        return Link::where('short_code', $code)
            ->orWhere('custom_alias', $code)
            ->exists();
    }

    /**
     * Generate random string.
     */
    private function randomString(int $length): string
    {
        $result = '';
        $maxIndex = strlen(self::CHARACTERS) - 1;

        for ($i = 0; $i < $length; $i++) {
            $result .= self::CHARACTERS[random_int(0, $maxIndex)];
        }

        return $result;
    }
}
