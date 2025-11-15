<?php

namespace App\Http\Requests\Audit;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreAuditFindingRequest extends FormRequest
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
            'audit_schedule_id' => ['required', 'exists:audit_schedules,id'],
            'finding_code' => ['nullable', 'string', 'max:50', 'unique:audit_findings,finding_code'],
            'category' => ['required', 'in:major,minor,ofi'],
            'standar_reference' => ['nullable', 'string', 'max:255'],
            'clause' => ['nullable', 'string', 'max:100'],
            'description' => ['required', 'string'],
            'evidence_description' => ['required', 'string'],
            'root_cause' => ['nullable', 'string'],
            'recommendation' => ['required', 'string'],
            'impact' => ['nullable', 'string'],
            'pic_id' => ['nullable', 'exists:users,id'],
            'unit_kerja_id' => ['required', 'exists:unit_kerjas,id'],
            'due_date' => ['nullable', 'date', 'after:today'],
            'priority' => ['nullable', 'in:low,medium,high,critical'],
            'severity' => ['nullable', 'in:low,medium,high'],
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
            'due_date.after' => 'Tanggal jatuh tempo harus setelah hari ini',
            'priority.in' => 'Prioritas tidak valid',
            'severity.in' => 'Tingkat keparahan tidak valid',
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
