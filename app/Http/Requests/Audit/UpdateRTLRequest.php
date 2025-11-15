<?php

namespace App\Http\Requests\Audit;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateRTLRequest extends FormRequest
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
        $rtlId = $this->route('id') ?? $this->route('rtl');

        return [
            'audit_finding_id' => ['sometimes', 'required', 'exists:audit_findings,id'],
            'rtl_code' => ['sometimes', 'string', 'max:50', Rule::unique('rtls')->ignore($rtlId)],
            'action_plan' => ['sometimes', 'required', 'string'],
            'success_indicator' => ['nullable', 'string'],
            'implementation_steps' => ['nullable', 'string'],
            'pic_id' => ['sometimes', 'required', 'exists:users,id'],
            'unit_kerja_id' => ['sometimes', 'required', 'exists:unit_kerjas,id'],
            'target_date' => ['sometimes', 'required', 'date'],
            'budget' => ['nullable', 'numeric', 'min:0'],
            'resources_needed' => ['nullable', 'string'],
            'status' => ['sometimes', 'in:not_started,in_progress,completed,overdue,cancelled'],
            'completion_percentage' => ['sometimes', 'integer', 'min:0', 'max:100'],
            'current_status_notes' => ['nullable', 'string'],
            'completion_notes' => ['nullable', 'string'],
            'risk_level' => ['sometimes', 'in:low,medium,high'],
            'risk_description' => ['nullable', 'string'],
            'verification_status' => ['sometimes', 'in:pending,approved,rejected,revision'],
            'verification_notes' => ['nullable', 'string'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'audit_finding_id.required' => 'Temuan audit harus dipilih',
            'audit_finding_id.exists' => 'Temuan audit tidak valid',
            'rtl_code.unique' => 'Kode RTL sudah digunakan',
            'rtl_code.max' => 'Kode RTL maksimal 50 karakter',
            'action_plan.required' => 'Rencana tindakan harus diisi',
            'pic_id.required' => 'PIC harus dipilih',
            'pic_id.exists' => 'PIC tidak valid',
            'unit_kerja_id.required' => 'Unit kerja harus dipilih',
            'unit_kerja_id.exists' => 'Unit kerja tidak valid',
            'target_date.required' => 'Tanggal target harus diisi',
            'target_date.date' => 'Tanggal target harus berupa tanggal yang valid',
            'budget.numeric' => 'Budget harus berupa angka',
            'budget.min' => 'Budget tidak boleh negatif',
            'status.in' => 'Status tidak valid',
            'completion_percentage.integer' => 'Persentase harus berupa angka',
            'completion_percentage.min' => 'Persentase minimal 0',
            'completion_percentage.max' => 'Persentase maksimal 100',
            'risk_level.in' => 'Tingkat risiko tidak valid',
            'verification_status.in' => 'Status verifikasi tidak valid',
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
