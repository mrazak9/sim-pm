<?php

namespace App\Http\Requests\SPMI;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateSpmiIndicatorRequest extends FormRequest
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
            'spmi_standard_id' => ['sometimes', 'required', 'exists:spmi_standards,id'],
            'code' => ['sometimes', 'required', 'string', Rule::unique('spmi_indicators', 'code')->ignore($this->route('spmi_indicator'))],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'measurement_unit' => ['nullable', 'string'],
            'measurement_type' => ['sometimes', 'required', 'in:kuantitatif,kualitatif'],
            'formula' => ['nullable', 'string'],
            'data_source' => ['nullable', 'string'],
            'frequency' => ['sometimes', 'required', 'in:harian,mingguan,bulanan,semesteran,tahunan'],
            'pic_id' => ['nullable', 'exists:users,id'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'spmi_standard_id.required' => 'Standar SPMI harus dipilih',
            'spmi_standard_id.exists' => 'Standar SPMI tidak valid',
            'code.required' => 'Kode indikator harus diisi',
            'code.unique' => 'Kode indikator sudah terdaftar',
            'name.required' => 'Nama indikator harus diisi',
            'name.max' => 'Nama indikator maksimal 255 karakter',
            'measurement_type.required' => 'Jenis pengukuran harus dipilih',
            'measurement_type.in' => 'Jenis pengukuran tidak valid',
            'frequency.required' => 'Frekuensi pengukuran harus dipilih',
            'frequency.in' => 'Frekuensi pengukuran tidak valid',
            'pic_id.exists' => 'PIC tidak valid',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation error',
            'errors' => $validator->errors()
        ], 422));
    }
}
