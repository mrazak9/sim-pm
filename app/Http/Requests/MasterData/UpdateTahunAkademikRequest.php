<?php

namespace App\Http\Requests\MasterData;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTahunAkademikRequest extends FormRequest
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
        // apiResource uses the parameter name from the route, which is 'tahun_akademik'
        $id = $this->route('tahun_akademik');

        return [
            'kode_tahun' => 'required|string|max:20|unique:tahun_akademiks,kode_tahun,' . $id,
            'nama_tahun' => 'required|string|max:255',
            'semester' => 'required|in:ganjil,genap',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after:tanggal_mulai',
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
            'kode_tahun.required' => 'Kode Tahun Akademik wajib diisi',
            'kode_tahun.unique' => 'Kode Tahun Akademik sudah digunakan',
            'kode_tahun.max' => 'Kode Tahun Akademik maksimal 20 karakter',
            'nama_tahun.required' => 'Nama Tahun Akademik wajib diisi',
            'nama_tahun.max' => 'Nama Tahun Akademik maksimal 255 karakter',
            'semester.required' => 'Semester wajib dipilih',
            'semester.in' => 'Semester harus salah satu dari: ganjil, genap',
            'tanggal_mulai.required' => 'Tanggal mulai wajib diisi',
            'tanggal_mulai.date' => 'Format tanggal mulai tidak valid',
            'tanggal_selesai.required' => 'Tanggal selesai wajib diisi',
            'tanggal_selesai.date' => 'Format tanggal selesai tidak valid',
            'tanggal_selesai.after' => 'Tanggal selesai harus setelah tanggal mulai',
            'is_active.boolean' => 'Status aktif harus berupa boolean',
        ];
    }
}
