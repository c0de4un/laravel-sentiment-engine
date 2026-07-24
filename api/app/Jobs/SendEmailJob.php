<?php

namespace App\Jobs;

use App\Services\Mail\MailQuotaManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Throwable;

final class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public int $timeout = 180;

    public array $backoff = [10, 30, 90, 270];

    /**
     * Create a new job instance.
     */
    public function __construct(
        public readonly Mailable $mailable,
        public readonly string $recipientEmail
    ) {
        $this->onQueue('mailers');
    }

    /**
     * Execute the job.
     * @throws LockTimeoutException
     * @throws TransportExceptionInterface
     */
    public function handle(MailQuotaManager $quotaManager): void
    {
        try {
            Log::info('Attempting to send email', [
                'recipient' => $this->recipientEmail,
                'mailable' => get_class($this->mailable),
            ]);

            // Пытаемся отправить письмо
            Mail::to($this->recipientEmail)->send($this->mailable);

            $quotaManager->incrementUsage();

            Log::info('Email sent successfully', [
                'recipient' => $this->recipientEmail,
            ]);
        } catch (TransportExceptionInterface $e) {
            $this->handleSmtpException($e, $quotaManager);
        }
    }

    /**
     * @throws TransportExceptionInterface
     */
    private function handleSmtpException(TransportExceptionInterface $e, MailQuotaManager $quotaManager): void
    {
        $message = $e->getMessage();
        $code = $e->getCode();

        Log::warning('SMTP error occurred', [
            'recipient' => $this->recipientEmail,
            'error' => $message,
            'code' => $code,
            'attempt' => $this->attempts(),
        ]);
        $isRateLimitError = $this->isRateLimitError($message, $code);

        if ($isRateLimitError) {
            Log::warning('Rate limit exceeded, scheduling retry after quota reset', [
                'recipient' => $this->recipientEmail,
            ]);
            $delaySeconds = $quotaManager->getSecondsUntilReset();

            self::dispatch($this->mailable, $this->recipientEmail)
                ->delay(now()->addSeconds($delaySeconds + 10)); // +10 сек buffer

            $this->delete();

            return;
        }

        throw $e;
    }

    private function isRateLimitError(string $message, int $code): bool
    {
        $rateLimitCodes = [421, 432, 450, 451, 452, 550, 552, 553, 554];

        if (in_array($code, $rateLimitCodes, true)) {
            return true;
        }

        // Ключевые слова в сообщении об ошибке
        $rateLimitKeywords = [
            'rate limit',
            'too many',
            'exceeded',
            'quota',
            'throttle',
            'limit reached',
            'maximum',
            'daily limit',
            'hourly limit',
            'sending limit',
        ];

        $messageLower = strtolower($message);

        foreach ($rateLimitKeywords as $keyword) {
            if (str_contains($messageLower, $keyword)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $exception): void
    {
        Log::error('Email job failed permanently', [
            'recipient' => $this->recipientEmail,
            'mailable' => get_class($this->mailable),
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts(),
        ]);
    }
}
