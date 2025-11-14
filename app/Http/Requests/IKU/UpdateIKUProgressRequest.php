<?php

namespace App\Http\Requests\IKU;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateIKUProgressRequest extends FormRequest
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
            'iku_target_id' => 'required|exists:iku_targets,id',
            'tanggal_capaian' => 'required|date',
            'nilai_capaian' => 'required|numeric|min:0',
            'persentase_capaian' => 'nullable|numeric|min:0|max:100',
            'keterangan' => 'nullable|string',
            'bukti_dokumen' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:5120',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'iku_target_id.required' => 'IKU Target wajib dipilih',
            'iku_target_id.exists' => 'IKU Target tidak ditemukan',
            'tanggal_capaian.required' => 'Tanggal capaian wajib diisi',
            'tanggal_capaian.date' => 'Format tanggal tidak valid',
            'nilai_capaian.required' => 'Nilai capaian wajib diisi',
            'nilai_capaian.numeric' => 'Nilai capaian harus berupa angka',
            'nilai_capaian.min' => 'Nilai capaian minimal 0',
            'persentase_capaian.numeric' => 'Persentase capaian harus berupa angka',
            'persentase_capaian.min' => 'Persentase capaian minimal 0',
            'persentase_capaian.max' => 'Persentase capaian maksimal 100',
            'bukti_dokumen.file' => 'Bukti dokumen harus berupa file',
            'bukti_dokumen.mimes' => 'Bukti dokumen harus berformat: pdf, doc, docx, xls, xlsx, jpg, jpeg, png',
            'bukti_dokumen.max' => 'Ukuran bukti dokumen maksimal 5MB',
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
