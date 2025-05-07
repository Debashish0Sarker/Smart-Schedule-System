<?php


namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class NotificationSettingsSeeder extends Seeder
{
    public function run()
    {
        User::where('is_admin', false)->each(function($user){
            $user->notificationSettings()->createMany([
                [
                  'type'         => 'assignment_due',
                  'enabled'      => true,
                  'frequency'    => 'once',
                  'before_hours' => 24,
                ],
                [
                  'type'         => 'new_registration',
                  'enabled'      => true,
                  'frequency'    => 'once',
                  'before_hours' => 0,
                ],
            ]);
        });
    }
}
