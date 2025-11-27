<?php

namespace App\Http\Requests\Akreditasi;

use Illuminate\Foundation\Http\FormRequest;

class StoreInstrumenAkreditasiRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'kode' => 'required|string|max:50|unique:instrumen_akreditasis,kode',
            'nama' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'jenis' => 'required|in:program_studi,institusi,both',
            'lembaga' => 'required|in:BAN-PT,LAM,Internasional',
            'tahun_berlaku' => 'required|integer|min:2000|max:2100',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'kode' => 'Kode Instrumen',
            'nama' => 'Nama Instrumen',
            'deskripsi' => 'Deskripsi',
            'jenis' => 'Jenis Akreditasi',
            'lembaga' => 'Lembaga',
            'tahun_berlaku' => 'Tahun Berlaku',
            'is_active' => 'Status Aktif',
        ];
    }
}
