<?php

namespace App\Entity;

use App\Repository\EventsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EventsRepository::class)]
class Events
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\Column]
    private ?int $AmazonAmount = null;

    #[ORM\Column]
    private ?int $NumberCodes = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $Description = null;

    #[ORM\ManyToMany(targetEntity: Users::class, inversedBy: 'events')]
    private Collection $Users;

    #[ORM\OneToMany(mappedBy: 'event', targetEntity: CodeAmazon::class)]
    private Collection $codesAmazon;

    #[ORM\OneToMany(mappedBy: 'event_id', targetEntity: HistoryLog::class)]
    private Collection $history;

    #[ORM\Column(length: 100)]
    private ?string $SMTPEmail = null;

    #[ORM\Column(length: 300)]
    private ?string $SMTPPassword = null;

    #[ORM\Column(length: 300)]
    private ?string $EmailHeader = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $EmailBody = null;

    #[ORM\Column(length: 300)]
    private ?string $EmailSubject = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $EmailAltBody = null;

    #[ORM\Column]
    private ?int $nbCodeGift = null;

    public function __construct()
    {
        $this->Users = new ArrayCollection();
        $this->codesAmazon = new ArrayCollection();
        $this->history = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getAmazonAmount(): ?int
    {
        return $this->AmazonAmount;
    }

    public function setAmazonAmount(int $AmazonAmount): self
    {
        $this->AmazonAmount = $AmazonAmount;

        return $this;
    }

    public function getNumberCodes(): ?int
    {
        return $this->NumberCodes;
    }

    public function setNumberCodes(int $NumberCodes): self
    {
        $this->NumberCodes = $NumberCodes;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->Description;
    }

    public function setDescription(string $Description): self
    {
        $this->Description = $Description;

        return $this;
    }

    /**
     * @return Collection<int, Users>
     */
    public function getUsers(): Collection
    {
        return $this->Users;
    }

    public function addUser(Users $user): self
    {
        if (!$this->Users->contains($user)) {
            $this->Users->add($user);
        }

        return $this;
    }

    public function removeUser(Users $user): self
    {
        $this->Users->removeElement($user);

        return $this;
    }

    /**
     * @return Collection<int, CodeAmazon>
     */
    public function getCodesAmazon(): Collection
    {
        return $this->codesAmazon;
    }

    public function addCodesAmazon(CodeAmazon $codesAmazon): self
    {
        if (!$this->codesAmazon->contains($codesAmazon)) {
            $this->codesAmazon->add($codesAmazon);
            $codesAmazon->setEvent($this);
        }

        return $this;
    }

    public function removeCodesAmazon(CodeAmazon $codesAmazon): self
    {
        if ($this->codesAmazon->removeElement($codesAmazon)) {
            // set the owning side to null (unless already changed)
            if ($codesAmazon->getEvent() === $this) {
                $codesAmazon->setEvent(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, HistoryLog>
     */
    public function getHistory(): Collection
    {
        return $this->history;
    }

    public function addHistory(HistoryLog $history): self
    {
        if (!$this->history->contains($history)) {
            $this->history->add($history);
            $history->setEventId($this);
        }

        return $this;
    }

    public function removeHistory(HistoryLog $history): self
    {
        if ($this->history->removeElement($history)) {
            // set the owning side to null (unless already changed)
            if ($history->getEventId() === $this) {
                $history->setEventId(null);
            }
        }

        return $this;
    }

    public function getSMTPEmail(): ?string
    {
        return $this->SMTPEmail;
    }

    public function setSMTPEmail(string $SMTPEmail): self
    {
        $this->SMTPEmail = $SMTPEmail;

        return $this;
    }

    public function getSMTPPassword(): ?string
    {
        return $this->SMTPPassword;
    }

    public function setSMTPPassword(string $SMTPPassword): self
    {
        $this->SMTPPassword = $SMTPPassword;

        return $this;
    }

    public function getEmailHeader(): ?string
    {
        return $this->EmailHeader;
    }

    public function setEmailHeader(string $EmailHeader): self
    {
        $this->EmailHeader = $EmailHeader;

        return $this;
    }

    public function getEmailBody(): ?string
    {
        return $this->EmailBody;
    }

    public function setEmailBody(string $EmailBody): self
    {
        $this->EmailBody = $EmailBody;

        return $this;
    }

    public function getEmailSubject(): ?string
    {
        return $this->EmailSubject;
    }

    public function setEmailSubject(string $EmailSubject): self
    {
        $this->EmailSubject = $EmailSubject;

        return $this;
    }

    public function getEmailAltBody(): ?string
    {
        return $this->EmailAltBody;
    }

    public function setEmailAltBody(string $EmailAltBody): self
    {
        $this->EmailAltBody = $EmailAltBody;

        return $this;
    }

    public function getNbCodeGift(): ?int
    {
        return $this->nbCodeGift;
    }

    public function setNbCodeGift(int $nbCodeGift): self
    {
        $this->nbCodeGift = $nbCodeGift;

        return $this;
    }
}
