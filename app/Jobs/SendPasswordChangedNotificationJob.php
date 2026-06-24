<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendPasswordChangedNotificationJob implements ShouldQueue
{
    use Queueable, Dispatchable, InteractsWithQueue, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private readonly User $user
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Send Email
        // Mail::to($this->user->email)->send(new PasswordChangedMail($this->user));

        // Test
        \Log::info("Password changed notification sent to: {$this->user->email}");
    }
}
