<?php

/**
 * @file: VehicleRequest.php
 * @description: التحقق من بيانات المركبات - Route & Dispatch Service
 * @module: RouteDispatch
 * @author: Team Leader (Khalid)
 */

namespace App\Modules\RouteDispatch\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VehicleRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $vehicleId = $this->route('id') ?? null;

        return [
            'plate_number'         => 'required|string|max:20|unique:vehicles,plate_number,' . $vehicleId . ',vehicle_id',
            'type'                 => 'required|in:light,heavy,refrigerated',
            'max_weight_kg'        => 'required|numeric|min:0',
            'max_volume_m3'        => 'required|numeric|min:0',
            'odometer_km'          => 'nullable|numeric|min:0',
            'status'               => 'nullable|in:available,in_service,out_of_service,in_repair',
            'market_value'         => 'nullable|numeric|min:0',
            'fuel_type'            => 'nullable|in:petrol,diesel,electric',
            'year'                 => 'nullable|integer|min:1990|max:' . (date('Y') + 1),
            'required_license_type' => 'nullable|in:light,heavy',
        ];
    }
}
