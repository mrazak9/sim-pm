<?php

namespace App\Http\Requests\Audit;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreRTLRequest extends FormRequest
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
            'audit_finding_id' => ['required', 'exists:audit_findings,id'],
            'rtl_code' => ['nullable', 'string', 'max:50', 'unique:rtls,rtl_code'],
            'action_plan' => ['required', 'string'],
            'success_indicator' => ['nullable', 'string'],
            'implementation_steps' => ['nullable', 'string'],
            'pic_id' => ['required', 'exists:users,id'],
            'unit_kerja_id' => ['required', 'exists:unit_kerjas,id'],
            'target_date' => ['required', 'date', 'after:today'],
            'budget' => ['nullable', 'numeric', 'min:0'],
            'resources_needed' => ['nullable', 'string'],
            'risk_level' => ['nullable', 'in:low,medium,high'],
            'risk_description' => ['nullable', 'string'],
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
            'target_date.after' => 'Tanggal target harus setelah hari ini',
            'budget.numeric' => 'Budget harus berupa angka',
            'budget.min' => 'Budget tidak boleh negatif',
            'risk_level.in' => 'Tingkat risiko tidak valid',
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
