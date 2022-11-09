<?php

namespace App\Http\Services;

use \App\Notifications\Slack;
use \App\Repositories\Slack\SlackRepository;
use \App\Models\Reservation;
use \App\Http\Services\CommonService;

/**
 * slackサービス
 */
class SlackService
{
    /**
     * slackWEBHOOK一覧
     *
     * @var array
     */
    const SLACK_WEBHOOK = "https://hooks.slack.com/services/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";

    /**
     * 会議予約システムのベースURL
     *
     * @var string
     */
    const MEET_RESERVATION_SYSTEM_BASE_URL = "time-reservation-u.co.jp";

    /**
     * 送信者のデフォルト名
     *
     * @var string
     */
    const SLACK_SENDER_DEFAULT_NAME = "time-reservation-u";

    /**
     * デフォルトアイコン
     *
     * @var string
     */
    const SLACK_DEFAULT_ICON = "";

    /**
     * slack通知タイプ
     *
     * @var array
     */
    const SLACK_NOTIFICATION_TYPE = [
        'ADD' => 'ADD',
        'EDIT' => 'EDIT',
        'REMIND' => 'REMIND',
    ];

    /**
     * slack通知タイトル
     *
     * @var array
     */
    const SLACK_NOTIFICATION_TITLE = [
        self::SLACK_NOTIFICATION_TYPE['ADD'] => '【新規会議】',
        self::SLACK_NOTIFICATION_TYPE['EDIT'] => '【会議情報更新】',
        self::SLACK_NOTIFICATION_TYPE['REMIND'] => '【リマインド】',
    ];

    /**
     * 会議室予約モデルインスタンス
     *
     * @var Reservation
     */
    protected $reservationModel;

    /**
     * コンストラクト
     *
     * @param Reservation $reservationModel
     * @return void
     */
    public function __construct(Reservation $reservationModel)
    {
        $this->reservationModel = $reservationModel;
    }

    /**
     * リマインド対象者に会議情報を通知する
     *
     * @return void
     */
    public function sendToMember(string $slackId, string $message)
    {
        // slackIDが存在しない場合は処理を抜ける
        if (empty($slackId)) {
            return;
        }

        // slack送信処理
        $this->_slackSend(
            self::SLACK_WEBHOOK,
            $slackId,
            self::SLACK_SENDER_DEFAULT_NAME,
            self::SLACK_DEFAULT_ICON,
            $message
        );
    }

    /**
     * リマインドバッチ処理を実行
     *
     * @return void
     */
    public function remindBatchExecution()
    {
        // 5分毎にリマインドデータを取得
        $slackData = $this->reservationModel->getMeetingRemindByCurrentDate(date('Y-m-d H:i:00'));

        // リマインドデータが存在しない場合は処理を終了する
        if ($slackData->isEmpty()) {
            exit;
        }
        // 会議対象メンバーにリマインドを行う
        $this->_slackToTargetMeetingMembers(self::SLACK_NOTIFICATION_TYPE['REMIND'], $slackData);
    }

    /**
     * 保存した会議情報を通知
     *
     * @param string $slackType
     * @param int $reservationId
     * @return void
     */
    public function slackMeetingReservation(string $slackType, int $reservationId) : void
    {
        // 通知に必要な会議予約情報を取得
        $slackData = $this->reservationModel->getMeetingNotificationInfo($reservationId);
        // 会議対象メンバーにリマインドを行う
        $this->_slackToTargetMeetingMembers($slackType, $slackData);
    }


    /**
     * slack送信
     *
     * @param string $channel slackのHOOKURL ※設定必須
     * @param string $to 個人に送る場合は個人のslackID 全体の場合は空文字
     * @param string $from 任意で設定可能
     * @param string $icon 任意で設定可能
     * @param string $message 任意で設定可能
     * @return boolean
     */
    private function _slackSend($channel, $to, $from, $icon, $message)
    {
        try {
            // チャンネル設定がない場合はエラー
            if (empty($channel)) {
                throw new \Exception('channel is not found.');
            }
            // slack送信処理
            $slackHook = new SlackRepository($channel);
            $slackHook->notify(new Slack($to, $from, $icon, $message));

            return true;
        } catch(\Exception $e) {
            return false;
        }
    }

    /**
     * 会議対象メンバーにリマインドを行う
     *
     * @param string $slackType
     * @param object $slackData
     * @return void
     */
    private function _slackToTargetMeetingMembers(string $slackType, $slackData)
    {
        // リマインドメンバーの数だけループ
        foreach ($slackData as $slack) {

            // slackIDが存在しない場合は処理を抜ける
            if (empty($slack->slackID)) {
                continue;
            }
            // リマインドメッセージ作成
            $slackMessage = $this->_createSlackMessage(
                $slackType,
                $slack->title,
                $slack->roomName,
                $slack->date,
                $slack->start,
                $slack->end,
                $slack->comment,
                $slack->organizerName,
                CommonService::formatMeetingMembers($slackData, $slack->reservationId)
            );
            // slackに通知する
            $this->sendToMember($slack->slackID, $slackMessage);
        }
    }

    /**
     * リマインドメッセージを作成
     *
     * @param string $type
     * @param string $title
     * @param string $roomName
     * @param string $date
     * @param string $start
     * @param string $end
     * @param string|null $comment
     * @param string $organizerName
     * @param array $members
     * @return string
     */
    private function _createSlackMessage(string $type, string $title, string $roomName, string $date, string $start, string $end, $comment, string $organizerName, array $members) : string
    {
        echo "\n\n";
        $slackMessage = self:: SLACK_NOTIFICATION_TITLE[$type] . "下記の日時で会議が予約されています。\n";
        $slackMessage .= "```";
        $slackMessage .=  "【会議名】\n" . $title . "\n\n";
        $slackMessage .= "【会議日時】\n" . date("Y年m月d日", strtotime($date)) . " " . $start . "～" . $end . "\n\n";
        $slackMessage .= "【会議室】\n" . $roomName . "\n\n";
        $slackMessage .= "【会議主催者】\n" . $organizerName . "\n\n";
        $slackMessage .= "【会議メンバー】" . "\n" . CommonService::formatMemberListForMessage($members) . "\n";
        $slackMessage .= "【会議詳細】" . "\n" . $comment;
        $slackMessage .= "```";

        echo "\n\n";

        return $slackMessage;
    }
}