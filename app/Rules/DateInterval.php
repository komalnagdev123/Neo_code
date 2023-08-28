<?php

namespace App\Rules;

use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class DateInterval implements ValidationRule
{
    public function __construct(public int $numDays = 7)
    {
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (request()->end_date->diffInDays(request()->start_date) > $this->numDays) {
            $fail('The date range must be less than or equal to ' . $this->numDays);
        }
    }
}
