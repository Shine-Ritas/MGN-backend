<?php

/**
 * Project: MGN-Backend
 * Owner: @Htet_Shine
 * Email: whoishsh@gmail.com
 *
 * This file is part of the proprietary source code owned by @Htet_Shine.
 * Unauthorized copying, distribution, or modification is prohibited.
 */

namespace App\Services\Device;

use App\Models\LoginHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DeviceLimitService
{
    protected const MAX_DEVICES = 3;

    /**
     * Check if user has reached device limit and handle auto-logout if needed.
     */
    public function enforceDeviceLimit(User $user, string $deviceFingerprint, string $guard = 'web'): void
    {
        // Get active devices for this user
        $activeDevices = $this->getActiveDevices($user);

        // Check if current device is already active
        $isCurrentDeviceActive = $activeDevices->contains('device_fingerprint', $deviceFingerprint);

        // If current device is not active and user has reached limit, logout oldest device
        if (! $isCurrentDeviceActive && $activeDevices->count() >= self::MAX_DEVICES) {
            $this->logoutOldestDevice($user, $guard);
        }

        // Mark current device as active
        $this->activateDevice($user, $deviceFingerprint);
    }

    /**
     * Get active devices for a user.
     *
     * @return \Illuminate\Support\Collection<int,LoginHistory>
     */
    public function getActiveDevices(User $user): \Illuminate\Support\Collection
    {
        return LoginHistory::where('user_id', $user->id)
            ->where('is_active', true)
            ->whereNotNull('device_fingerprint')
            ->select('device_fingerprint', 'device', 'login_at')
            ->orderBy('login_at', 'desc')
            ->get()
            ->unique('device_fingerprint');
    }

    /**
     * Get active device count for a user.
     */
    public function getActiveDeviceCount(User $user): int
    {
        return $this->getActiveDevices($user)->count();
    }

    /**
     * Logout the oldest active device.
     */
    protected function logoutOldestDevice(User $user, string $guard): void
    {
        // Get the oldest active device
        $oldestDevice = LoginHistory::where('user_id', $user->id)
            ->where('is_active', true)
            ->whereNotNull('device_fingerprint')
            ->orderBy('login_at', 'asc')
            ->first();

        if (! $oldestDevice) {
            return;
        }

        $deviceFingerprint = $oldestDevice->device_fingerprint;

        // Deactivate the device in login history
        LoginHistory::where('user_id', $user->id)
            ->where('device_fingerprint', $deviceFingerprint)
            ->update(['is_active' => false]);

        // Logout from sessions (web)
        if ($guard === 'web') {
            DB::table('sessions')
                ->where('user_id', $user->id)
                ->where('device_fingerprint', $deviceFingerprint)
                ->delete();
        }

        // Logout from tokens (API)
        $user->tokens()
            ->where('device_fingerprint', $deviceFingerprint)
            ->delete();
    }

    /**
     * Activate a device for a user.
     */
    protected function activateDevice(User $user, string $deviceFingerprint): void
    {
        // Deactivate all other logins from this device (if any)
        LoginHistory::where('user_id', $user->id)
            ->where('device_fingerprint', $deviceFingerprint)
            ->update(['is_active' => false]);

        // Activate the most recent login from this device
        $latestLogin = LoginHistory::where('user_id', $user->id)
            ->where('device_fingerprint', $deviceFingerprint)
            ->orderBy('login_at', 'desc')
            ->first();

        if ($latestLogin) {
            $latestLogin->update(['is_active' => true]);
        }
    }

    /**
     * Deactivate a device for a user (on logout).
     */
    public function deactivateDevice(User $user, string $deviceFingerprint): void
    {
        LoginHistory::where('user_id', $user->id)
            ->where('device_fingerprint', $deviceFingerprint)
            ->update(['is_active' => false]);
    }
}
