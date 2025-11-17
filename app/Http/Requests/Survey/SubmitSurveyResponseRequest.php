<?php

namespace App\Http\Requests\Survey;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class SubmitSurveyResponseRequest extends FormRequest
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
            'answers' => ['required', 'array', 'min:1'],
            'answers.*.question_id' => ['required', 'exists:survey_questions,id'],
            'answers.*.answer' => ['nullable', 'string'],
            'answers.*.option' => ['nullable', 'string'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'answers.required' => 'Jawaban harus diisi',
            'answers.array' => 'Format jawaban tidak valid',
            'answers.min' => 'Minimal harus ada 1 jawaban',
            'answers.*.question_id.required' => 'ID pertanyaan harus diisi',
            'answers.*.question_id.exists' => 'Pertanyaan tidak ditemukan',
            'answers.*.answer.string' => 'Jawaban harus berupa teks',
            'answers.*.option.string' => 'Opsi harus berupa teks',
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
