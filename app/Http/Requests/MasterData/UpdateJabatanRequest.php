<?php

namespace App\Http\Requests\MasterData;

use Illuminate\Foundation\Http\FormRequest;

class UpdateJabatanRequest extends FormRequest
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
        // Get ID from route parameter (apiResource uses singular form)
        $id = $this->route('jabatan');

        return [
            'kode_jabatan' => 'required|string|max:20|unique:jabatans,kode_jabatan,' . $id,
            'nama_jabatan' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'kategori' => 'required|in:struktural,fungsional,dosen,tendik',
            'level' => 'nullable|integer|min:1',
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
            'kode_jabatan.required' => 'Kode Jabatan wajib diisi',
            'kode_jabatan.unique' => 'Kode Jabatan sudah digunakan',
            'kode_jabatan.max' => 'Kode Jabatan maksimal 20 karakter',
            'nama_jabatan.required' => 'Nama Jabatan wajib diisi',
            'nama_jabatan.max' => 'Nama Jabatan maksimal 255 karakter',
            'kategori.required' => 'Kategori wajib dipilih',
            'kategori.in' => 'Kategori harus salah satu dari: struktural, fungsional, dosen, tendik',
            'level.integer' => 'Level harus berupa angka',
            'level.min' => 'Level minimal 1',
            'is_active.boolean' => 'Status aktif harus berupa boolean',
        ];
    }
}
