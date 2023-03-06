<?php

namespace App\Entity;

use App\Repository\HistoryLogRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HistoryLogRepository::class)]
class HistoryLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Users $email_id = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?CodeAmazon $amazon_code_id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $datetime = null;

    #[ORM\ManyToOne(inversedBy: 'history')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Events $event_id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmailId(): ?Users
    {
        return $this->email_id;
    }

    public function setEmailId(?Users $email_id): self
    {
        $this->email_id = $email_id;

        return $this;
    }

    public function getAmazonCodeId(): ?CodeAmazon
    {
        return $this->amazon_code_id;
    }

    public function setAmazonCodeId(CodeAmazon $amazon_code_id): self
    {
        $this->amazon_code_id = $amazon_code_id;

        return $this;
    }

    public function getDatetime(): ?\DateTimeInterface
    {
        return $this->datetime;
    }

    public function setDatetime(\DateTimeInterface $datetime): self
    {
        $this->datetime = $datetime;

        return $this;
    }

    public function getEventId(): ?Events
    {
        return $this->event_id;
    }

    public function setEventId(?Events $event_id): self
    {
        $this->event_id = $event_id;

        return $this;
    }
}
