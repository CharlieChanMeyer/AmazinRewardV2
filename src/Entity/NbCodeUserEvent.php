<?php

namespace App\Entity;

use App\Repository\NbCodeUserEventRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NbCodeUserEventRepository::class)]
class NbCodeUserEvent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Users $User = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Events $Event = null;

    #[ORM\Column(options : ["default" => 1])]
    private ?int $nbCode = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?Users
    {
        return $this->User;
    }

    public function setUser(?Users $User): self
    {
        $this->User = $User;

        return $this;
    }

    public function getEvent(): ?Events
    {
        return $this->Event;
    }

    public function setEvent(?Events $Event): self
    {
        $this->Event = $Event;

        return $this;
    }

    public function getNbCode(): ?int
    {
        return $this->nbCode;
    }

    public function setNbCode(int $nbCode): self
    {
        $this->nbCode = $nbCode;

        return $this;
    }
}
