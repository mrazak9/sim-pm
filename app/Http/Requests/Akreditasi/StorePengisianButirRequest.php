<?php

namespace App\Http\Requests\Akreditasi;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StorePengisianButirRequest extends FormRequest
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
            'periode_akreditasi_id' => [
                'required',
                'exists:periode_akreditasis,id',
                Rule::unique('pengisian_butirs')->where(function ($query) {
                    return $query->where('butir_akreditasi_id', $this->butir_akreditasi_id);
                }),
            ],
            'butir_akreditasi_id' => 'required|exists:butir_akreditasis,id',
            'pic_user_id' => 'nullable|exists:users,id',
            'konten' => 'nullable|string',
            'konten_plain' => 'nullable|string',
            'form_data' => 'nullable|array',
            'files' => 'nullable|array',
            'status' => 'nullable|in:draft,submitted,review,revision,approved',
            'notes' => 'nullable|string',
            'completion_percentage' => 'nullable|numeric|min:0|max:100',
            'is_complete' => 'boolean',
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
            'periode_akreditasi_id.required' => 'Periode akreditasi wajib dipilih',
            'periode_akreditasi_id.exists' => 'Periode akreditasi tidak ditemukan',
            'periode_akreditasi_id.unique' => 'Pengisian butir untuk periode dan butir akreditasi ini sudah ada',
            'butir_akreditasi_id.required' => 'Butir akreditasi wajib dipilih',
            'butir_akreditasi_id.exists' => 'Butir akreditasi tidak ditemukan',
            'pic_user_id.exists' => 'PIC user tidak ditemukan',
            'konten.string' => 'Konten harus berupa teks',
            'konten_plain.string' => 'Konten plain harus berupa teks',
            'files.array' => 'Files harus berupa array',
            'status.in' => 'Status harus salah satu dari: draft, submitted, review, revision, approved',
            'notes.string' => 'Notes harus berupa teks',
            'completion_percentage.numeric' => 'Persentase penyelesaian harus berupa angka',
            'completion_percentage.min' => 'Persentase penyelesaian minimal 0',
            'completion_percentage.max' => 'Persentase penyelesaian maksimal 100',
            'is_complete.boolean' => 'Status kelengkapan harus berupa boolean',
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
