<?php

namespace App\Http\Requests\Akreditasi;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreButirAkreditasiRequest extends FormRequest
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
            'kode' => 'required|string|max:50|unique:butir_akreditasis,kode',
            'nama' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'instrumen' => 'required|string|max:50',
            'kategori' => 'required|string|max:100',
            'bobot' => 'nullable|numeric|min:0|max:100',
            'parent_id' => 'nullable|exists:butir_akreditasis,id',
            'urutan' => 'nullable|integer|min:0',
            'is_mandatory' => 'boolean',
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
            'kode.required' => 'Kode butir akreditasi wajib diisi',
            'kode.unique' => 'Kode butir akreditasi sudah digunakan',
            'kode.max' => 'Kode butir akreditasi maksimal 50 karakter',
            'nama.required' => 'Nama butir akreditasi wajib diisi',
            'nama.max' => 'Nama butir akreditasi maksimal 255 karakter',
            'instrumen.required' => 'Instrumen wajib diisi',
            'instrumen.max' => 'Instrumen maksimal 50 karakter',
            'kategori.required' => 'Kategori wajib diisi',
            'kategori.max' => 'Kategori maksimal 100 karakter',
            'bobot.numeric' => 'Bobot harus berupa angka',
            'bobot.min' => 'Bobot minimal 0',
            'bobot.max' => 'Bobot maksimal 100',
            'parent_id.exists' => 'Parent butir akreditasi tidak ditemukan',
            'urutan.integer' => 'Urutan harus berupa angka',
            'urutan.min' => 'Urutan minimal 0',
            'is_mandatory.boolean' => 'Status wajib harus berupa boolean',
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
