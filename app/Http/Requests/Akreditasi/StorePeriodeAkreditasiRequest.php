<?php

namespace App\Http\Requests\Akreditasi;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StorePeriodeAkreditasiRequest extends FormRequest
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
            'nama' => 'required|string|max:255',
            'jenis_akreditasi' => 'required|in:institusi,program_studi',
            'lembaga' => 'required|in:BAN-PT,LAM,Internasional',
            'instrumen' => 'required|string|max:50',
            'jenjang' => 'nullable|string|max:50',
            'unit_kerja_id' => 'nullable|exists:unit_kerjas,id',
            'program_studi_id' => 'nullable|exists:program_studis,id',
            'tanggal_mulai' => 'required|date',
            'tanggal_berakhir' => 'required|date|after:tanggal_mulai',
            'deadline_pengumpulan' => 'required|date|after:tanggal_mulai|before:tanggal_berakhir',
            'jadwal_visitasi' => 'nullable|date|after:deadline_pengumpulan',
            'status' => 'nullable|in:persiapan,pengisian,review,submit,visitasi,selesai',
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
            'nama.required' => 'Nama periode akreditasi wajib diisi',
            'nama.max' => 'Nama periode akreditasi maksimal 255 karakter',
            'jenis_akreditasi.required' => 'Jenis akreditasi wajib dipilih',
            'jenis_akreditasi.in' => 'Jenis akreditasi harus institusi atau program_studi',
            'lembaga.required' => 'Lembaga akreditasi wajib dipilih',
            'lembaga.in' => 'Lembaga akreditasi harus BAN-PT, LAM, atau Internasional',
            'instrumen.required' => 'Instrumen akreditasi wajib diisi',
            'instrumen.max' => 'Instrumen akreditasi maksimal 50 karakter',
            'jenjang.max' => 'Jenjang maksimal 50 karakter',
            'unit_kerja_id.exists' => 'Unit kerja tidak ditemukan',
            'program_studi_id.exists' => 'Program studi tidak ditemukan',
            'tanggal_mulai.required' => 'Tanggal mulai wajib diisi',
            'tanggal_mulai.date' => 'Tanggal mulai harus berupa tanggal yang valid',
            'tanggal_berakhir.required' => 'Tanggal berakhir wajib diisi',
            'tanggal_berakhir.date' => 'Tanggal berakhir harus berupa tanggal yang valid',
            'tanggal_berakhir.after' => 'Tanggal berakhir harus setelah tanggal mulai',
            'deadline_pengumpulan.required' => 'Deadline pengumpulan wajib diisi',
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
