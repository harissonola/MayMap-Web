<?php

namespace App\Entity;

use App\Repository\ConversationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ConversationRepository::class)]
class Conversation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['conversation_list', 'conversation_details', 'message_details'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['conversation_list', 'conversation_details'])]
    private ?User $user1 = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['conversation_list', 'conversation_details'])]
    private ?User $user2 = null;

    #[ORM\OneToMany(targetEntity: Message::class, mappedBy: 'conversation', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Groups(['conversation_details', 'conversation_list'])]
    private Collection $messages;

    #[ORM\Column]
    #[Groups(['conversation_list', 'conversation_details'])]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    #[Groups(['conversation_list', 'conversation_details'])]
    private \DateTimeImmutable $updatedAt;

    #[ORM\Column(nullable: true)]
    #[Groups(['conversation_list'])]
    private ?string $lastMessageContent = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['conversation_list'])]
    private ?\DateTimeImmutable $lastMessageDate = null;

    public function __construct()
    {
        $this->messages = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->lastMessageDate = new \DateTimeImmutable();
        $this->lastMessageContent = '';


    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser1(): ?User
    {
        return $this->user1;
    }

    public function setUser1(?User $user1): self
    {
        $this->user1 = $user1;
        return $this;
    }

    public function getUser2(): ?User
    {
        return $this->user2;
    }

    public function setUser2(?User $user2): self
    {
        $this->user2 = $user2;
        return $this;
    }

    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(Message $message): self
    {
        if (!$this->messages->contains($message)) {
            $this->messages->add($message);
            $message->setConversation($this);
            $this->updateLastMessage($message);
        }
        return $this;
    }

    public function removeMessage(Message $message): self
    {
        if ($this->messages->removeElement($message)) {
            if ($message->getConversation() === $this) {
                $message->setConversation(null);
            }
        }
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getLastMessageContent(): ?string
    {
        return $this->lastMessageContent;
    }

    public function getLastMessageDate(): ?\DateTimeImmutable
    {
        return $this->lastMessageDate;
    }

    public function updateLastMessage(?Message $message): void
    {
            $this->lastMessageContent = $message->getContent();
            $this->lastMessageDate = $message->getCreatedAt();
            $this->updatedAt = new \DateTimeImmutable();
    }

    public function getOtherUser(User $currentUser): ?User
    {
        if ($currentUser->getId() === $this->user1->getId()) {
            return $this->user2;
        } elseif ($currentUser->getId() === $this->user2->getId()) {
            return $this->user1;
        }
        return null;
    }

    public function hasUser(User $user): bool
    {
        return $this->user1->getId() === $user->getId() || $this->user2->getId() === $user->getId();
    }
}