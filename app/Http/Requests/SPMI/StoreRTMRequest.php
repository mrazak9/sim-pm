<?php

namespace App\Http\Requests\SPMI;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreRTMRequest extends FormRequest
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
            'meeting_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'location' => ['nullable', 'string'],
            'agenda' => ['nullable', 'string'],
            'discussion_points' => ['nullable', 'string'],
            'decisions' => ['nullable', 'string'],
            'minutes' => ['nullable', 'string'],
            'follow_up_plan' => ['nullable', 'string'],
            'chairman_id' => ['required', 'exists:users,id', 'different:secretary_id'],
            'secretary_id' => ['required', 'exists:users,id'],
            'minutes_file' => ['nullable', 'file', 'mimes:pdf,doc,docx'],
            'attendance_file' => ['nullable', 'file', 'mimes:pdf,doc,docx,xlsx,xls'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Judul RTM harus diisi',
            'title.max' => 'Judul RTM maksimal 255 karakter',
            'tahun_akademik_id.required' => 'Tahun akademik harus dipilih',
            'tahun_akademik_id.exists' => 'Tahun akademik tidak valid',
            'meeting_date.required' => 'Tanggal pertemuan harus diisi',
            'meeting_date.date' => 'Tanggal pertemuan harus berupa tanggal yang valid',
            'start_time.required' => 'Jam mulai harus diisi',
            'start_time.date_format' => 'Jam mulai harus format HH:mm',
            'end_time.required' => 'Jam berakhir harus diisi',
            'end_time.date_format' => 'Jam berakhir harus format HH:mm',
            'end_time.after' => 'Jam berakhir harus setelah jam mulai',
            'chairman_id.required' => 'Ketua rapat harus dipilih',
            'chairman_id.exists' => 'Ketua rapat tidak valid',
            'chairman_id.different' => 'Ketua rapat tidak boleh sama dengan sekretaris',
            'secretary_id.required' => 'Sekretaris harus dipilih',
            'secretary_id.exists' => 'Sekretaris tidak valid',
            'minutes_file.file' => 'File minutes harus berupa file',
            'minutes_file.mimes' => 'Format minutes harus pdf, doc, atau docx',
            'attendance_file.file' => 'File kehadiran harus berupa file',
            'attendance_file.mimes' => 'Format kehadiran harus pdf, doc, docx, xlsx, atau xls',
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
