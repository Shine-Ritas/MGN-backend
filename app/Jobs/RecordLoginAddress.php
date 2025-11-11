<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\ClientIp\ClientIpAddressService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RecordLoginAddress implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 60;

    public User $user;

    public string $ip;

    public ?string $deviceFingerprint;

    public ?string $deviceDisplayName;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user, string $ip, ?string $deviceFingerprint = null, ?string $deviceDisplayName = null)
    {
        $this->user = $user;
        $this->ip = $ip;
        $this->deviceFingerprint = $deviceFingerprint;
        $this->deviceDisplayName = $deviceDisplayName;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $clientIp = app(ClientIpAddressService::class);

        $clientIp->saveRecord($this->user, $this->ip, $this->deviceFingerprint, $this->deviceDisplayName);
    }
}
