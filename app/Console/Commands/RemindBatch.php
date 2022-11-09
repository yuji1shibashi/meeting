<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Services\SlackService;

class RemindBatch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'batch:remind';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '会議予約リマインドバッチ処理';

    /**
     * slackサービスインスタンス
     *
     * @var SlackService
     */
    protected $slackService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(SlackService $slackService)
    {
        $this->slackService = $slackService;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->slackService->remindBatchExecution();
    }
}
