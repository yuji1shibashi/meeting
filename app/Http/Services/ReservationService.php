<?php

namespace App\Http\Services;

use App\Models\Reservation;
use App\Models\MeetingAccount;
use App\Models\Remind;
use Illuminate\Support\Facades\DB;
use App\Http\Services\CommonService;
use App\Http\Services\SlackService;

/**
 * 会議室予約システムサービス
 */
class ReservationService
{
    /**
     * 曜日
     *
     * @var array
     */
    const WEEKDAY = [
        'MONDAY' => '1',
        'TUESDAY' => '2',
        'WEDNESDAY' => '3',
        'THURSDAY' => '4',
        'FRYDAY' => '5'
    ];

    /**
     * 会議室予約モデルインスタンス
     *
     * @var Reservation
     */
    protected $reservationModel;

    /**
     * slackサービスインスタンス
     *
     * @var SlackService
     */
    protected $slackService;

    public function __construct(Reservation $reservationModel, SlackService $slackService)
    {
        $this->reservationModel = $reservationModel;
        $this->slackService = $slackService;
    }

    /**
     * 会議室情報取得
     *
     * @return object
     */
    public function getMeetingRooms()
    {
        return DB::table('meeting_rooms')->get();
    }

    /**
     * 会議対象メンバー情報取得
     *
     * @return object
     */
    public function getMeetingMembers()
    {
        return DB::table('accounts')->get();
    }

    /**
     * 検索対象の会議予約一覧取得
     *
     * @param array $params
     * @return array
     */
    public function search(array $params) : array
    {
        // 会議予約対象日、主催者ID、対象会議メンバーIDを格納
        $targetDate = (empty($params['targetDate'])) ? date("Y-m-d") : $params['targetDate'];
        $targetRepresentativeId = (!empty($params['targetRepresentativeId'])) ? $params['targetRepresentativeId'] : '';
        $targetMemberId = (!empty($params['targetMemberId'])) ? $params['targetMemberId'] : '';

        // 会議予約情報一覧を取得
        $meetingReservationList = $this->reservationModel->getTargetDateMeetingReservationList($targetDate, $targetRepresentativeId, $targetMemberId);
        // 会議室一覧を取得
        $meetingRoomList = $this->getMeetingRooms();
        // 会議室予約状況を整形してビューに返す
        return $this->_formatMeetingReservationList($meetingRoomList, $meetingReservationList);
    }

    /**
     * 検索対象の会議詳細取得
     *
     * @param int $reservationId
     * @return array
     */
    public function detail(int $reservationId) : array
    {
        // 検索対象の会議詳細取得
        $meetingReservationDetails = $this->reservationModel->getMeetingReservationDetailByReservationId($reservationId);
        // 取得した会議詳細を整形して返す
        return $this->_formatMeetingReservationDetail($meetingReservationDetails);
    }

    /**
     * 会議室予約登録
     *
     * @param array $params
     * @param array $repeatDateList
     * @return int
     */
    public function store(array $params, array $repeatDateList) : int
    {
        DB::beginTransaction();

        try {
            // 会議予約登録
            $meetingInfo = $this->_addMeetingReservation($params, $params['startDataTime'], $params['endDateTime']);

            // 会議予約IDが存在しない場合はエラー
            if (empty($meetingInfo->id)) {
                throw new \Exception("会議予約登録に失敗しました。");
            }
            // 会議メンバー登録
            $this->_addMeetingMembers($params['meetingMembers'], $meetingInfo->id);
            // リマインド設定が存在するかチェックし、存在する場合は登録処理を実行する
            $this->_existMeetingReminds($params, $meetingInfo->id, $params['startDataTime'], false);
            // リピートが存在する場合は対象日でリピート登録する
            $this->_existMeetingRepeat($params, $repeatDateList);

            DB::commit();

        } catch (\Exception $e) {
            echo $e;
            DB::rollback();
        }
        return $meetingInfo->id;
    }

