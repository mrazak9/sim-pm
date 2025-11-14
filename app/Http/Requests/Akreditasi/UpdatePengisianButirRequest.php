<?php

namespace App\Http\Requests\Akreditasi;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdatePengisianButirRequest extends FormRequest
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
        $pengisianId = $this->route('id') ?? $this->route('pengisian_butir');

        return [
            'periode_akreditasi_id' => [
                'sometimes',
                'exists:periode_akreditasis,id',
                Rule::unique('pengisian_butirs')->where(function ($query) {
                    return $query->where('butir_akreditasi_id', $this->butir_akreditasi_id);
                })->ignore($pengisianId),
            ],
            'butir_akreditasi_id' => 'sometimes|exists:butir_akreditasis,id',
            'pic_user_id' => 'nullable|exists:users,id',
            'konten' => 'nullable|string',
            'konten_plain' => 'nullable|string',
            'files' => 'nullable|array',
            'status' => 'sometimes|in:draft,submitted,review,revision,approved',
            'version' => 'sometimes|integer|min:1',
            'notes' => 'nullable|string',
            'reviewed_by' => 'nullable|exists:users,id',
            'review_notes' => 'nullable|string',
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
            'periode_akreditasi_id.exists' => 'Periode akreditasi tidak ditemukan',
            'periode_akreditasi_id.unique' => 'Pengisian butir untuk periode dan butir akreditasi ini sudah ada',
            'butir_akreditasi_id.exists' => 'Butir akreditasi tidak ditemukan',
            'pic_user_id.exists' => 'PIC user tidak ditemukan',
            'konten.string' => 'Konten harus berupa teks',
            'konten_plain.string' => 'Konten plain harus berupa teks',
            'files.array' => 'Files harus berupa array',
            'status.in' => 'Status harus salah satu dari: draft, submitted, review, revision, approved',
            'version.integer' => 'Versi harus berupa angka',
            'version.min' => 'Versi minimal 1',
            'notes.string' => 'Notes harus berupa teks',
            'reviewed_by.exists' => 'Reviewer tidak ditemukan',
            'review_notes.string' => 'Review notes harus berupa teks',
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
