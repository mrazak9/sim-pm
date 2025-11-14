<?php

namespace App\Http\Requests\Akreditasi;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdatePeriodeAkreditasiRequest extends FormRequest
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
        return [
            'nama' => 'sometimes|string|max:255',
            'jenis_akreditasi' => 'sometimes|in:institusi,program_studi',
            'lembaga' => 'sometimes|in:BAN-PT,LAM,Internasional',
            'instrumen' => 'sometimes|string|max:50',
            'jenjang' => 'nullable|string|max:50',
            'unit_kerja_id' => 'nullable|exists:unit_kerjas,id',
            'program_studi_id' => 'nullable|exists:program_studis,id',
            'tanggal_mulai' => 'sometimes|date',
            'tanggal_berakhir' => 'sometimes|date|after:tanggal_mulai',
            'deadline_pengumpulan' => 'sometimes|date|after:tanggal_mulai|before:tanggal_berakhir',
            'jadwal_visitasi' => 'nullable|date|after:deadline_pengumpulan',
            'status' => 'sometimes|in:persiapan,pengisian,review,submit,visitasi,selesai',
            'keterangan' => 'nullable|string',
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
            'nama.string' => 'Nama periode akreditasi harus berupa teks',
            'nama.max' => 'Nama periode akreditasi maksimal 255 karakter',
            'jenis_akreditasi.in' => 'Jenis akreditasi harus institusi atau program_studi',
            'lembaga.in' => 'Lembaga akreditasi harus BAN-PT, LAM, atau Internasional',
            'instrumen.string' => 'Instrumen akreditasi harus berupa teks',
            'instrumen.max' => 'Instrumen akreditasi maksimal 50 karakter',
            'jenjang.max' => 'Jenjang maksimal 50 karakter',
            'unit_kerja_id.exists' => 'Unit kerja tidak ditemukan',
            'program_studi_id.exists' => 'Program studi tidak ditemukan',
            'tanggal_mulai.date' => 'Tanggal mulai harus berupa tanggal yang valid',
            'tanggal_berakhir.date' => 'Tanggal berakhir harus berupa tanggal yang valid',
            'tanggal_berakhir.after' => 'Tanggal berakhir harus setelah tanggal mulai',
            'deadline_pengumpulan.date' => 'Deadline pengumpulan harus berupa tanggal yang valid',
            'deadline_pengumpulan.after' => 'Deadline pengumpulan harus setelah tanggal mulai',
            'deadline_pengumpulan.before' => 'Deadline pengumpulan harus sebelum tanggal berakhir',
            'jadwal_visitasi.date' => 'Jadwal visitasi harus berupa tanggal yang valid',
            'jadwal_visitasi.after' => 'Jadwal visitasi harus setelah deadline pengumpulan',
            'status.in' => 'Status tidak valid',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}
