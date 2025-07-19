<?php

namespace App\Presentation\Web\Twig\Components;

use DateInterval;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class DecreasingTimerAlert
{
    public string $whileType = 'success';
    public string $expiredType = 'danger';
    public string $whileMessage;
    public string $expiredMessage;
    public DateInterval $interval;
    public string $whileIcon {
        get => match ($this->whileType) {
            'success' => 'bi:check-circle',
            default => 'bi:exclamation-circle',
        };
    }
    public string $expiredIcon {
        get => match ($this->expiredType) {
            'success' => 'bi:check-circle',
            default => 'bi:exclamation-circle',
        };
    }
}
