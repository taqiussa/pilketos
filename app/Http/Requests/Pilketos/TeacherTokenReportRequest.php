<?php

namespace App\Http\Requests\Pilketos;

use App\Services\PilketosTeacherTokenReportService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TeacherTokenReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'periode' => ['required', 'integer', 'exists:pilketos_periodes,id'],
            'status' => ['nullable', 'string', Rule::in(PilketosTeacherTokenReportService::STATUSES)],
            'search' => ['nullable', 'string', 'max:100'],
        ];
    }
}
