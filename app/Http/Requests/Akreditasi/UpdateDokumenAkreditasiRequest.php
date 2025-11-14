<?php

namespace App\Http\Requests\Akreditasi;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateDokumenAkreditasiRequest extends FormRequest
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
            'periode_akreditasi_id' => 'sometimes|exists:periode_akreditasis,id',
            'nama_dokumen' => 'sometimes|string|max:255',
            'nomor_dokumen' => 'nullable|string|max:100',
            'jenis_dokumen' => 'sometimes|in:kebijakan,pedoman,manual,sop,formulir,instruksi_kerja,laporan,sertifikat,sk,lainnya',
            'deskripsi' => 'nullable|string',
            'file' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:51200',
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
            'periode_akreditasi_id.exists' => 'Periode akreditasi tidak ditemukan',
            'nama_dokumen.string' => 'Nama dokumen harus berupa teks',
            'nama_dokumen.max' => 'Nama dokumen maksimal 255 karakter',
            'nomor_dokumen.string' => 'Nomor dokumen harus berupa teks',
            'nomor_dokumen.max' => 'Nomor dokumen maksimal 100 karakter',
            'jenis_dokumen.in' => 'Jenis dokumen tidak valid. Pilih salah satu: kebijakan, pedoman, manual, sop, formulir, instruksi_kerja, laporan, sertifikat, sk, lainnya',
            'deskripsi.string' => 'Deskripsi harus berupa teks',
            'file.file' => 'File harus berupa file yang valid',
            'file.mimes' => 'File harus berformat: pdf, doc, docx, xls, xlsx, jpg, jpeg, atau png',
            'file.max' => 'Ukuran file maksimal 50 MB',
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