    /**
     * 会議室予約更新
     *
     * @param array $params
     * @param array $repeatDateList
     * @return int
     */
    public function update(array $params, array $repeatDateList) : int
    {
        DB::beginTransaction();

        try {
            // 会議予約IDが存在しない場合はエラー
            if (empty($params['reservationId'])) {
                throw new \Exception("会議予約編集に失敗しました。");
            }

            // 会議予約更新
            $this->_updateMeetingReservation($params);
            // 既存会議予約メンバーを一度削除
            $this->_deleteMeetingMembers(intval($params['reservationId']));
            // リマインドを一度削除
            $this->_deleteMeetingReminds(intval($params['reservationId']));
            // 会議メンバー登録
            $this->_addMeetingMembers($params['meetingMembers'], intval($params['reservationId']));
            // リマインド設定が存在するかチェックし、存在する場合は登録処理を実行する
            $this->_existMeetingReminds($params, intval($params['reservationId']), $params['startDataTime'], false);
            // リピートが存在する場合は対象日でリピート登録する
            $this->_existMeetingRepeat($params, $repeatDateList);

            DB::commit();

        } catch (\Exception $e) {
            echo $e;
            DB::rollback();
        }
        return intval($params['reservationId']);
    }

    /**
     * 会議室予約削除
     *
     * @param int $reservationId
     * @return array
     */
    public function destroy(int $reservationId) : array
    {
        DB::beginTransaction();

        try {
            // 会議予約IDが存在しない場合はエラー
            if (empty($reservationId)) {
                throw new \Exception("会議予約削除に失敗しました。");
            }

            // 会議予約削除
            $this->_deleteMeetingReservation($reservationId);
            // 既存会議予約メンバー削除
            $this->_deleteMeetingMembers($reservationId);
            // 既存リマインドを削除
            $this->_deleteMeetingReminds($reservationId);

            DB::commit();

        } catch (\Exception $e) {
            echo $e;
            DB::rollback();
        }
        return ['success' => 'success'];
    }

    /**
     * 会議室予約テーブルに登録
     *
     * @param string $startDateTime
     * @param string $endDateTime
     * @param array $params
     * @return object
     */
    private function _addMeetingReservation(array $params, $startDateTime, $endDateTime)
    {
        return Reservation::create([
            'organizer_id' => $params['organizerId'],
            'meeting_room_id' => $params['meetingRoomId'],
            'title' => $params['title'],
            'start_time' => $startDateTime,
            'end_time' => $endDateTime,
            'comment' => $params['comment'],
            'color' => $params['color'],
            'is_remind' => CommonService::convertBooleanValuePhp($params['isRemind']),
            'is_three_days_ago' => CommonService::convertBooleanValuePhp($params['remind']['isThreeDaysAgo']),
            'is_two_days_ago' => CommonService::convertBooleanValuePhp($params['remind']['isTwoDaysAgo']),
            'is_prev_days_ago' => CommonService::convertBooleanValuePhp($params['remind']['isPrevDaysAgo']),
            'is_current_day' => CommonService::convertBooleanValuePhp($params['remind']['isCurrentDay']),
            'is_one_hour_ago' => CommonService::convertBooleanValuePhp($params['remind']['isOneHourAgo']),
            'is_half_an_hour_ago' => CommonService::convertBooleanValuePhp($params['remind']['isHalfAnHourAgo']),
            'is_ten_minute_ago' => CommonService::convertBooleanValuePhp($params['remind']['isTenMinuteAgo']),
            'is_optional' => CommonService::convertBooleanValuePhp($params['remind']['isOptionalRemind']),
            'optional_remind_time' => $params['remind']['optionalRemindDate']
        ]);
    }

    /**
     * 会議室予約テーブルに登録
     *
     * @param array $params
     * @return object
     */
    private function _updateMeetingReservation(array $params)
    {
        return Reservation::where('id', $params['reservationId'])->update([
            'organizer_id' => $params['organizerId'],
            'meeting_room_id' => $params['meetingRoomId'],
            'title' => $params['title'],
            'start_time' => $params['startDataTime'],
            'end_time' => $params['endDateTime'],
            'comment' => $params['comment'],
            'color' => $params['color'],
            'is_remind' => CommonService::convertBooleanValuePhp($params['isRemind']),
            'is_three_days_ago' => CommonService::convertBooleanValuePhp($params['remind']['isThreeDaysAgo']),
            'is_two_days_ago' => CommonService::convertBooleanValuePhp($params['remind']['isTwoDaysAgo']),
            'is_prev_days_ago' => CommonService::convertBooleanValuePhp($params['remind']['isPrevDaysAgo']),
            'is_current_day' => CommonService::convertBooleanValuePhp($params['remind']['isCurrentDay']),
            'is_one_hour_ago' => CommonService::convertBooleanValuePhp($params['remind']['isOneHourAgo']),
            'is_half_an_hour_ago' => CommonService::convertBooleanValuePhp($params['remind']['isHalfAnHourAgo']),
            'is_ten_minute_ago' => CommonService::convertBooleanValuePhp($params['remind']['isTenMinuteAgo']),
            'is_optional' => CommonService::convertBooleanValuePhp($params['remind']['isOptionalRemind']),
            'optional_remind_time' => $params['remind']['optionalRemindDate']
        ]);
    }

