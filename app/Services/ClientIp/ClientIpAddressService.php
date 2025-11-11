<?php

namespace App\Services\ClientIp;

use App\Models\LoginHistory;
use App\Models\User;
use App\Services\Device\DeviceFingerprintService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stevebauman\Location\Facades\Location;
use Stevebauman\Location\Position;

class ClientIpAddressService
{
    public function __construct(
        protected DeviceFingerprintService $deviceFingerprintService
    ) {}

    public function getClientInfo(string $ip): bool|Position
    {
        Log::info('ClientIpAddressService R1', ['client_ip' => $ip]);

        return Location::get($ip);
    }

    public function saveRecord(User $user, string $ip, ?string $deviceFingerprint = null, ?string $deviceDisplayName = null): bool
    {
        if ($user == null) {
            return false;
        }

        $location = $this->getClientInfo($ip);

        // Use provided device display name, or generate from current request
        $device = $deviceDisplayName ?? $this->deviceFingerprintService->getDeviceDisplayName(request());

        $country = $location instanceof Position ? $location->countryName : 'Unknown';
        $region = $location instanceof Position ? $location->regionName : 'Unknown';
        $locationString = "{$country}/{$region}";

        LoginHistory::create([
            'user_id' => $user->id,
            'location' => $locationString,
            'country' => $country,
            'device' => $device,
            'device_fingerprint' => $deviceFingerprint,
            'is_active' => true,
            'login_at' => now(),
        ]);

        $user->last_login_at = now();
        $user->save();

        return true;
    }
}
