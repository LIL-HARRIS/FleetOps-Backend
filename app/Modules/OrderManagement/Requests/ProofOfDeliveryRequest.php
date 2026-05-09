<?php

namespace App\Modules\OrderManagement\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProofOfDeliveryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'driver_id'       => 'required|integer',
            'lat'             => 'required|numeric',
            'lng'             => 'required|numeric',
            'signature'       => 'nullable|string',
            'photo'           => 'nullable|string',
            'customer_name'   => 'nullable|string',
            'customer_signed' => 'nullable|boolean',
            'is_safe_drop'    => 'nullable|boolean',
        ];
    }
}
