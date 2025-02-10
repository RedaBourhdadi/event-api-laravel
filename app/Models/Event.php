<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends BaseModel
{
    public static $cacheKey = 'events';

    protected $fillable = [
        'title', 
        'location', 
        'date',
        'max_attendees', 
        'user_id',
        
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function rules($id = null)
    {
        $id = $id ?? request()->route('id');

        return [
            'title' => 'required|string',
            'location' => 'required|string',
            'date' => 'required|date',
            'max_attendees' => 'required|integer',
            'user_id' => 'required|exists:users,id',
        ];
    }

    
}
