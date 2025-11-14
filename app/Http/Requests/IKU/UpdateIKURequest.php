<?php

namespace App\Http\Requests\IKU;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateIKURequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // TODO: Add authorization logic if needed
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $ikuId = $this->route('id'); // Get ID from route parameter

        return [
            'kode_iku' => "required|string|max:20|unique:ikus,kode_iku,{$ikuId}",
            'nama_iku' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'satuan' => 'required|in:persen,jumlah,skor,nilai',
            'target_type' => 'required|in:increase,decrease',
            'kategori' => 'nullable|string|max:100',
            'bobot' => 'nullable|integer|min:0|max:100',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'kode_iku.required' => 'Kode IKU wajib diisi',
            'kode_iku.unique' => 'Kode IKU sudah digunakan',
            'kode_iku.max' => 'Kode IKU maksimal 20 karakter',
            'nama_iku.required' => 'Nama IKU wajib diisi',
            'nama_iku.max' => 'Nama IKU maksimal 255 karakter',
            'satuan.required' => 'Satuan wajib dipilih',
            'satuan.in' => 'Satuan tidak valid',
            'target_type.required' => 'Tipe target wajib dipilih',
            'target_type.in' => 'Tipe target tidak valid',
            'bobot.integer' => 'Bobot harus berupa angka',
            'bobot.min' => 'Bobot minimal 0',
            'bobot.max' => 'Bobot maksimal 100',
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
