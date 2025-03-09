<?php

namespace App\Jobs;

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
    public function __construct($seconds)
    {
        $this->seconds = $seconds;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info("Job started: waiting for {$this->seconds} seconds");

        // 指定された秒数だけ待機
        sleep($this->seconds);

        Log::info("Job completed after {$this->seconds} seconds");
    }
}
