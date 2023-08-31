<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;

class NeoFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */

     public function __construct(Request $request ,public int $numDays = 7)
    {
        //Explode date to get startDate and endDate
        $dates = explode(' - ', $request->filter_date);

        $request->request->add([
                'start_date' => date("Y-m-d", strtotime($dates[0])),
                'end_date' => date("Y-m-d", strtotime($dates[1])),
            ]);

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
        $startDate = $validator->getData()['start_date'];
        $endtDate = $validator->getData()['end_date'];

        $validator->after(
        function ($validator) use ($startDate,$endtDate)
        {
            if (Carbon::parse($endtDate)->diffInDays(Carbon::parse($startDate)) > $this->numDays) {
                $validator->errors()->add(
                'filter_date',
                'The date range must be less than or equal to ' . $this->numDays. ' days'
                );
            }
        }
        );
    }
}