    /**
     * 会議予約削除
     *
     * @param int $reservationId
     * @return void
     */
    private function _deleteMeetingReservation(int $reservationId) : void
    {
        Reservation::where('id', $reservationId)->delete();
    }

    /**
     * 会議予約メンバー削除
     *
     * @param int $reservationId
     * @return void
     */
    private function _deleteMeetingMembers(int $reservationId) : void
    {
        MeetingAccount::where('reservation_id', $reservationId)->delete();
    }

    /**
     * 会議室予約メンバーテーブルに登録
     *
     * @param array $meetingMembers
     * @param int $reservationId
     * @return void
     */
    private function _addMeetingMembers(array $meetingMembers, int $reservationId) : void
    {
        // メンバーの数だけループを行い登録
        foreach ($meetingMembers as $member) {
            MeetingAccount::create([
                'reservation_id' => $reservationId,
                'account_id' => intval($member['memberId'])
            ]);
        }
    }

    /**
     * リマインド新規作成
     *
     * @param array $meetingReminds
     * @param integer $reservationId
     * @return void
     */
    private function _addMeetingReminds(array $meetingReminds, int $reservationId) : void
    {
        // リマインドの数だけループを行い登録
        foreach ($meetingReminds as $remindDate) {
            Remind::create([
                'reservation_id' => $reservationId,
                'remind_at' => $remindDate,
                'is_complete' => Remind::IS_COMPLETE['FALSE']
            ]);
        }
    }

    /**
     * 会議予約リマインド削除
     *
     * @param int $reservationId
     * @return void
     */
    private function _deleteMeetingReminds(int $reservationId) : void
    {
        Remind::where('reservation_id', $reservationId)->delete();
    }

    /**
     * 会議室予約一覧を整形する
     *
     * @param object $meetingRoomList
     * @param object $meetingReservationList
     * @return array
     */
    private function _formatMeetingReservationList($meetingRoomList, $meetingReservationList) : array
    {
        $formatingData = [];

        // 会議室の数だけループ
        foreach ($meetingRoomList as $meetingRoom) {
            // 整形した会議予約情報を配列に格納
            $formatingData[] = [
                'meetingRoomName' => $meetingRoom->name,
                'reservationInfo' => $this->_getMeetingReservationInfo($meetingRoom->id, $meetingReservationList),
            ];
        }
        return $formatingData;
    }

    /**
     * 会議予約情報を取得
     *
     * @param int $meetingRoomId
     * @param object $meetingReservationList
     * @return array
     */
    private function _getMeetingReservationInfo(int $meetingRoomId, $meetingReservationList) : array
    {
        $setReservationIds = [];
        $reservationInfo = [];

        // 会議予約の数だけループ
        foreach ($meetingReservationList as $reservation) {

            // 会議予約情報を整形していないかつ、会議室IDが一致する場合
            if (!in_array($reservation->reservationId, $setReservationIds, true) && $meetingRoomId === $reservation->roomId) {
                // 会議室予約情報を整形
                $reservationInfo[] = $this->_formatMeetingReservationInfo($meetingReservationList, $reservation);
                // 整形した会議予約情報は配列にIDを格納
                array_push($setReservationIds, $reservation->reservationId);
            }
        }
        return $reservationInfo;
    }

    /**
     * 会議室予約情報を整形
     *
     * @param object $meetingReservationList
     * @param object $reservation
     * @return array
     */
    private function _formatMeetingReservationInfo($meetingReservationList, $reservation) : array
    {
        return [
            'reservationId' => $reservation->reservationId,
            'title' => $reservation->title,
            'roomName' => $reservation->roomName,
            'date' => $reservation->date,
            'start' => $reservation->start,
            'end' => $reservation->end,
            'comment' => $reservation->comment,
            'color' => $reservation->color,
            'organizerName' => $reservation->organizerName,
            'members' => CommonService::formatMeetingMembers($meetingReservationList, $reservation->reservationId)
        ];
    }

