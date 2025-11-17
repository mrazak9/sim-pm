<?php

namespace App\Http\Requests\SPMI;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreSpmiStandardRequest extends FormRequest
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
            'code' => ['required', 'string', 'unique:spmi_standards,code'],
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'in:pendidikan,penelitian,pengabdian,pengelolaan'],
            'description' => ['nullable', 'string'],
            'objective' => ['nullable', 'string'],
            'scope' => ['nullable', 'string'],
            'reference' => ['nullable', 'string'],
            'effective_date' => ['required', 'date', 'after_or_equal:today'],
            'review_date' => ['required', 'date', 'after:effective_date'],
            'unit_kerja_id' => ['nullable', 'exists:unit_kerjas,id'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'code.required' => 'Kode standar SPMI harus diisi',
            'code.unique' => 'Kode standar SPMI sudah terdaftar',
            'name.required' => 'Nama standar SPMI harus diisi',
            'name.max' => 'Nama standar SPMI maksimal 255 karakter',
            'category.required' => 'Kategori standar harus dipilih',
            'category.in' => 'Kategori standar tidak valid',
            'effective_date.required' => 'Tanggal efektif harus diisi',
            'effective_date.date' => 'Tanggal efektif harus berupa tanggal yang valid',
            'effective_date.after_or_equal' => 'Tanggal efektif tidak boleh sebelum hari ini',
            'review_date.required' => 'Tanggal review harus diisi',
            'review_date.date' => 'Tanggal review harus berupa tanggal yang valid',
            'review_date.after' => 'Tanggal review harus setelah tanggal efektif',
            'unit_kerja_id.exists' => 'Unit kerja tidak valid',
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
