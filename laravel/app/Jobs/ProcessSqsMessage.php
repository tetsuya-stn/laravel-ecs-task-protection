<?php

namespace App\Jobs;

use App\Services\EcsTaskProtectionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessSqsMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * 待機する秒数
     *
     * @var int
     */
    protected $seconds;

    /**
     * Create a new job instance.
     *
     * @param int $seconds 待機する秒数
     * @return void
     */
    public function __construct(int $seconds)
    {
        $this->seconds = $seconds;
    }

    /**
     * Execute the job.
     *
     * @param EcsTaskProtectionService $protectionService
     * @return void
     */
    public function handle(EcsTaskProtectionService $protectionService)
    {
        // タスク保護の有効化
        $protectionEnabled = $protectionService->enableProtection(
            // 秒数から分に変換して少し余裕を持たせる
            ceil($this->seconds / 60) + 5
        );

        if (!$protectionEnabled) {
            Log::warning("ECSタスク保護の有効化に失敗しました。処理は継続します。");
        }

        try {
            Log::info("ジョブ開始: {$this->seconds}秒間待機します");

            // 指定された秒数だけ待機
            // プロセスが割り込まれないように、小さな単位で待機する
            $startTime = time();
            $endTime = $startTime + $this->seconds;

            while (time() < $endTime) {
                // 10秒ずつ待機
                $waitTime = min(10, $endTime - time());
                if ($waitTime > 0) {
                    sleep($waitTime);
                }

                // 定期的に進捗をログに出力
                $elapsed = time() - $startTime;
                $remaining = $this->seconds - $elapsed;

                if ($remaining > 0 && $remaining % 60 == 0) {
                    Log::info("処理中: 残り約{$remaining}秒");
                }
            }

            Log::info("ジョブ完了: {$this->seconds}秒間の待機が終了しました");
        } catch (\Exception $e) {
            Log::error("ジョブ実行中にエラーが発生しました: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
        } finally {
            // 処理が完了したらタスク保護を無効化
            if ($protectionEnabled) {
                $protectionDisabled = $protectionService->disableProtection();

                if (!$protectionDisabled) {
                    Log::warning("ECSタスク保護の無効化に失敗しました。");
                }
            }
        }
    }

    /**
     * ジョブの失敗を処理
     *
     * @param \Exception $exception
     * @return void
     */
    public function failed($exception = null)
    {
        Log::error("ジョブが失敗しました", [
            'seconds' => $this->seconds,
            'exception' => $exception ? $exception->getMessage() : 'Unknown error'
        ]);

        // ジョブが失敗した場合もタスク保護を無効化する
        try {
            $protectionService = app(EcsTaskProtectionService::class);
            $protectionService->disableProtection();
        } catch (\Exception $e) {
            Log::error("失敗処理中にタスク保護の無効化に失敗しました: " . $e->getMessage());
        }
    }
}