    /**
     * 会議室予約詳細を整形して返す
     *
     * @param object $meetingReservationDetails
     * @return array
     */
    private function _formatMeetingReservationDetail($meetingReservationDetails) : array
    {
        $setDetail = [];
        $meetingMembers = [];
        $isSetDetail = false;

        // 会議メンバーの数だけループ
        foreach ($meetingReservationDetails as $detail) {
            // 会議詳細をセットしていない場合
            if (!$isSetDetail) {
                // 会議室予約詳細をセットして取得
                $setDetail = $this->_getMeetingReservationDetail($detail);
                $isSetDetail = true;
            }
            // 会議メンバーをまとめる
            $meetingMembers[] = ['memberId' => $detail->userId, 'memberName' => $detail->userName];
        }
        // 会議時予約詳細を1つの配列にまとめて返す
        return array_merge($setDetail, ['members' => $meetingMembers]);
    }

    /**
     * 会議室予約詳細をセットして返す
     *
     * @param object $detail
     * @return array
     */
    private function _getMeetingReservationDetail($detail) : array
    {
        return [
            'title' => $detail->title,
            'meetingRoomId' => $detail->meetingRoomId,
            'date' => $detail->date,
            'startHour' => $detail->startHour,
            'startMinute' => $detail->startMinute,
            'endHour' => $detail->endHour,
            'endMinute' => $detail->endMinute,
            'comment' => $detail->comment,
            'color' => $detail->color,
            'organizerId' => $detail->organizerId,
            'isRemind' => boolval ($detail->isRemind),
            'isRepeat' => boolval ($detail->isRepeat),
            'isThreeDaysAgo' => boolval ($detail->isThreeDaysAgo),
            'isTwoDaysAgo' => boolval ($detail->isTwoDaysAgo),
            'isPrevDaysAgo' => boolval ($detail->isPrevDaysAgo),
            'isCurrentDay' => boolval ($detail->isCurrentDay),
            'isOneHourAgo' => boolval ($detail->isOneHourAgo),
            'isHalfAnHourAgo' => boolval ($detail->isHalfAnHourAgo),
            'isTenMinuteAgo' => boolval ($detail->isTenMinuteAgo),
            'isOptional' => boolval ($detail->isOptional),
            'optionalRemindDate' => $detail->optionalRemindDate,
            'optionalRemindHour' => $detail->optionalRemindHour,
            'optionalRemindMinute' => $detail->optionalRemindMinute
        ];
    }

    /**
     * リマインド日時一覧を取得
     *
     * @param string $startTime
     * @param object $params
     * @param boolean $isRepeat
     * @return array
     */
    private function _getRemindDateList($params, $startTime, $isRepeat) : array
    {
        $remindDateList = [];
        $timeStampByDay = 86400;
        $timeStampByHour = 3600;

        // 「3日前」にチェックがついている場合
        if (CommonService::convertBooleanValuePhp($params['remind']['isThreeDaysAgo']) === CommonService::BOOL_VALUE['TRUE']) {
            $remindDateList[] = date('Y-m-d 10:00:00', (strtotime($startTime) - ($timeStampByDay * 3)));
        }

        // 「2日前」にチェックがついている場合
        if (CommonService::convertBooleanValuePhp($params['remind']['isTwoDaysAgo']) === CommonService::BOOL_VALUE['TRUE']) {
            $remindDateList[] = date('Y-m-d 10:00:00', (strtotime($startTime) - ($timeStampByDay * 2)));
        }

        // 「会議前日」にチェックがついている場合
        if (CommonService::convertBooleanValuePhp($params['remind']['isPrevDaysAgo']) === CommonService::BOOL_VALUE['TRUE']) {
            $remindDateList[] = date('Y-m-d 10:00:00', (strtotime($startTime) - $timeStampByDay));
        }

        // 「会議当日」にチェックがついている場合
        if (CommonService::convertBooleanValuePhp($params['remind']['isCurrentDay']) === CommonService::BOOL_VALUE['TRUE']) {
            $remindDateList[] = date('Y-m-d 10:00:00', strtotime($startTime));
        }

        // 「1時間前」にチェックがついている場合
        if (CommonService::convertBooleanValuePhp($params['remind']['isOneHourAgo']) === CommonService::BOOL_VALUE['TRUE']) {
            $remindDateList[] = date('Y-m-d H:i:s', (strtotime($startTime) - $timeStampByHour));
        }

        // 「30分前」にチェックがついている場合
        if (CommonService::convertBooleanValuePhp($params['remind']['isHalfAnHourAgo']) === CommonService::BOOL_VALUE['TRUE']) {
            $remindDateList[] = date('Y-m-d H:i:s', (strtotime($startTime) - ($timeStampByHour / 2)));
        }

        // 「10分前」にチェックがついている場合
        if (CommonService::convertBooleanValuePhp($params['remind']['isTenMinuteAgo']) === CommonService::BOOL_VALUE['TRUE']) {
            $remindDateList[] = date('Y-m-d H:i:s', (strtotime($startTime) - ($timeStampByHour / 6)));
        }

        // 「任意リマインド設定」にチェックがついている場合
        if (CommonService::convertBooleanValuePhp($params['remind']['isOptionalRemind']) === CommonService::BOOL_VALUE['TRUE'] && !$isRepeat) {
            $remindDateList[] = $params['remind']['optionalRemindDate'];
        }
        return $remindDateList;
    }

