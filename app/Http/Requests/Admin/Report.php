<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Validator;

class Report extends FormRequest
{
    public const DEFAULT_DAYS = 30;

    public const MAXIMUM_DAYS = 92;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'start_date' => ['nullable', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:start_date'],
        ];
    }

    /** @return list<callable> */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $days = Carbon::parse($this->startDate())->diffInDays(Carbon::parse($this->endDate())) + 1;

                if ($days > self::MAXIMUM_DAYS) {
                    $validator->errors()->add('end_date', 'The date range can cover at most '.self::MAXIMUM_DAYS.' days.');
                }
            },
        ];
    }

    public function startDate(): string
    {
        return $this->input('start_date') ?? now('UTC')->subDays(self::DEFAULT_DAYS - 1)->toDateString();
    }

    public function endDate(): string
    {
        return $this->input('end_date') ?? now('UTC')->toDateString();
    }
}
