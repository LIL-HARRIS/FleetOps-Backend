<?php

namespace App\Modules\ReportingAnalytics\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitReconciliationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'route_id' => 'required|integer|exists:routes,route_id',
            'collected_amounts' => 'required|array',
            'collected_amounts.*' => 'numeric|min:0',
            'notes' => 'nullable|string',
            'proof_file' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048',
        ];
    }
}
