<?php

declare(strict_types=1);

namespace LiveVoting\Player\QR;

use Endroid\QrCode\QrCode;
use ilLiveVotingPlugin;
use srag\DIC\LiveVoting\DICTrait;

class xlvoQR
{
    use DICTrait;

    public const PLUGIN_CLASS_NAME = ilLiveVotingPlugin::class;

    public static function getImageDataString(string $content, int $size): string
    {
        $qrCodeLarge = new QrCode($content);
        $qrCodeLarge->setErrorCorrection('high');
        $qrCodeLarge->setForegroundColor(array(
            'r' => 0,
            'g' => 0,
            'b' => 0,
            'a' => 0,
        ));
        $qrCodeLarge->setBackgroundColor(array(
            'r' => 255,
            'g' => 255,
            'b' => 255,
            'a' => 0,
        ));
        $qrCodeLarge->setPadding(10);
        $qrCodeLarge->setSize($size);

        return $qrCodeLarge->getDataUri();
    }
}
