<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventAttendee extends BaseModel
{
    public static $cacheKey = 'event_attendees';
    protected $fillable = [
        'event_id',
        'user_id'
    ];
    //
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id');
    }
    public function rules($id = null)
    {
        $id = $id ?? request()->route('id');

        return [
            'event_id' => 'required|exists:events,id',
            'user_id' => 'required|exists:users,id',
        ];
    }


}
