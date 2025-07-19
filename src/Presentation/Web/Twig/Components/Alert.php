<?php

namespace App\Presentation\Web\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class Alert
{
    public string $type = 'success';
    public string $message;
    public string $icon {
        get => match ($this->type) {
            'success' => 'bi:check-circle',
            default => 'bi:exclamation-circle',
        };
    }
}