    /**
     * 会議予約重複チェック
     *
     * @param object $params
     * @param array $repeatDateList
     * @return array
     */
    public function checkMeetingDateTimeDuplicated($params, array $repeatDateList) : array
    {
        $duplicatedMessages = [];
        $meetingDuplicates = [];

        $meetingDuplicated = $this->reservationModel->checkMeetingDateTimeDuplicated(
            intval($params['reservationId']),
            intval($params['meetingRoomId']),
            $params['startDataTime'],
            $params['endDateTime']
        );
        $meetingDuplicates = array_merge($meetingDuplicates, $meetingDuplicated->toArray());

        // リピートが設定されている場合は重複チェックを行う
        $meetingDuplicates = $this->checkMeetingDateTimeDuplicatedForRepeat($params, $repeatDateList, $meetingDuplicates);

        foreach ($meetingDuplicates as $duplicated) {
            $duplicatedMessages[] = $this->_createDuplicatedMessages(
                $duplicated['title'],
                $duplicated['date'],
                $duplicated['start'],
                $duplicated['end'],
                $duplicated['organizerName']
            );
        }
        return $duplicatedMessages;
    }

    /**
     * 会議予約重複チェック（リピート）
     *
     * @param object $params
     * @param array $meetingDuplicates
     * @param array $meetingDuplicates
     * @return array
     */
    public function checkMeetingDateTimeDuplicatedForRepeat($params, array $repeatDateList, array $meetingDuplicates) : array
    {
        foreach ($repeatDateList as $repeat) {
            $meetingDuplicated = $this->reservationModel->checkMeetingDateTimeDuplicated(
                intval($params['reservationId']),
                intval($params['meetingRoomId']),
                $repeat . ' ' . $params['startTime'],
                $repeat . ' ' . $params['endTime']
            );
            $meetingDuplicates = array_merge($meetingDuplicates, $meetingDuplicated->toArray());
        }
        return $meetingDuplicates;
    }

    /**
     * 会議予約重複メッセージ作成
     *
     * @param string $title
     * @param string $date
     * @param string $start
     * @param string $end
     * @param string $organizerName
     * @return string
     */
    private function _createDuplicatedMessages(string $title, string $date, string $start, string $end, string $organizerName) : string
    {
        $message = date("Y年m月d日 ", strtotime($date));
        $message .= $start . '～' . $end . 'に会議「' . $title . '」が予約されています。<br>';
        $message .= 'この会議を登録する場合は主催者「' . $organizerName . '」と調整後に先の予約を削除してください。';
        return $message;
    }

    /**
     * リマインド設定が存在するかチェック
     *
     * @param object $params
     * @param int $meetingId
     * @param string $startDataTime
     * @param boolean $isRepeat
     * @return void
     */
    private function _existMeetingReminds($params, int $meetingId, string $startDataTime, bool $isRepeat) : void
    {
        // リマインド設定がある場合
        if (CommonService::convertBooleanValuePhp($params['isRemind']) === CommonService::BOOL_VALUE['TRUE']) {
            // リマインド日時一覧を取得する
            $remindDateList = $this->_getRemindDateList($params, $startDataTime, $isRepeat);
            // リマインド日時を登録
            $this->_addMeetingReminds($remindDateList, $meetingId);
        }
    }

