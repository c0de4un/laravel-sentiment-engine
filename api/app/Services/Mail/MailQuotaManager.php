<?php

namespace App\Services\Mail;

use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;

final readonly class MailQuotaManager
{
    public function __construct(
        private string $provider = 'beget' // или 'yandex'
    ) {}

    public function getAvailableQuota(): int
    {
        $hourlyLimit = (int) config("mail.limits.$this->provider.hour", 100);
        $dailyLimit = (int) config("mail.limits.$this->provider.day", 500);

        $hourlyUsed = $this->getUsage('hour');
        $dailyUsed = $this->getUsage('day');

        $hourlyAvailable = max(0, $hourlyLimit - $hourlyUsed);
        $dailyAvailable = max(0, $dailyLimit - $dailyUsed);

        return min($hourlyAvailable, $dailyAvailable);
    }

    /**
     * @throws LockTimeoutException
     */
    public function incrementUsage(): void
    {
        $this->incrementWindow('hour', 3600); // TTL 1 час
        $this->incrementWindow('day', 86400); // TTL 1 сутки
    }

    public function getSecondsUntilReset(): int
    {
        // Считаем, сколько секунд осталось до конца текущего часа
        $secondsInHour = (int) date('s') + ((int) date('i') * 60);
        return 3600 - $secondsInHour;
    }

    private function getUsage(string $window): int
    {
        $key = $this->getKey($window);
        // Используем фасад напрямую, он типизирован корректно
        return (int) Cache::get($key, 0);
    }

    /**
     * @throws LockTimeoutException
     */
    private function incrementWindow(string $window, int $ttl): void
    {
        $key = $this->getKey($window);

        Cache::lock($key . '_lock', 1)->block(3, function () use ($key, $ttl) {
            if (!Cache::has($key)) {
                Cache::put($key, 1, $ttl);
            } else {
                Cache::increment($key);
            }
        });
    }

    private function getKey(string $window): string
    {
        $time = $window === 'hour' ? date('Y-m-d-H') : date('Y-m-d');
        return "mail_quota:$this->provider:$window:$time";
    }
}
