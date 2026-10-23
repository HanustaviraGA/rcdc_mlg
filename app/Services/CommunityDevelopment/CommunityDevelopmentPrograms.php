<?php

namespace App\Services\CommunityDevelopment;

use Illuminate\Support\Str;

class CommunityDevelopmentPrograms
{
    public static function key(string $name): string
    {
        $normalized = strtolower(preg_replace('/[^a-z0-9]/i', '', preg_replace('/^prodi\s+/i', '', trim($name))));

        return match ($normalized) {
            'binusmalang', 'binusmalangtotal', 'total', 'grandtotal' => '_total',
            'ebc', 'entrepreneurship', 'entrepreneurshipbusinesscreation' => 'ebc',
            'cs', 'computerscience', 'informatics', 'teknikinformatika' => 'cs',
            'pr', 'publicrelations' => 'pr',
            'ilkom', 'digicomm', 'digicom', 'communicationscience', 'digitalcommunication', 'ilmukomunikasi' => 'ilkom',
            'di', 'dp', 'pjj', 'interiordesign', 'desaininterior' => 'di',
            'dkv', 'visualcommunicationdesign', 'desainkomunikasivisual' => 'dkv',
            'cbdc', 'characterbuilding', 'characterbuildingdevelopmentcenter' => 'cbdc',
            'dllc', 'lc', 'languagecenter', 'englishliterature', 'chineseliterature', 'sastrainggris', 'sastrachina' => 'dllc',
            'psychology', 'psikologi' => 'psychology',
            default => 'prodi-'.Str::limit(Str::slug($name), 120, '').'-'.substr(hash('sha256', $normalized), 0, 8),
        };
    }

    public static function label(string $key, string $fallback): string
    {
        return match ($key) {
            'ebc' => 'Entrepreneurship', 'cs' => 'Computer Science', 'pr' => 'Public Relations',
            'ilkom' => 'Digital Communication', 'di' => 'Interior Design', 'dkv' => 'Visual Communication Design',
            'cbdc' => 'Character Building', 'dllc' => 'Language Center', 'psychology' => 'Psychology',
            default => $fallback,
        };
    }
}
