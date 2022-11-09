<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ReservationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'organizerId' => 'required',
            'meetingRoomId' => 'required',
            'title' => 'required',
            'startDataTime' => 'required|date|after:now',
            'endDateTime' => 'required|date|after:startTime',
            'isRemind' => 'required',
            'isRepeat' => 'required',
            'color' => 'required',
            'meetingMembers' => 'required|array',
            'meetingMembers.*' => 'required|array',
            'meetingMembers.*.memberId' => 'required',
        ];
    }

    /**
     * 定義済みバリデーションルールのエラーメッセージ取得
     *
     * @return array
     */
    public function messages()
    {
        return [
            'organizerId.required' => '会議担当者が未設定です。',
            'meetingRoomId.required' => '会議室が未設定です。',
            'title.required' => '会議タイトルが未設定です。',
            'startTime.required' => '会議開始日時が未設定です。',
            'startTime.date' => '会議開始日時が「YYYY/MM/DD HH:mm:ss」の形式ではありません。',
            'startTime.after' => '会議開始日時は現日時以降で設定してください。',
            'endTime.required' => '会議終了日時が未設定です。',
            'endTime.date' => '会議終了日時が「YYYY/MM/DD HH:mm:ss」の形式ではありません。',
            'endTime.after' => '会議終了日時は会議開始日時以降の日時で設定してください。',
            'isRemind.required' => 'リマインド設定有無は必須です。',
            'isRepeat.required' => 'リピート設定有無は必須です。',
            'color.required' => '会議カラーが未設定です。',
            'meetingMembers.required' => '会議対象者が未設定です。',
            'meetingMembers.array' => '会議対象者が未設定です。',
            'meetingMembers.*.required' => '会議対象者が未設定です。',
            'meetingMembers.*.array' => '会議対象者が未設定です。',
            'meetingMembers.*.memberId.required' => '会議対象者が未設定です。',
        ];
    }

    /**
     * エラー発生時にJSONに変換する
     * ※文字化け対策
     *
     * @param Validator $validator
     * @return HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        $res = response()->json(
            [
                'status' => 400,
                'errors' => $validator->errors(),
            ],
            400,
            ['Content-Type' => 'application/json;charset=UTF-8', 'Charset' => 'utf-8'],
            JSON_UNESCAPED_UNICODE
        );
        throw new HttpResponseException($res);
    }
}
