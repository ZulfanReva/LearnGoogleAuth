<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
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
            'password' => 'hashed',
        ];
    }

    /**
     * Get the attributes that should not be nullable.
     *
     * @return array<string>
     */
    public function getRequiredFields(): array
    {
        return ['name', 'email'];
    }

    /**
     * Get the default password for OAuth users.
     *
     * @return string
     */
    public static function getDefaultPassword(): string
    {
        return 'google123';
    }

    /**
     * Check if user is using default password.
     *
     * @return bool
     */
    public function isUsingDefaultPassword(): bool
    {
        return $this->password && password_verify(self::getDefaultPassword(), $this->password);
    }
}
