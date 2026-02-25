<?php

namespace App\Listeners;

use App\Mail\WelcomeMail;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendWelcomeEmail implements ShouldQueue
{
    use InteractsWithQueue;

    public string $queue = 'emails';
    public int $tries    = 3;

    /**
     * Handles the built-in Registered event from Laravel Breeze.
     */
    public function handle(Registered $event): void
    {
        Mail::to($event->user->email)->send(new WelcomeMail($event->user));
    }
}
