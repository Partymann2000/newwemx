<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    public $incrementing = false;

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'last_activity' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function operatingSystem(): string
    {
        $userAgent = $this->user_agent;
        $osArray = [
            '/windows nt 10.0; Win64; x64/i' => 'Windows 11',
            '/windows nt 10/i'      => 'Windows 10',
            '/windows nt 6.3/i'     => 'Windows 8.1',
            '/windows nt 6.2/i'     => 'Windows 8',
            '/windows nt 6.1/i'     => 'Windows 7',
            '/windows nt 6.0/i'     => 'Windows Vista',
            '/windows nt 5.1/i'     => 'Windows XP',
            '/macintosh|mac os x/i' => 'Mac OS X',
            '/mac_powerpc/i'        => 'Mac OS 9',
            '/linux/i'              => 'Linux',
            '/ubuntu/i'             => 'Ubuntu',
            '/iphone/i'             => 'iPhone',
            '/ipad/i'               => 'iPad',
            '/android/i'            => 'Android',
            '/blackberry/i'         => 'BlackBerry',
            '/webos/i'              => 'Mobile',
        ];

        foreach ($osArray as $regex => $value) {
            if (preg_match($regex, $userAgent)) {
                return $value;
            }
        }

        return 'Unknown OS';
    }

    public function getDeviceFromUserAgent(): string
    {
        $userAgent = $this->user_agent;

        $deviceArray = [
            '/iphone/i'     => 'iPhone',
            '/ipad/i'       => 'iPad',
            '/ipod/i'       => 'iPod',
            '/android/i'    => 'Android',
            '/blackberry/i' => 'BlackBerry',
            '/windows phone/i' => 'Windows Phone',
            '/mobile/i'     => 'Mobile',
            '/tablet/i'     => 'Tablet',
        ];

        foreach ($deviceArray as $regex => $value) {
            if (preg_match($regex, $userAgent)) {
                return $value;
            }
        }

        // Default classification
        if (preg_match('/windows|macintosh|linux/i', $userAgent)) {
            return 'Desktop';
        }

        return 'Unknown Device';
    }

    public function browser(): string
    {
        $userAgent = $this->user_agent;
        $browserArray = [
            '/msie/i'       => 'Internet Explorer',
            '/firefox/i'    => 'Firefox',
            '/chrome/i'     => 'Chrome',
            '/safari/i'     => 'Safari',
            '/edge/i'       => 'Edge',
            '/opera/i'      => 'Opera',
            '/netscape/i'   => 'Netscape',
            '/maxthon/i'    => 'Maxthon',
            '/konqueror/i'  => 'Konqueror',
            '/mobile/i'     => 'Handheld Browser',
        ];

        foreach ($browserArray as $regex => $value) {
            if (preg_match($regex, $userAgent)) {
                return $value;
            }
        }

        return 'Unknown Browser';
    }

    public function isDesktopDevice(): bool
    {
        $device = $this->getDeviceFromUserAgent();
        return $device === 'Desktop';
    }
}
