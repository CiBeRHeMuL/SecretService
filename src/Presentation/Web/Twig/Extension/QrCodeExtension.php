<?php

namespace App\Presentation\Web\Twig\Extension;

use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Color\ColorInterface;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\SvgWriter;
use Twig\Attribute\AsTwigFunction;

class QrCodeExtension
{
    #[AsTwigFunction('svg_qrcode')]
    public function svgQrcode(
        string $data,
        Encoding $encoding = new Encoding('UTF-8'),
        ErrorCorrectionLevel $errorCorrectionLevel = ErrorCorrectionLevel::Low,
        int $size = 300,
        int $margin = 10,
        RoundBlockSizeMode $roundBlockSizeMode = RoundBlockSizeMode::Margin,
        ColorInterface $foregroundColor = new Color(0, 0, 0),
        ColorInterface $backgroundColor = new Color(255, 255, 255),
    ): string {
        $writer = new SvgWriter();

        $result = $writer->write(
            new QrCode(
                $data,
                $encoding,
                $errorCorrectionLevel,
                $size,
                $margin,
                $roundBlockSizeMode,
                $foregroundColor,
                $backgroundColor,
            ),
        );

        return $result->getString();
    }
}
