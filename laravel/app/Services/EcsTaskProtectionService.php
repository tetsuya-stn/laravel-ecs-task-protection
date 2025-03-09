<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EcsTaskProtectionService
{
    /**
     * ECSタスクスケールイン保護を有効化
     *
     * @param int $expiresInMinutes 保護の有効期限（分）
     * @return bool 成功したかどうか
     */
    public function enableProtection(int $expiresInMinutes = 60): bool
    {
        try {
            $ecsAgentUri = env('ECS_AGENT_URI');

            if (empty($ecsAgentUri)) {
                Log::warning('ECS_AGENT_URI が設定されていません');
                return false;
            }

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->put("{$ecsAgentUri}/task-protection/v1/state", [
                'ProtectionEnabled' => true,
                'ExpiresInMinutes' => $expiresInMinutes,
            ]);

            if ($response->successful()) {
                Log::info("ECSタスク保護を有効化しました（{$expiresInMinutes}分間）", [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return true;
            } else {
                Log::error('ECSタスク保護の有効化に失敗しました', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('ECSタスク保護の有効化中に例外が発生しました', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }

    /**
     * ECSタスクスケールイン保護を無効化
     *
     * @return bool 成功したかどうか
     */
    public function disableProtection(): bool
    {
        try {
            $ecsAgentUri = env('ECS_AGENT_URI');

            if (empty($ecsAgentUri)) {
                Log::warning('ECS_AGENT_URI が設定されていません');
                return false;
            }

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->put("{$ecsAgentUri}/task-protection/v1/state", [
                'ProtectionEnabled' => false,
            ]);

            if ($response->successful()) {
                Log::info('ECSタスク保護を無効化しました', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return true;
            } else {
                Log::error('ECSタスク保護の無効化に失敗しました', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('ECSタスク保護の無効化中に例外が発生しました', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }
}
