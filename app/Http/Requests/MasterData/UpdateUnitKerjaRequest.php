<?php

namespace App\Http\Requests\MasterData;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUnitKerjaRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Get ID from route parameter (apiResource uses singular form with underscore)
        $id = $this->route('unit_kerja');

        return [
            'kode_unit' => 'required|string|max:20|unique:unit_kerjas,kode_unit,' . $id,
            'nama_unit' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'jenis_unit' => 'required|in:fakultas,program_studi,lembaga,unit_pendukung',
            'parent_id' => 'nullable|exists:unit_kerjas,id',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'kode_unit.required' => 'Kode Unit wajib diisi',
            'kode_unit.unique' => 'Kode Unit sudah digunakan',
            'kode_unit.max' => 'Kode Unit maksimal 20 karakter',
            'nama_unit.required' => 'Nama Unit wajib diisi',
            'nama_unit.max' => 'Nama Unit maksimal 255 karakter',
            'jenis_unit.required' => 'Jenis Unit wajib diisi',
            'jenis_unit.in' => 'Jenis Unit harus salah satu dari: fakultas, program_studi, lembaga, unit_pendukung',
            'parent_id.exists' => 'Parent Unit tidak ditemukan',
            'is_active.boolean' => 'Status aktif harus berupa boolean',
        ];
    }
}
