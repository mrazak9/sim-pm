<?php

namespace App\Http\Requests\Survey;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreSurveyQuestionRequest extends FormRequest
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
            'survey_id' => ['required', 'exists:surveys,id'],
            'question_text' => ['required', 'string'],
            'question_type' => ['required', 'in:text,textarea,radio,checkbox,dropdown,rating,matrix'],
            'options' => ['nullable', 'array'],
            'options.*' => ['string'],
            'is_required' => ['nullable', 'boolean'],
            'order' => ['nullable', 'integer', 'min:1'],
            'validation_rules' => ['nullable', 'array'],
            'conditional_logic' => ['nullable', 'array'],
            'help_text' => ['nullable', 'string'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'survey_id.required' => 'ID kuesioner harus diisi',
            'survey_id.exists' => 'Kuesioner tidak ditemukan',
            'question_text.required' => 'Teks pertanyaan harus diisi',
            'question_type.required' => 'Tipe pertanyaan harus dipilih',
            'question_type.in' => 'Tipe pertanyaan tidak valid. Pilih: text, textarea, radio, checkbox, dropdown, rating, atau matrix',
            'options.array' => 'Opsi harus berupa array',
            'options.*.string' => 'Setiap opsi harus berupa teks',
            'is_required.boolean' => 'Wajib diisi harus berupa true atau false',
            'order.integer' => 'Urutan harus berupa angka',
            'order.min' => 'Urutan minimal 1',
            'validation_rules.array' => 'Aturan validasi harus berupa array',
            'conditional_logic.array' => 'Logika kondisional harus berupa array',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validasi gagal',
            'errors' => $validator->errors()
        ], 422));
    }
}
