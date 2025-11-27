<?php

namespace App\Http\Requests\MasterData;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProgramStudiRequest extends FormRequest
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
        $id = $this->route('program_studi');

        return [
            'kode_prodi' => 'required|string|max:20|unique:program_studis,kode_prodi,' . $id,
            'nama_prodi' => 'required|string|max:255',
            'unit_kerja_id' => 'required|exists:unit_kerjas,id',
            'jenjang' => 'required|in:D3,D4,S1,S2,S3',
            'akreditasi' => 'nullable|string|max:50',
            'tanggal_akreditasi' => 'nullable|date',
            'kuota_mahasiswa' => 'nullable|integer|min:0',
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
            'kode_prodi.required' => 'Kode Program Studi wajib diisi',
            'kode_prodi.unique' => 'Kode Program Studi sudah digunakan',
            'kode_prodi.max' => 'Kode Program Studi maksimal 20 karakter',
            'nama_prodi.required' => 'Nama Program Studi wajib diisi',
            'nama_prodi.max' => 'Nama Program Studi maksimal 255 karakter',
            'unit_kerja_id.required' => 'Unit Kerja wajib dipilih',
            'unit_kerja_id.exists' => 'Unit Kerja tidak ditemukan',
            'jenjang.required' => 'Jenjang wajib dipilih',
            'jenjang.in' => 'Jenjang harus salah satu dari: D3, D4, S1, S2, S3',
            'akreditasi.max' => 'Akreditasi maksimal 50 karakter',
            'tanggal_akreditasi.date' => 'Format tanggal akreditasi tidak valid',
            'kuota_mahasiswa.integer' => 'Kuota mahasiswa harus berupa angka',
            'kuota_mahasiswa.min' => 'Kuota mahasiswa tidak boleh negatif',
            'is_active.boolean' => 'Status aktif harus berupa boolean',
        ];
    }
}
