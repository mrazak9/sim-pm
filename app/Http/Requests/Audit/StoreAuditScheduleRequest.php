<?php

namespace App\Http\Requests\Audit;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreAuditScheduleRequest extends FormRequest
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
            'audit_plan_id' => ['required', 'exists:audit_plans,id'],
            'unit_kerja_id' => ['required', 'exists:unit_kerjas,id'],
            'auditor_lead_id' => ['required', 'exists:users,id'],
            'auditor_ids' => ['nullable', 'array'],
            'auditor_ids.*' => ['exists:users,id'],
            'scheduled_date' => ['required', 'date'],
            'estimated_duration' => ['required', 'integer', 'min:30', 'max:480'],
            'location' => ['nullable', 'string', 'max:255'],
            'agenda' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'preparation_notes' => ['nullable', 'string'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'audit_plan_id.required' => 'Rencana audit harus dipilih',
            'audit_plan_id.exists' => 'Rencana audit tidak valid',
            'unit_kerja_id.required' => 'Unit kerja harus dipilih',
            'unit_kerja_id.exists' => 'Unit kerja tidak valid',
            'auditor_lead_id.required' => 'Lead auditor harus dipilih',
            'auditor_lead_id.exists' => 'Lead auditor tidak valid',
            'auditor_ids.array' => 'Data auditor harus berupa array',
            'auditor_ids.*.exists' => 'Salah satu auditor tidak valid',
            'scheduled_date.required' => 'Tanggal audit harus diisi',
            'scheduled_date.date' => 'Tanggal audit harus berupa tanggal yang valid',
            'estimated_duration.required' => 'Estimasi durasi harus diisi',
            'estimated_duration.integer' => 'Estimasi durasi harus berupa angka',
            'estimated_duration.min' => 'Estimasi durasi minimal 30 menit',
            'estimated_duration.max' => 'Estimasi durasi maksimal 480 menit (8 jam)',
            'location.max' => 'Lokasi maksimal 255 karakter',
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
