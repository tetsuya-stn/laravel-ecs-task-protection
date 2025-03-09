<?php

namespace App\Console\Commands;

use App\Jobs\ProcessSqsMessage;
use Illuminate\Console\Command;

class DispatchSqsMessageCommand extends Command
{
    /**
     * コマンドの名前と使用法
     *
     * @var string
     */
    protected $signature = 'sqs:dispatch {seconds : ジョブ内で待機する秒数}';

    /**
     * コマンドの説明
     *
     * @var string
     */
    protected $description = '指定された秒数だけ待機するProcessSqsMessageジョブをSQSキューにディスパッチします';

    /**
     * コマンドの実行
     *
     * @return int
     */
    public function handle()
    {
        $seconds = (int) $this->argument('seconds');

        if ($seconds <= 0) {
            $this->error('待機秒数は1以上の整数を指定してください');
            return 1;
        }

        $this->info("SQSメッセージをディスパッチしています...");
        $this->info("待機秒数: {$seconds} 秒");

        // ジョブをディスパッチ
        ProcessSqsMessage::dispatch($seconds);

        $this->info('ジョブが正常にディスパッチされました!');

        return 0;
    }
}
