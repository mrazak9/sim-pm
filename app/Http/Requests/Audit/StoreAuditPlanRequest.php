<?php

namespace App\Http\Requests\Audit;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreAuditPlanRequest extends FormRequest
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
            'tahun_akademik_id' => ['required', 'exists:tahun_akademiks,id'],
            'periode' => ['required', 'in:semester_1,semester_2,tahunan'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'objectives' => ['nullable', 'string'],
            'scope' => ['nullable', 'string'],
            'status' => ['nullable', 'in:draft,approved,ongoing,completed,cancelled'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Judul rencana audit harus diisi',
            'title.max' => 'Judul rencana audit maksimal 255 karakter',
            'tahun_akademik_id.required' => 'Tahun akademik harus dipilih',
            'tahun_akademik_id.exists' => 'Tahun akademik tidak valid',
            'periode.required' => 'Periode audit harus dipilih',
            'periode.in' => 'Periode audit tidak valid',
            'start_date.required' => 'Tanggal mulai harus diisi',
            'start_date.date' => 'Tanggal mulai harus berupa tanggal yang valid',
            'start_date.after_or_equal' => 'Tanggal mulai tidak boleh sebelum hari ini',
            'end_date.required' => 'Tanggal selesai harus diisi',
            'end_date.date' => 'Tanggal selesai harus berupa tanggal yang valid',
            'end_date.after' => 'Tanggal selesai harus setelah tanggal mulai',
            'status.in' => 'Status tidak valid',
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
