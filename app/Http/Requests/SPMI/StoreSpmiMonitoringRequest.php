<?php

namespace App\Http\Requests\SPMI;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreSpmiMonitoringRequest extends FormRequest
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
            'spmi_standard_id' => ['required', 'exists:spmi_standards,id'],
            'tahun_akademik_id' => ['required', 'exists:tahun_akademiks,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'monitoring_date' => ['required', 'date'],
            'monitoring_type' => ['required', 'in:desk_evaluation,field_visit,interview,document_review,mixed'],
            'findings' => ['nullable', 'string'],
            'strengths' => ['nullable', 'string'],
            'weaknesses' => ['nullable', 'string'],
            'opportunities' => ['nullable', 'string'],
            'threats' => ['nullable', 'string'],
            'recommendations' => ['nullable', 'string'],
            'compliance_level' => ['nullable', 'in:belum_terpenuhi,terpenuhi_sebagian,terpenuhi,terpenuhi_sempurna'],
            'compliance_score' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'unit_kerja_id' => ['nullable', 'exists:unit_kerjas,id'],
            'monitored_by' => ['nullable', 'exists:users,id'],
            'report_file' => ['nullable', 'file', 'mimes:pdf,doc,docx'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'spmi_standard_id.required' => 'Standar SPMI harus dipilih',
            'spmi_standard_id.exists' => 'Standar SPMI tidak valid',
            'tahun_akademik_id.required' => 'Tahun akademik harus dipilih',
            'tahun_akademik_id.exists' => 'Tahun akademik tidak valid',
            'title.required' => 'Judul monitoring harus diisi',
            'title.max' => 'Judul monitoring maksimal 255 karakter',
            'monitoring_date.required' => 'Tanggal monitoring harus diisi',
            'monitoring_date.date' => 'Tanggal monitoring harus berupa tanggal yang valid',
            'monitoring_type.required' => 'Jenis monitoring harus dipilih',
            'monitoring_type.in' => 'Jenis monitoring tidak valid',
            'compliance_level.in' => 'Level kepatuhan tidak valid',
            'compliance_score.numeric' => 'Skor kepatuhan harus berupa angka',
            'compliance_score.min' => 'Skor kepatuhan minimal 0',
            'compliance_score.max' => 'Skor kepatuhan maksimal 100',
            'unit_kerja_id.exists' => 'Unit kerja tidak valid',
            'monitored_by.exists' => 'Monitored by tidak valid',
            'report_file.file' => 'Laporan harus berupa file',
            'report_file.mimes' => 'Format laporan harus pdf, doc, atau docx',
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
