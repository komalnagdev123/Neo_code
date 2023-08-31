<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class NeoFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */

     public function __construct(public int $numDays = 7)
    {
    }

    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {

        return [
            'filter_date' => ['required'],
        ];

    }

    public function withValidator($validator)
    {
        $filter_date = $validator->getData()['filter_date'] ?? '';

            $validator->after(
            function ($validator) use ($filter_date)
            {
                //Explode date to get startDate and endDate
                $dates = explode(' - ', $filter_date);

                if (Carbon::parse($dates[1])->diffInDays(Carbon::parse($dates[0])) > $this->numDays) {
                    $validator->errors()->add(
                    'filter_date',
                    'The date range must be less than or equal to ' . $this->numDays
                    );
                }
            }
        );
    }
}
