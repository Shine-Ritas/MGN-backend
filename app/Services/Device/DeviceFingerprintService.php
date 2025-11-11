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

use hisorange\BrowserDetect\Facade as Browser;
use Illuminate\Http\Request;

class DeviceFingerprintService
{
    /**
     * Generate a unique device fingerprint from request data.
     */
    public function generate(Request $request): string
    {
        $components = [
            'platform' => Browser::platformName() ?? 'Unknown',
            'browser' => Browser::browserFamily() ?? 'Unknown',
            'user_agent' => $request->userAgent() ?? '',
            'accept_language' => $request->header('Accept-Language', ''),
            'accept_encoding' => $request->header('Accept-Encoding', ''),
        ];

        // Create a hash from the components
        $fingerprintString = implode('|', $components);

        return hash('sha256', $fingerprintString);
    }

    /**
     * Get device display name for logging purposes.
     */
    public function getDeviceDisplayName(Request $request): string
    {
        $platform = Browser::platformName() ?? 'Unknown';
        $browser = Browser::browserFamily() ?? 'Unknown';

        return "{$platform} ( {$browser} )";
    }
}
