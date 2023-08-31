<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Carbon;

class DateInterval implements ValidationRule
{
    public function __construct(public string $startDate, public string $endDate, public int $numDays = 7)
    {
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (Carbon::parse($this->endDate)->diffInDays(Carbon::parse($this->startDate)) > $this->numDays) {
            $fail('The date range must be less than or equal to ' . $this->numDays);
        }
    }
}
