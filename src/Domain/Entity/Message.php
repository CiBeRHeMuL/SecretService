<?php

namespace App\Domain\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table('message')]
#[ORM\Index(name: 'message_valid_until_idx', columns: ['valid_until'])]
class Message
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(UuidGenerator::class)]
    #[ORM\Column(type: 'uuid', nullable: false)]
    public Uuid $id {
        get => $this->id;
        set => $this->id = $value;
    }
    #[ORM\Column(name: 'text_hash', type: 'text', nullable: false)]
    public string $textHash {
        get => $this->textHash;
        set => $this->textHash = $value;
    }
    #[ORM\Column(name: 'created_at', type: 'datetime_immutable', nullable: false)]
    public DateTimeImmutable $createdAt {
        get => $this->createdAt;
        set => $this->createdAt = $value;
    }
    #[ORM\Column(name: 'valid_until', type: 'datetime_immutable', nullable: false)]
    public DateTimeImmutable $validUntil {
        get => $this->validUntil;
        set => $this->validUntil = $value;
    }
}
