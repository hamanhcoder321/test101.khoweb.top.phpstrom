<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class APIStoreAdminRequest extends FormRequest
{
    public function authorize()
    {
        // Cho phép mọi người dùng có thể gửi request này (hoặc tùy quyền)
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'short_name' => 'nullable|string|max:255',
            'email' => 'required|email|unique:admin,email',
            'tel' => 'nullable|string|max:12',
            'password' => 'required|string|min:6|confirmed',
            'facebook' => 'nullable|string|max:255',
            'work_time' => 'nullable|integer',
            'invite_by' => 'nullable|string|max:255',
            'super_admin' => 'nullable|boolean',
            'code' => 'nullable|string|max:100',
            'may_cham_cong_id' => 'nullable|integer',
            'status' => 'nullable|boolean',
            'address' => 'nullable|string|max:255',
            'date_start_work' => 'nullable|date',
            'birthday' => 'nullable|date',
            'intro' => 'nullable|string',
            'note' => 'nullable|string',
            'cccd' => 'nullable|string|max:100',
            'gioitinh' => 'nullable|string|max:50',
            'ID_card_photo_on_the_front' => 'nullable|string|max:500',
            'ID_card_photo_on_the_back'  => 'nullable|string|max:500',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Họ tên không được để trống',
            'email.required' => 'Email không được để trống',
            'email.unique' => 'Email đã tồn tại trong hệ thống',
            'password.required' => 'Vui lòng nhập mật khẩu',
            'password.confirmed' => 'Mật khẩu nhập lại không khớp',
        ];
    }
}
