<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends BaseModel
{
    //
    public static $cacheKey = 'notifications';

    protected $fillable = [
        'user_id',
        'title',
        'message',
        'type',
        'data',
        'read_at'
    ];
    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime'
    ];

    public function rules($id = null)
    {
        return [
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'required|in:info,success,warning,error',
            'data' => 'nullable|array',
            'read_at' => 'nullable|date'
        ];
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
