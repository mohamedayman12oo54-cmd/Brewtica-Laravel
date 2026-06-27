<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendOrderConfirmationJob implements ShouldQueue
{
    use Queueable, Dispatchable, InteractsWithQueue, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private readonly User $user,
        private readonly Order $order
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        \Log::info("Order #{$this->order->id} confirmed for {$this->user->email}");
        // Mail::to($this->user->email)->send(new OrderConfirmationMail($this->order));
    }
}
