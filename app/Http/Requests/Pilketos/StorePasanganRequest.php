<?php

namespace App\Http\Requests\Pilketos;

use Illuminate\Foundation\Http\FormRequest;

class StorePasanganRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return ['periode_id' => ['required', 'integer', 'exists:pilketos_periodes,id'], 'kategori' => ['required', 'in:L,P'], 'nomor_urut' => ['required', 'integer', 'min:1', 'max:999'], 'ketua_nis' => ['required', 'integer', 'different:wakil_nis'], 'wakil_nis' => ['required', 'integer'], 'foto_pasangan' => ['nullable', 'string', 'max:500'], 'visi' => ['nullable', 'string'], 'misi' => ['nullable', 'string'], 'deskripsi' => ['nullable', 'string'], 'is_active' => ['boolean']];
    }
}
