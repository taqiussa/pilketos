<?php

namespace App\Http\Requests\Pilketos;

use Illuminate\Foundation\Http\FormRequest;

class StorePeriodeRequest extends FormRequest
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
        return ['tahun' => ['required', 'regex:/^\\d{4} \\/ \\d{4}$/'], 'nama' => ['required', 'string', 'max:150'], 'status' => ['required', 'in:draft,aktif,ditutup'], 'voting_mulai' => ['nullable', 'date'], 'voting_selesai' => ['nullable', 'date', 'after_or_equal:voting_mulai'], 'hasil_publik' => ['boolean'], 'judul_landing' => ['nullable', 'string', 'max:150'], 'deskripsi_landing' => ['nullable', 'string']];
    }
}
