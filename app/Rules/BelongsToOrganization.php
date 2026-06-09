<?php

namespace App\Rules;

use App\Models\Organization;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class BelongsToOrganization implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function __construct(
        protected Organization $organization,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $exists = $this->organization
            ->members()
            ->where('users.id', $value)
            ->exists();

        if (! $exists) {
            $fail('The selected user does not belong to this organization.');
        }
    }
}
