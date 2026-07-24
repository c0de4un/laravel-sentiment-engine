<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/**
 * @property int                       $id
 * @property string                    $name
 * @property string                    $email
 * @property Carbon|null               $email_verified_at
 * @property string                    $password
 * @property string                    $role
 * @property string                    $status
 * @property Carbon|null               $last_login_at
 * @property string|null               $remember_token
 * @property Carbon|null               $created_at
 * @property Carbon|null               $updated_at
 *
 * @method static User create(array $attributes = [])
 * @method static Builder<static>|User newModelQuery()
 * @method static Builder<static>|User newQuery()
 * @method static Builder<static>|User query()
 */
final class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'role',
        'status',
        'last_login_at',
        'remember_token',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at'     => 'datetime',
            'created_at'        => 'datetime',
            'updated_at'        => 'datetime',
            'password'          => 'hashed',
        ];
    }

    public function markAsLoggedIn(): void
    {
        $this->updateQuietly(['last_login_at' => now()]);
    }
}
