<?php

namespace App\Http\Requests\Survey;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreSurveyRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'in:internal,external,public'],
            'start_date' => ['nullable', 'date', 'after_or_equal:today'],
            'end_date' => ['nullable', 'date', 'after:start_date'],
            'max_responses' => ['nullable', 'integer', 'min:1'],
            'is_anonymous' => ['nullable', 'boolean'],
            'allow_multiple_responses' => ['nullable', 'boolean'],
            'require_login' => ['nullable', 'boolean'],
            'show_results' => ['nullable', 'boolean'],
            'welcome_message' => ['nullable', 'string'],
            'thank_you_message' => ['nullable', 'string'],
            'unit_kerja_id' => ['nullable', 'exists:unit_kerjas,id'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Judul kuesioner harus diisi',
            'title.max' => 'Judul kuesioner maksimal 255 karakter',
            'type.required' => 'Tipe kuesioner harus dipilih',
            'type.in' => 'Tipe kuesioner tidak valid. Pilih: internal, external, atau public',
            'start_date.date' => 'Tanggal mulai harus berupa tanggal yang valid',
            'start_date.after_or_equal' => 'Tanggal mulai tidak boleh sebelum hari ini',
            'end_date.date' => 'Tanggal selesai harus berupa tanggal yang valid',
            'end_date.after' => 'Tanggal selesai harus setelah tanggal mulai',
            'max_responses.integer' => 'Maksimal respons harus berupa angka',
            'max_responses.min' => 'Maksimal respons minimal 1',
            'is_anonymous.boolean' => 'Anonim harus berupa true atau false',
            'allow_multiple_responses.boolean' => 'Izinkan respons ganda harus berupa true atau false',
            'require_login.boolean' => 'Wajib login harus berupa true atau false',
            'show_results.boolean' => 'Tampilkan hasil harus berupa true atau false',
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
            'message' => 'Validasi gagal',
            'errors' => $validator->errors()
        ], 422));
    }
}
