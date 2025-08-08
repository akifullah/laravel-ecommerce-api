<?php

namespace App\Listeners;

use App\Events\UserLoggedIn;
use App\Models\UserLogin;
use Jenssegers\Agent\Agent;

class LogUserLogin
{
    public function handle(UserLoggedIn $event): void
    {
        $agent = new Agent();
        $agent->setUserAgent($event->userAgent);

        UserLogin::create([
            'user_id' => $event->user->id,
            'ip_address' => $event->ip,
            'user_agent' => $event->userAgent,
            'device' => $agent->device(),
            'platform' => $agent->platform(),
            'browser' => $agent->browser(),
        ]);
    }
}
