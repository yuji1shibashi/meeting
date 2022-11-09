<?php

namespace App\Repositories\Slack;

use Illuminate\Notifications\Notifiable;

class SlackRepository implements SlackRepositoryInterface
{
    use Notifiable;

    protected $slack;

    /**
     * slackUrl
     *
     * @var string
     */
    protected $webhookUrl = "";

    /**
     * コンストラクター
     */
    public function __construct($webhookUrl)
    {
        $this->webhookUrl = $webhookUrl;
    }

    /**
     * slackのルートを返す
     *
     * @return string
     */
    public function routeNotificationForSlack()
    {
        return $this->webhookUrl;
    }
}