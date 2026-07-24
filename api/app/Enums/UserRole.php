<?php

namespace App\Enums;

/**
 * @method static self User()
 * @method static self Admin()
 */
enum UserRole: string
{
    case User = 'user';
    case Admin = 'admin';
    // case Moderator = 'moderator'; // Можно легко добавить при масштабировании

    /**
     * Человекочитаемое название роли (для админ-панели или логов).
     */
    public function label(): string
    {
        return match ($this) {
            self::User => 'Пользователь',
            self::Admin => 'Администратор',
        };
    }

    /**
     * Проверка на совпадение с переданной ролью.
     * Удобнее, чем $user->role === UserRole::Admin
     */
    public function is(self $role): bool
    {
        return $this === $role;
    }

    /**
     * Возвращает все возможные варианты в формате [value => label].
     * Идеально для генерации выпадающих списков (select) в API или Blade.
     *
     * @return array<string, string>
     */
    public static function toArray(): array
    {
        return array_map(fn (self $case) => $case->label(), self::cases());
    }
}
