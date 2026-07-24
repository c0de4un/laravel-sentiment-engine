<?php

namespace App\Enums;

/**
 * @method static self Active()
 * @method static self Suspended()
 * @method static self Deleted()
 */
enum UserStatus: string
{
    case Active = 'active';
    case Suspended = 'suspended';
    case Deleted = 'deleted';

    /**
     * Человекочитаемое название статуса.
     */
    public function label(): string
    {
        return match ($this) {
            self::Active => 'Активен',
            self::Suspended => 'Заблокирован',
            self::Deleted => 'Удален',
        };
    }

    /**
     * Проверка на совпадение со статусом.
     */
    public function is(self $status): bool
    {
        return $this === $status;
    }

    /**
     * Возвращает семантический цвет для UI (Nuxt.js / Tailwind CSS).
     * Отличная практика: бэкенд диктует логику отображения, фронтенд только применяет класс.
     */
    public function color(): string
    {
        return match ($this) {
            self::Active => 'green',
            self::Suspended => 'red',
            self::Deleted => 'gray',
        };
    }

    /**
     * Возвращает все варианты в формате [value => label].
     *
     * @return array<string, string>
     */
    public static function toArray(): array
    {
        return array_map(fn (self $case) => $case->label(), self::cases());
    }
}
