<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * 会議室予約モデル
 */
class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'organizer_id',
        'meeting_room_id',
        'title',
        'start_time',
        'end_time',
        'comment',
        'is_remind',
        'is_repeat',
        'color',
        'is_three_days_ago',
        'is_two_days_ago',
        'is_prev_days_ago',
        'is_current_day',
        'is_one_hour_ago',
        'is_half_an_hour_ago',
        'is_ten_minute_ago',
        'is_optional',
        'optional_remind_time'
    ];
    protected $guarded = [];

    /**
     * 対象の会議予約情報をDBから取得
     *
     * @param string $targetDate
     * @param string $targetRepresentative
     * @param string $targetMember
     * @return object
     */
    public function getTargetDateMeetingReservationList(string $targetDate, string $targetRepresentative, string $targetMember)
    {
        $query = Reservation::from('reservations')
            ->select(DB::raw("
                `reservations`.`id` AS 'reservationId',
                `reservations`.`title` AS 'title',
                `meeting_rooms`.`id` AS 'roomId',
                `meeting_rooms`.`name` AS 'roomName',
                SUBSTRING(`reservations`.`start_time`, 1, 10) AS 'date',
                `reservations`.`start_time` AS 'start',
                `reservations`.`end_time` AS 'end',
                `reservations`.`comment` AS 'comment',
                `reservations`.`color` AS 'color',
                `organizer`.`name` AS 'organizerName',
                `accounts`.`name` AS 'userName'
            "))
            ->join('meeting_accounts', 'reservations.id', '=', 'meeting_accounts.reservation_id')
            ->join('accounts', 'meeting_accounts.account_id', '=', 'accounts.id')
            ->join('accounts as organizer', 'reservations.organizer_id', '=', 'organizer.id')
            ->join('meeting_rooms', 'reservations.meeting_room_id', '=', 'meeting_rooms.id');

        // 主催者を絞り込む場合
        if ($targetRepresentative !== '') {
            $query->where('reservations.organizer_id', '=', $targetRepresentative);
        }

        // 対象会議メンバーを絞り込む場合
        if ($targetMember !== '') {
            $query->whereIn('reservations.id', function ($query) use ($targetDate, $targetMember) {
                $query->select('reservations.id')->from('reservations')
                    ->join('meeting_accounts', 'reservations.id', '=', 'meeting_accounts.reservation_id')
                    ->whereRaw('SUBSTRING(`reservations`.`start_time`, 1, 10) = ?', [$targetDate])
                    ->where('meeting_accounts.account_id', '=', $targetMember);
            });
        }

        return $query->whereRaw('SUBSTRING(`reservations`.`start_time`, 1, 10) = ?', [$targetDate])
            ->orderBy('meeting_rooms.id', 'asc')
            ->orderBy('accounts.id', 'asc')
            ->orderBy('reservations.start_time', 'asc')
            ->orderBy('reservations.end_time', 'asc')
            ->get();
    }

    /**
     * 会議予約詳細を取得
     *
     * @param int $reservationId
     * @return object
     */
    public function getMeetingReservationDetailByReservationId($reservationId)
    {
        return Reservation::from('reservations')
            ->select(DB::raw("
                `reservations`.`title` AS 'title',
                `reservations`.`meeting_room_id` AS 'meetingRoomId',
                SUBSTRING(`reservations`.`start_time`, 1, 10) AS 'date',
                SUBSTRING(`reservations`.`start_time`, 12, 2) AS 'startHour',
                SUBSTRING(`reservations`.`start_time`, 15, 2) AS 'startMinute',
                SUBSTRING(`reservations`.`end_time`, 12, 2) AS 'endHour',
                SUBSTRING(`reservations`.`end_time`, 15, 2) AS 'endMinute',
                `reservations`.`comment` AS 'comment',
                `reservations`.`color` AS 'color',
                `reservations`.`organizer_id` AS 'organizerId',
                `accounts`.`name` AS 'userName',
                `accounts`.`id` AS 'userId',
                `is_remind` AS 'isRemind',
                `is_three_days_ago` AS 'isThreeDaysAgo',
                `is_two_days_ago` AS 'isTwoDaysAgo',
                `is_prev_days_ago` AS 'isPrevDaysAgo',
                `is_current_day` AS 'isCurrentDay',
                `is_one_hour_ago` AS 'isOneHourAgo',
                `is_half_an_hour_ago` AS 'isHalfAnHourAgo',
                `is_ten_minute_ago` AS 'isTenMinuteAgo',
                `is_optional` AS 'isOptional',
                CASE WHEN `is_optional` = 1 THEN SUBSTRING(`reservations`.`optional_remind_time`, 1, 10)
                    ELSE NULL
                END AS 'optionalRemindDate',
                CASE WHEN `is_optional` = 1 THEN SUBSTRING(`reservations`.`optional_remind_time`, 12, 2)
                    ELSE NULL
                END AS 'optionalRemindHour',
                CASE WHEN `is_optional` = 1 THEN SUBSTRING(`reservations`.`optional_remind_time`, 15, 2)
                    ELSE NULL
                END AS 'optionalRemindMinute'
            "))
            ->join('meeting_accounts', 'reservations.id', '=', 'meeting_accounts.reservation_id')
            ->join('accounts', 'meeting_accounts.account_id', '=', 'accounts.id')
            ->where('reservations.id', '=', $reservationId)
            ->get();
    }

    /**
     * 重複している会議予約が存在するかチェック
     *
     * @param int $reservationId
     * @param int $meetingRoomId
     * @param string $startDate
     * @param string $endDate
     * @return object
     */
    public function checkMeetingDateTimeDuplicated(int $reservationId, int $meetingRoomId, string $startDate, string $endDate)
    {
        $query = Reservation::from('reservations')
            ->select(DB::raw("
                `reservations`.`title`,
                SUBSTRING(`reservations`.`start_time`, 1, 10) AS 'date',
                SUBSTRING(`reservations`.`start_time`, 12, 5) AS 'start',
                SUBSTRING(`reservations`.`end_time`, 12, 5) AS 'end',
                `accounts`.`name` AS 'organizerName'
            "))
            ->join('accounts', 'reservations.organizer_id', '=', 'accounts.id')
            ->where('meeting_room_id', '=', $meetingRoomId)
            ->whereRaw('((`reservations`.`start_time` BETWEEN ? AND ?)
                OR (`reservations`.`end_time` BETWEEN ? AND ?))
                AND (`reservations`.`start_time` <> ?)
                AND (`reservations`.`end_time` <> ?)',
                [$startDate, $endDate, $startDate, $endDate, $endDate, $startDate]);

        // 更新処理の場合は自身を含まない
        if (!empty($reservationId)) {
            $query->where('reservations.id', '<>', $reservationId);
        }
        return $query->get();
    }

    /**
     * リマインドを行う会議予約を取得
     *
     * @param string $currentDate
     * @return object
     */
    public function getMeetingRemindByCurrentDate(string $currentDate)
    {
        return Reservation::from('reservations')
            ->select(DB::raw("
                `reservations`.`id` AS 'reservationId',
                `reservations`.`title` AS 'title',
                `meeting_rooms`.`name` AS 'roomName',
                SUBSTRING(`reservations`.`start_time`, 1, 10) AS 'date',
                SUBSTRING(`reservations`.`start_time`, 12, 5) AS 'start',
                SUBSTRING(`reservations`.`end_time`, 12, 5) AS 'end',
                `reservations`.`comment` AS 'comment',
                `organizer`.`name` AS 'organizerName',
                `accounts`.`name` AS 'userName',
                `accounts`.`slackID` AS 'slackID'
            "))
            ->join('meeting_accounts', 'reservations.id', '=', 'meeting_accounts.reservation_id')
            ->join('accounts', 'meeting_accounts.account_id', '=', 'accounts.id')
            ->join('accounts as organizer', 'reservations.organizer_id', '=', 'organizer.id')
            ->join('meeting_rooms', 'reservations.meeting_room_id', '=', 'meeting_rooms.id')
            ->whereIn('reservations.id', function ($query) use ($currentDate) {
                $query->select('reservation_id')->from('reminds')
                ->where('remind_at', '=', $currentDate)
                ->where('is_complete', '=', Remind::IS_COMPLETE['FALSE']);
            })
            ->orderBy('accounts.id', 'asc')
            ->orderBy('reservations.start_time', 'asc')
            ->orderBy('reservations.end_time', 'asc')
            ->get();
    }

    /**
     * 通知に必要な会議予約情報を取得
     *
     * @param int $reservationId
     * @return object
     */
    public function getMeetingNotificationInfo(int $reservationId)
    {
        return Reservation::from('reservations')
            ->select(DB::raw("
                `reservations`.`id` AS 'reservationId',
                `reservations`.`title` AS 'title',
                `meeting_rooms`.`name` AS 'roomName',
                SUBSTRING(`reservations`.`start_time`, 1, 10) AS 'date',
                SUBSTRING(`reservations`.`start_time`, 12, 5) AS 'start',
                SUBSTRING(`reservations`.`end_time`, 12, 5) AS 'end',
                `reservations`.`comment` AS 'comment',
                `organizer`.`name` AS 'organizerName',
                `accounts`.`name` AS 'userName',
                `accounts`.`slackID` AS 'slackID'
            "))
            ->join('meeting_accounts', 'reservations.id', '=', 'meeting_accounts.reservation_id')
            ->join('accounts', 'meeting_accounts.account_id', '=', 'accounts.id')
            ->join('accounts as organizer', 'reservations.organizer_id', '=', 'organizer.id')
            ->join('meeting_rooms', 'reservations.meeting_room_id', '=', 'meeting_rooms.id')
            ->where('reservations.id', '=', $reservationId)
            ->orderBy('accounts.id', 'asc')
            ->orderBy('reservations.start_time', 'asc')
            ->orderBy('reservations.end_time', 'asc')
            ->get();
    }
}
