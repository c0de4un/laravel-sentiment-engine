<?php

namespace App\Services\Mail;

use App\Jobs\SendEmailJob;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;

final readonly class SmartMailDispatcher
{
    public function __construct(
        private MailQuotaManager $quotaManager
    ) {}

    /**
     * Dispatch email with smart quota checking.
     *
     * Проактивно проверяет доступные квоты перед отправкой в очередь.
     * Если квота исчерпана — автоматически откладывает отправку до сброса лимита.
     */
    public function dispatch(Mailable $mailable, string $recipient): void
    {
        $availableQuota = $this->quotaManager->getAvailableQuota();

        Log::info('SmartMailDispatcher: checking quota', [
            'recipient' => $recipient,
            'available_quota' => $availableQuota,
            'mailable' => get_class($mailable),
        ]);

        if ($availableQuota > 0) {
            // Квота есть — отправляем в воркер прямо сейчас
            Log::info('SmartMailDispatcher: quota available, dispatching immediately', [
                'recipient' => $recipient,
            ]);

            SendEmailJob::dispatch($mailable, $recipient);
            return;
        }

        // Квота исчерпана! Вычисляем задержку и ставим в очередь с отложенным выполнением.
        // Воркер даже не будет дергаться, экономя ресурсы.
        $delaySeconds = $this->quotaManager->getSecondsUntilReset();

        Log::info('SmartMailDispatcher: quota exhausted, scheduling delayed dispatch', [
            'recipient' => $recipient,
            'delay_seconds' => $delaySeconds,
        ]);

        SendEmailJob::dispatch($mailable, $recipient)
            ->delay(now()->addSeconds($delaySeconds + 10)); // +10 сек buffer
    }

    /**
     * Dispatch email immediately without quota checking.
     * Используется для критичных писем (сброс пароля), когда нельзя ждать.
     */
    public function dispatchImmediate(Mailable $mailable, string $recipient): void
    {
        Log::info('SmartMailDispatcher: immediate dispatch (bypassing quota)', [
            'recipient' => $recipient,
            'mailable' => get_class($mailable),
        ]);

        SendEmailJob::dispatch($mailable, $recipient);
    }

    /**
     * Get current quota status.
     */
    public function getQuotaStatus(): array
    {
        return [
            'available' => $this->quotaManager->getAvailableQuota(),
            'seconds_until_reset' => $this->quotaManager->getSecondsUntilReset(),
        ];
    }
}
