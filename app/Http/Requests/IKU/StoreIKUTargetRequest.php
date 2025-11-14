<?php

namespace App\Http\Requests\IKU;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreIKUTargetRequest extends FormRequest
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
            'iku_id' => 'required|exists:ikus,id',
            'tahun_akademik_id' => 'required|exists:tahun_akademiks,id',
            'unit_kerja_id' => 'nullable|exists:unit_kerjas,id',
            'program_studi_id' => 'nullable|exists:program_studis,id',
            'target_value' => 'required|numeric|min:0',
            'periode' => 'required|in:tahunan,semester_1,semester_2,triwulan_1,triwulan_2,triwulan_3,triwulan_4',
            'keterangan' => 'nullable|string',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'iku_id.required' => 'IKU wajib dipilih',
            'iku_id.exists' => 'IKU tidak ditemukan',
            'tahun_akademik_id.required' => 'Tahun akademik wajib dipilih',
            'tahun_akademik_id.exists' => 'Tahun akademik tidak ditemukan',
            'unit_kerja_id.exists' => 'Unit kerja tidak ditemukan',
            'program_studi_id.exists' => 'Program studi tidak ditemukan',
            'target_value.required' => 'Nilai target wajib diisi',
            'target_value.numeric' => 'Nilai target harus berupa angka',
            'target_value.min' => 'Nilai target minimal 0',
            'periode.required' => 'Periode wajib dipilih',
            'periode.in' => 'Periode tidak valid',
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
