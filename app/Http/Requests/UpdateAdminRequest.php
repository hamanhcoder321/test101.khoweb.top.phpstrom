<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAdminRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Cho phép request này chạy
    }

    public function rules()
    {
        $id = $this->route('id'); // Lấy ID trong URL

        return [
            'name' => 'sometimes|required|string|max:255',
            'short_name' => 'sometimes|nullable|string|max:255',
            'email' => "sometimes|required|email|unique:admin,email,{$id}",
            'tel' => 'sometimes|nullable|string|max:12',
            'password' => 'sometimes|nullable|string|min:6|confirmed',
            'facebook' => 'sometimes|nullable|string|max:255',
            'work_time' => 'sometimes|nullable|integer',
            'invite_by' => 'sometimes|nullable|string|max:255',
            'super_admin' => 'sometimes|nullable|boolean',
            'code' => 'sometimes|nullable|string|max:100',
            'may_cham_cong_id' => 'sometimes|nullable|integer',
            'status' => 'sometimes|nullable|boolean',
            'address' => 'sometimes|nullable|string|max:255',
            'date_start_work' => 'sometimes|nullable|date',
            'birthday' => 'sometimes|nullable|date',
            'intro' => 'sometimes|nullable|string',
            'note' => 'sometimes|nullable|string',
            'cccd' => 'sometimes|nullable|string|max:100',
            'gioitinh' => 'sometimes|nullable|string|max:50',
            'ID_card_photo_on_the_front' => 'nullable|string|max:500',
            'ID_card_photo_on_the_back'  => 'nullable|string|max:500',
        ];
    }

    public function messages()
    {
        return [
            'email.unique' => 'Email này đã tồn tại trong hệ thống.',
            'password.confirmed' => 'Mật khẩu nhập lại không khớp.',
        ];
    }
}
