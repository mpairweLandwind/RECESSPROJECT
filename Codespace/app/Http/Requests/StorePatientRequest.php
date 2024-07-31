<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePatientRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            
            'name' => 'required|string|max:255',
            'email' => 'email|max:255',
            'contact' => 'required|string|max:20', // Update max length as per your requirements
            'sex' => 'required|in:Male,Female,Other',
            'age' => 'integer|min:1|max:120', // Adjust min and max age limits as needed
            'testRequired' => 'required|array', // Assuming testRequired is an array
            'testRequired.*' => 'string', // Assuming each element in testRequired is a string
            'test_date' => 'required|date', 
        ];
    }
}