    /**
     * リピート設定が存在するかチェック
     *
     * @param array $params
     * @param array $repeatDateList
     * @return void
     */
    private function _existMeetingRepeat(array $params, array $repeatDateList) : void
    {
        // リマインド設定がある場合
        if (CommonService::convertBooleanValuePhp($params['isRepeat']) === CommonService::BOOL_VALUE['TRUE']) {
            foreach ($repeatDateList as $repeatDate) {
                // ミーティング情報登録
                $meetingInfo = $this->_addMeetingReservation(
                    $params,
                    $this->_formatDateTime($repeatDate, $params['startTime']),
                    $this->_formatDateTime($repeatDate, $params['endTime'])
                );
                // 会議メンバー登録
                $this->_addMeetingMembers($params['meetingMembers'], $meetingInfo->id);
                // リマインド設定が存在するかチェックし、存在する場合は登録処理を実行する
                $this->_existMeetingReminds($params, $meetingInfo->id, $this->_formatDateTime($repeatDate, $params['startTime']), true);
            }
        }
    }

    /**
     * 日時に整形する
     *
     * @param string $date
     * @param string $time
     * @return string
     */
    private function _formatDateTime(string $date, string $time) : string
    {
        return $date . ' ' . $time;
    }

    /**
     * slackの送信を行うかチェック
     *
     * @param string $isSlack
     * @param int $meetingId
     * @param string $type
     * @return void
     */
    public function isSlackSend($isSlack, $meetingId, $type) : void
    {
        // slack通知「あり」の場合
        if (CommonService::convertBooleanValuePhp($isSlack) === CommonService::BOOL_VALUE['TRUE']) {
            // slack通知を行う
            $this->slackService->slackMeetingReservation($type, $meetingId);
        }
    }

    /**
     * リピート日一覧取得
     *
     * @param object $params
     * @return array
     */
    public function getRepeatDateList($params) : array
    {
        $repeatDateList = [];

        if (CommonService::convertBooleanValuePhp($params['isRepeat']) === CommonService::BOOL_VALUE['TRUE']) {
            $repeatDateList = $this->_getRepeatDate($params);
        }
        return $repeatDateList;
    }

    /**
     * リピート日を取得
     *
     * @param object $params
     * @return array
     */
    private function _getRepeatDate($params) : array
    {
        $repeatDateList = [];
        $targetDate = $params['repeat']['start'];
        $startDate = $params['repeat']['start'];
        $endDate = $params['repeat']['end'];

        // 簡易設定の日付をセット
        while (strtotime($targetDate) <= strtotime($endDate)) {
            // 月初
            $repeatDateList = $this->_isRepeatManthFirstOrLast($repeatDateList, $targetDate, $params['repeat']['isMonthFirst'], 'first day of ');
            // 月末
            $repeatDateList = $this->_isRepeatManthFirstOrLast($repeatDateList, $targetDate, $params['repeat']['isMonthLast'], 'last day of ');
            // 月曜
            $repeatDateList = $this->_isRepeatWeekday($repeatDateList, $targetDate, $params['repeat']['isMonday'], self::WEEKDAY['MONDAY']);
            // 火曜
            $repeatDateList = $this->_isRepeatWeekday($repeatDateList, $targetDate, $params['repeat']['isTuesday'], self::WEEKDAY['TUESDAY']);
            // 水曜
            $repeatDateList = $this->_isRepeatWeekday($repeatDateList, $targetDate, $params['repeat']['isWednesday'], self::WEEKDAY['WEDNESDAY']);
            // 木曜
            $repeatDateList = $this->_isRepeatWeekday($repeatDateList, $targetDate, $params['repeat']['isThursday'], self::WEEKDAY['THURSDAY']);
            // 金曜
            $repeatDateList = $this->_isRepeatWeekday($repeatDateList, $targetDate, $params['repeat']['isFriday'], self::WEEKDAY['FRYDAY']);
            // 対象日に1日加算
            $targetDate = date("Y-m-d", strtotime($targetDate . ' +1 day'));
        }

        // 任意設定がない場合は空配列を格納
        $optionalRepeats = (!empty($params['repeat']['optionalRepeats'])) ? $params['repeat']['optionalRepeats'] : [];
        // 任意リピート日をセット
        $repeatDateList = $this->_checkOptionalRepeat($repeatDateList, $params['repeat']['isOptionalRepeat'], $optionalRepeats, $startDate, $endDate);

        return $repeatDateList;
    }

