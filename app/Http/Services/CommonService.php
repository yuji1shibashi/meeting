<?php

namespace App\Http\Services;

/**
 * 共通サービス
 */
class CommonService
{
    /**
     * ajaxで渡ってきた真偽値の判定に使用
     *
     * @var array
     */
    const BOOL_VALUE = [
        'TRUE' => 1,
        'FALSE' => 0
    ];

    /**
     * 真偽値を整数に変換する
     * ※ajaxのbooleanはPHPでstringになるため変換する
     *
     * @param string $bool
     * @return int
     */
    public static function convertBooleanValuePhp(string $bool) : int
    {
        return ($bool === 'true') ? self::BOOL_VALUE['TRUE'] : self::BOOL_VALUE['FALSE'];
    }

    /**
     * 会議メンバーを整形
     *
     * @param object $meetingReservationList
     * @param int $reservationId
     * @return array
     */
    public static function formatMeetingMembers($meetingReservationList, int $reservationId) : array
    {
        // 会議予約一覧から同一の会議予約IDを抽出する
        $filterMeetingMembers = array_filter($meetingReservationList->toArray(), function($reservation) use ($reservationId) {
            // 会議予約IDが一致するかどうか判定
            return ($reservation['reservationId'] === $reservationId) ? true : false;
        });
        // 抽出したデータから会議メンバー名を抽出
        return array_column($filterMeetingMembers, 'userName');
    }

    /**
     * メッセージ用にメンバー一覧を整形
     *
     * @param array $members
     * @return string
     */
    public static function formatMemberListForMessage(array $members) : string
    {
        $membersStr = '';

        // メンバーの数だけループ
        foreach ($members as $member) {
            $membersStr .= '・' . $member . "\n";
        }
        return $membersStr;
    }
}