<?php

/**
 * @file: RouteRequest.php
 * @description: التحقق من بيانات المسارات - Route & Dispatch Service
 * @module: RouteDispatch
 * @author: Team Leader (Khalid)
 */

namespace App\Modules\RouteDispatch\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RouteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isCreate = $this->isMethod('post');

        return [
            'route_name' => ($isCreate ? 'required' : 'sometimes') . '|string|max:200',
            'driver_id' => ($isCreate ? 'required' : 'sometimes') . '|integer|exists:drivers,driver_id',
            'vehicle_id' => ($isCreate ? 'required' : 'sometimes') . '|integer|exists:vehicles,vehicle_id',
            'scheduled_start_time' => ($isCreate ? 'required' : 'sometimes') . '|date',
            'scheduled_end_time' => 'sometimes|nullable|date|after:scheduled_start_time',
            'status' => 'sometimes|in:Planned,Active,Completed,Cancelled',
            'total_distance' => 'sometimes|nullable|numeric|min:0',
            'stops' => 'sometimes|array|min:1',
            'stops.*.order_id' => 'required_with:stops|integer|exists:order,OrderID|distinct',
            'stops.*.stop_no' => 'required|integer|min:1',
            'stops.*.eta' => 'required|date',
            'stops.*.latitude' => 'sometimes|nullable|numeric',
            'stops.*.longitude' => 'sometimes|nullable|numeric',
        ];
    }

    public function messages(): array
    {
        return [
            'route_name.required' => 'route_name is required.',
            'route_name.max' => 'route_name must not exceed 200 characters.',
            'driver_id.required' => 'driver_id is required.',
            'driver_id.exists' => 'driver_id does not exist in drivers.',
            'vehicle_id.required' => 'vehicle_id is required.',
            'vehicle_id.exists' => 'vehicle_id does not exist in vehicles.',
            'scheduled_start_time.required' => 'scheduled_start_time is required.',
            'scheduled_start_time.date' => 'scheduled_start_time must be a valid date.',
            'scheduled_end_time.after' => 'scheduled_end_time must be after scheduled_start_time.',
            'status.in' => 'status must be one of Planned, Active, Completed, or Cancelled.',
            'stops.array' => 'stops must be an array.',
            'stops.*.order_id.required_with' => 'order_id is required for each stop.',
            'stops.*.order_id.exists' => 'One or more order_id values do not exist.',
            'stops.*.order_id.distinct' => 'Duplicate order_id values are not allowed in stops.',
            'stops.*.latitude.between' => 'latitude must be between -90 and 90.',
            'stops.*.longitude.between' => 'longitude must be between -180 and 180.',
        ];
    }
}
