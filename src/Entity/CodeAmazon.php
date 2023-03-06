<?php

namespace App\Entity;

use App\Repository\CodeAmazonRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CodeAmazonRepository::class)]
class CodeAmazon
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 300)]
    private ?string $amazon_code = null;

    #[ORM\Column]
    private ?int $used = null;

    #[ORM\ManyToOne(inversedBy: 'codesAmazon')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Events $event = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAmazonCode(): ?string
    {
        return $this->amazon_code;
    }

    public function setAmazonCode(string $amazon_code): self
    {
        $this->amazon_code = $amazon_code;

        return $this;
    }

    public function getUsed(): ?int
    {
        return $this->used;
    }

    public function setUsed(int $used): self
    {
        $this->used = $used;

        return $this;
    }

    public function getEvent(): ?Events
    {
        return $this->event;
    }

    public function setEvent(?Events $event): self
    {
        $this->event = $event;

        return $this;
    }
}
