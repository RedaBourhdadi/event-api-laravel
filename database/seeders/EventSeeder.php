<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::where('email', 'user@cysc.fr')->first();
        
        $events = [
            [
                'title' => 'Team Building Workshop',
                'location' => 'Conference Room A',
                'date' => Carbon::now()->addDays(7),
                'max_attendees' => 20,
                'user_id' => $user->id,
            ],
            [
                'title' => 'Tech Talk: AI Innovation',
                'location' => 'Virtual Meeting',
                'date' => Carbon::now()->addDays(14),
                'max_attendees' => 50,
                'user_id' => $user->id,
            ],
            [
                'title' => 'Project Planning Session',
                'location' => 'Meeting Room 101',
                'date' => Carbon::now()->addDays(21),
                'max_attendees' => 15,
                'user_id' => $user->id,
            ],
        ];

        foreach ($events as $event) {
            Event::firstOrCreate(
                ['title' => $event['title']],
                $event
            );
        }
    }
} 