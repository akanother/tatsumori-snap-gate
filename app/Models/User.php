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

    // Constants
    const ADMIN_ROLE_THRESHOLD = 100; // この数字「以上」は管理者とする

    protected $connection = 'authentication';
    protected $dates = ['deleted_at'];

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
     * この社員が持っているorg_idと一致するレコードを地域テーブルから取得
     * withDefaultでNULL対策
     */
    public function organization()
    {
        return $this->belongsTo('App\Models\Master\Organization', 'org_id', 'id')->withDefault();

    }

    /**
     * この社員が持っているpost_idと一致するレコードを部署テーブルから取得
     * withDefaultでNULL対策
     */
    public function post()
    {
        return $this->belongsTo('App\Models\Master\Post', 'post_id', 'id')->withDefault();

    }
}
