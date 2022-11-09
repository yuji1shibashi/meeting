<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\SlackMessage;

/**
 * slack送信処理
 */
class Slack extends Notification
{
    use Queueable;

    /**
     * slackに送るメッセージ
     *
     * @var string
     */
    protected $content;

    /**
     * slackチャンネル名
     *
     * @var string
     */
    protected $hannel;

    /**
     * slack送信者名
     *
     * @var string
     */
    protected $sender;

    /**
     * slack送信者のアイコン
     *
     * @var string
     */
    protected $icon;

    /**
     * slack情報をセットする
     *
     * @return void
     */
    public function __construct($channel, $sender, $icon, $message)
    {
        $this->channel = $channel;
        $this->name = $sender;
        $this->icon = $icon;
        $this->content = $message;
    }

    /**
     * 通知チャンネル取得
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['slack'];
    }

    /**
     * 通知に使用する配列を取得
     * ※現状使用しないため空配列を返す
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [];
    }

    /**
     * 対象のslackへ送信
     *
     * @param $notifiable
     * @return $this
     */
    public function toSlack($notifiable)
    {
        return (new SlackMessage)
            ->from($this->name)
            ->image($this->icon)
            ->to($this->channel)
            ->content($this->content);
    }
}