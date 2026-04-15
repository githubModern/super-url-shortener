<?php

// © Atia Hegazy — atiaeno.com

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Propaganistas\LaravelDisposableEmail\DisposableDomains;

class NotDisposableEmail implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $disposable = app(DisposableDomains::class);

        if ($disposable->isDisposable($value)) {
            $fail('Disposable email addresses are not allowed. Please use a permanent email address.');
        }
    }
}