    /**
     * リピート対象の月初、月末かチェック
     *
     * @param array $repeatDateList
     * @param string $targetDate
     * @param string $isFirstOrLast
     * @param string $targetFirstOrLast
     * @return array
     */
    private function _isRepeatManthFirstOrLast(array $repeatDateList, string $targetDate, string $isFirstOrLast, string $targetFirstOrLast) : array
    {
        // 対象の月初、月末にチェックがついているかつ、対象の日付が対象の月初、月末の場合
        if (CommonService::convertBooleanValuePhp($isFirstOrLast)  === CommonService::BOOL_VALUE['TRUE']
            && ($targetDate === date('Y-m-d', strtotime($targetFirstOrLast . substr($targetDate, 0, 7))))
        ) {
            $repeatDateList[] = $targetDate;
        }
        return $repeatDateList;
    }

    /**
     * リピート対象の曜日かチェック
     *
     * @param array $repeatDateList
     * @param string $targetDate
     * @param string $isWeekday
     * @param string $targetWeekday
     * @return array
     */
    private function _isRepeatWeekday(array $repeatDateList, string $targetDate, string $isWeekday, string $targetWeekday) : array
    {
        // 対象の曜日にチェックがついているかつ、対象の日付が対象の曜日の場合
        if (CommonService::convertBooleanValuePhp($isWeekday)  === CommonService::BOOL_VALUE['TRUE']
            && $this->_checkTargetWeekday($targetDate, $targetWeekday)
        ) {
            $repeatDateList[] = $targetDate;
        }
        return $repeatDateList;
    }

    /**
     * 曜日チェック
     *
     * @param string $targetDate
     * @param string $TargetWeekday
     * @return boolean
     */
    private function _checkTargetWeekday(string $targetDate, string $targetWeekday) : bool
    {
        return (date('w', strtotime($targetDate)) === $targetWeekday) ? true : false;
    }

    /**
     * 任意リピート設定を行う
     *
     * @param array $repeatDateList
     * @param string $isOptionalRepeat
     * @param array $optionalRepeats
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    private function _checkOptionalRepeat(array $repeatDateList, string $isOptionalRepeat, array $optionalRepeats, string $startDate, string $endDate) : array
    {
        // 任意設定を行わない場合は処理を抜ける
        if (CommonService::convertBooleanValuePhp($isOptionalRepeat) !== CommonService::BOOL_VALUE['TRUE']) {
            return $repeatDateList;
        }

        // 年の数だけループ
        for ($year = intval(substr($startDate, 0, 4)); $year <= intval(substr($endDate, 0, 4)); $year++) {
            // 月の数だけループ
            for ($month = intval(substr($startDate, 5, 2)); $month <= intval(substr($endDate, 5, 2)); $month++) {
                // 任意リピートをセット
                $repeatDateList = $this->_setOptionalRepeat(
                    strtotime($startDate),
                    strtotime($endDate),
                    $year .'-'. str_pad($month, 2, 0, STR_PAD_LEFT),
                    $repeatDateList,
                    $optionalRepeats
                );
            }
        }
        return $repeatDateList;
    }

    /**
     * 任意リピートをセット
     *
     * @param int $startDate
     * @param int $endDate
     * @param string $yyyymm
     * @param array $repeatDateList
     * @param array $optionalRepeats
     * @return array
     */
    private function _setOptionalRepeat(int $startDate, int $endDate, string $yyyymm, array $repeatDateList, array $optionalRepeats) : array
    {
        // 選択された任意リピート日の数だけループ
        foreach ($optionalRepeats as $repeat) {
            $repeatDate = $yyyymm . '-' . $repeat['repeatDay'];

            // リピート開始から終了までの間の場合かつ、同一のリピート日が入っていない場合はリピート日格納
            if ($startDate <= strtotime($repeatDate) && strtotime($repeatDate) <= $endDate
                && !in_array($repeatDate, $repeatDateList)
            ) {
                $repeatDateList[] = $repeatDate;
            }
        }
        return $repeatDateList;
    }
}