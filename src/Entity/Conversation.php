<?php

namespace App\Entity;

use App\Repository\ConversationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ConversationRepository::class)]
class Conversation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $sujet = null;

    #[ORM\OneToMany(mappedBy: 'conversation', targetEntity: Messagerie::class)]
    private Collection $messages;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\ManyToOne(inversedBy: 'conversations')]
    private ?User $annonceur = null;

    #[ORM\ManyToOne(inversedBy: 'conversations')]
    private ?User $chercheur = null;

    #[ORM\ManyToOne(inversedBy: 'conversations')]
    private ?Jeux $jeux = null;

    public function __construct()
    {
        $this->messages = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSujet(): ?string
    {
        return $this->sujet;
    }

    public function setSujet(string $sujet): static
    {
        $this->sujet = $sujet;

        return $this;
    }

    /**
     * @return Collection<int, Messagerie>
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(Messagerie $message): static
    {
        if (!$this->messages->contains($message)) {
            $this->messages->add($message);
            $message->setConversation($this);
        }

        return $this;
    }

    public function removeMessage(Messagerie $message): static
    {
        if ($this->messages->removeElement($message)) {
            // set the owning side to null (unless already changed)
            if ($message->getConversation() === $this) {
                $message->setConversation(null);
            }
        }

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getAnnonceur(): ?User
    {
        return $this->annonceur;
    }

    public function setAnnonceur(?User $annonceur): static
    {
        $this->annonceur = $annonceur;

        return $this;
    }

    public function getChercheur(): ?User
    {
        return $this->chercheur;
    }

    public function setChercheur(?User $chercheur): static
    {
        $this->chercheur = $chercheur;

        return $this;
    }

    public function getJeux(): ?Jeux
    {
        return $this->jeux;
    }

    public function setJeux(?Jeux $jeux): static
    {
        $this->jeux = $jeux;

        return $this;
    }
}
