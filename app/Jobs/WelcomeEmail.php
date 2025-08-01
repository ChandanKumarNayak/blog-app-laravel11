<?php

namespace App\Jobs;

use App\Models\User;
use App\Mail\WelcomeEmailForUser;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class WelcomeEmail implements ShouldQueue
{
    use Queueable;
    protected $user;
    /**
     * Create a new job instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::to($this->user->email)->cc(["1998chandannayak@gmail.com"])->send(new WelcomeEmailForUser($this->user));
    }
}
