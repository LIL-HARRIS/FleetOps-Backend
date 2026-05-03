<?php

/**
 * @file: CapacityCheckRequest.php
 * @description: التحقق من بيانات فحص السعة - Route & Dispatch Service
 * @module: RouteDispatch
 */

namespace App\Modules\RouteDispatch\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CapacityCheckRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'data' => ['required', 'array', 'min:1'],
            'data.*.color' => ['required', 'string'],
            'data.*.zone' => ['required', 'string'],
            'data.*.vehicle_id' => ['required', 'integer', 'exists:vehicles,vehicle_id'],
            'data.*.orders' => ['required', 'array', 'min:1'],
            'data.*.orders.*.OrderID' => ['required', 'integer'],
            'data.*.orders.*.Weight' => ['nullable', 'numeric', 'min:0'],
            'data.*.orders.*.Volume' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'data.required' => 'The data field is required and must contain at least one cluster.',
            'data.array' => 'The data field must be an array.',
            'data.min' => 'The data field must contain at least one cluster.',
            'data.*.color.required' => 'Each cluster must include a color value.',
            'data.*.zone.required' => 'Each cluster must include a zone value.',
            'data.*.vehicle_id.required' => 'Each cluster must include a vehicle_id value.',
            'data.*.vehicle_id.exists' => 'One or more vehicle ids do not exist.',
            'data.*.orders.required' => 'Each cluster must include an orders array.',
            'data.*.orders.array' => 'Orders must be provided as an array.',
            'data.*.orders.min' => 'Each cluster must contain at least one order.',
            'data.*.orders.*.OrderID.required' => 'Each order must include OrderID.',
            'data.*.orders.*.OrderID.integer' => 'Each OrderID must be an integer.',
            'data.*.orders.*.Weight.numeric' => 'Order weight must be numeric.',
            'data.*.orders.*.Weight.min' => 'Order weight cannot be negative.',
            'data.*.orders.*.Volume.numeric' => 'Order volume must be numeric.',
            'data.*.orders.*.Volume.min' => 'Order volume cannot be negative.',
        ];
    }
}