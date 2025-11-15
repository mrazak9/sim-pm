<?php

namespace App\Http\Requests\SPMI;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreRTMActionItemRequest extends FormRequest
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
            'rtm_id' => ['required', 'exists:rtms,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'priority' => ['required', 'in:low,medium,high,critical'],
            'pic_id' => ['required', 'exists:users,id'],
            'unit_kerja_id' => ['nullable', 'exists:unit_kerjas,id'],
            'due_date' => ['required', 'date', 'after:today'],
            'progress_notes' => ['nullable', 'string'],
            'completion_remarks' => ['nullable', 'string'],
            'evidence_file' => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'rtm_id.required' => 'RTM harus dipilih',
            'rtm_id.exists' => 'RTM tidak valid',
            'title.required' => 'Judul action item harus diisi',
            'title.max' => 'Judul action item maksimal 255 karakter',
            'description.required' => 'Deskripsi action item harus diisi',
            'priority.required' => 'Prioritas harus dipilih',
            'priority.in' => 'Prioritas tidak valid',
            'pic_id.required' => 'PIC harus dipilih',
            'pic_id.exists' => 'PIC tidak valid',
            'unit_kerja_id.exists' => 'Unit kerja tidak valid',
            'due_date.required' => 'Tanggal deadline harus diisi',
            'due_date.date' => 'Tanggal deadline harus berupa tanggal yang valid',
            'due_date.after' => 'Tanggal deadline harus setelah hari ini',
            'evidence_file.file' => 'Evidence harus berupa file',
            'evidence_file.mimes' => 'Format evidence harus pdf, doc, docx, jpg, jpeg, atau png',
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
