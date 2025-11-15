<?php

namespace App\Http\Requests\Audit;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateAuditFindingRequest extends FormRequest
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
        $findingId = $this->route('id') ?? $this->route('audit_finding');

        return [
            'audit_schedule_id' => ['sometimes', 'required', 'exists:audit_schedules,id'],
            'finding_code' => ['sometimes', 'string', 'max:50', Rule::unique('audit_findings')->ignore($findingId)],
            'category' => ['sometimes', 'required', 'in:major,minor,ofi'],
            'standar_reference' => ['nullable', 'string', 'max:255'],
            'clause' => ['nullable', 'string', 'max:100'],
            'description' => ['sometimes', 'required', 'string'],
            'evidence_description' => ['sometimes', 'required', 'string'],
            'root_cause' => ['nullable', 'string'],
            'recommendation' => ['sometimes', 'required', 'string'],
            'impact' => ['nullable', 'string'],
            'pic_id' => ['nullable', 'exists:users,id'],
            'unit_kerja_id' => ['sometimes', 'required', 'exists:unit_kerjas,id'],
            'due_date' => ['nullable', 'date'],
            'priority' => ['sometimes', 'in:low,medium,high,critical'],
            'severity' => ['sometimes', 'in:low,medium,high'],
            'status' => ['sometimes', 'in:open,in_progress,resolved,verified,closed'],
            'resolution_notes' => ['nullable', 'string'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'audit_schedule_id.required' => 'Jadwal audit harus dipilih',
            'audit_schedule_id.exists' => 'Jadwal audit tidak valid',
            'finding_code.unique' => 'Kode temuan sudah digunakan',
            'finding_code.max' => 'Kode temuan maksimal 50 karakter',
            'category.required' => 'Kategori temuan harus dipilih',
            'category.in' => 'Kategori temuan tidak valid',
            'standar_reference.max' => 'Referensi standar maksimal 255 karakter',
            'clause.max' => 'Klausul maksimal 100 karakter',
            'description.required' => 'Deskripsi temuan harus diisi',
            'evidence_description.required' => 'Deskripsi bukti harus diisi',
            'recommendation.required' => 'Rekomendasi harus diisi',
            'pic_id.exists' => 'PIC tidak valid',
            'unit_kerja_id.required' => 'Unit kerja harus dipilih',
            'unit_kerja_id.exists' => 'Unit kerja tidak valid',
            'due_date.date' => 'Tanggal jatuh tempo harus berupa tanggal yang valid',
            'priority.in' => 'Prioritas tidak valid',
            'severity.in' => 'Tingkat keparahan tidak valid',
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
