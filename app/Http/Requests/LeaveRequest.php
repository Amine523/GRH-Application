<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LeaveRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // You can modify this to check for user permissions or roles if necessary
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'start_day' => ['required', 'date', 'after_or_equal:today'],
            'end_day' => ['required_if:type_of_leave,vacation,sick', 'nullable', 'date', 'after_or_equal:start_day'],
            'type_of_leave' => ['required', 'in:vacation,sick,authorisation,halfday'],
            'authorisationHours' => ['nullable', 'numeric', 'min:0', 'max:120'],
            'user_id' => ['nullable', 'numeric', 'min:0', 'max:120'],
        ];
    }

    /**
     * Custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'user_id.required' => 'The user ID is required.',
            'user_id.exists' => 'The selected user does not exist.',
            'start_day.required' => 'The start date is required.',
            'start_day.date' => 'The start date must be a valid date.',
            'start_day.after_or_equal' => 'The start date must be today or later.',
            'end_day.required' => 'The end date is required.',
            'end_day.date' => 'The end date must be a valid date.',
            'end_day.after_or_equal' => 'The end date must be after or equal to the start date.',
            'type_of_leave.required' => 'Please select a type of leave.',
            'type_of_leave.in' => 'The selected type of leave is invalid.',
            'status_of_leave.required' => 'The status of leave is required.',
            'status_of_leave.in' => 'The status of leave must be pending, approved, or rejected.',
        ];
    }
}
