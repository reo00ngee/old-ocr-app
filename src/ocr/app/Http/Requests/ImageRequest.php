<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ImageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules()
    {
        return [
            'image' => 'required|image|mimes:jpg,jpeg,png'
        ];
    }

    public function messages()
    {
        return [
            'image.mimes' => '画像はJPG、JPEG、PNG形式でアップロードしてください。',
            
        ];
    }
}