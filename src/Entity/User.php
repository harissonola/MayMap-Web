<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user_profile', 'establishment_public', 'message_list', 'message_details', 'user:list'])]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Groups(['user_profile', 'message_list', 'message_details', 'user:list'])]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    #[Groups(['user_profile', 'establishment_public', 'message_list', 'message_details', 'user:list'])]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    #[Groups(['user_profile', 'establishment_public', 'message_list', 'message_details', 'user:list'])]
    private ?string $username = null;

    #[ORM\Column(length: 255)]
    #[Groups(['user_profile', 'establishment_public', 'message_list', 'message_details', 'user:list'])]
    private ?string $avatar = null;

    #[ORM\Column(length: 255)]
    #[Groups(['user_profile', 'establishment_public', 'message_list', 'message_details', 'user:list'])]
    private ?string $fname = null;

    #[ORM\Column(length: 255)]
    #[Groups(['user_profile', 'establishment_public', 'message_list', 'message_details', 'user:list'])]
    private ?string $lname = null;

    #[ORM\Column(length: 5, options: ['default' => 'fr'])]
    #[Assert\Choice(choices: ['fr', 'en', 'es'], message: 'Langue non supportée')]
    #[Groups(['user_profile', 'user:list'])]
    private string $language = 'fr';

    #[ORM\Column]
    #[Groups(['user_profile', 'establishment_public', 'user:list'])]
    private ?bool $isVerified = null;

    #[ORM\Column]
    #[Groups(['user_profile', 'establishment_public', 'user:list'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * @var Collection<int, Establishment>
     */
    #[ORM\ManyToMany(targetEntity: Establishment::class, inversedBy: 'followers')]
    #[Groups(['user_profile', 'establishment_public'])]
    private Collection $favorites;

    #[ORM\OneToOne(targetEntity: Establishment::class, mappedBy: 'owner')]
    #[Groups(['user_profile'])]
    private ?Establishment $establishment = null;

    /**
     * @var Collection<int, Rating>
     */
    #[ORM\OneToMany(targetEntity: Rating::class, mappedBy: 'user')]
    #[Groups(['user_profile'])]
    private Collection $ratings;

    /**
     * @var Collection<int, PostLike>
     */
    #[ORM\OneToMany(targetEntity: PostLike::class, mappedBy: 'user')]
    private Collection $postLikes;

    /**
     * @var Collection<int, PostComment>
     */
    #[ORM\OneToMany(targetEntity: PostComment::class, mappedBy: 'user')]
    private Collection $postComments;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $verificationCode = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $verificationCodeExpiresAt = null;

    /**
     * @var Collection<int, Post>
     */
    #[ORM\OneToMany(targetEntity: Post::class, mappedBy: 'user')]
    private Collection $posts;

    /**
     * @var Collection<int, Conversation>
     */
    #[ORM\OneToMany(targetEntity: Conversation::class, mappedBy: 'user1')]
    private Collection $conversationsAsUser1;

    /**
     * @var Collection<int, Conversation>
     */
    #[ORM\OneToMany(targetEntity: Conversation::class, mappedBy: 'user2')]
    private Collection $conversationsAsUser2;

    public function __construct()
    {
        $this->favorites = new ArrayCollection();
        $this->ratings = new ArrayCollection();
        $this->postLikes = new ArrayCollection();
        $this->postComments = new ArrayCollection();
        $this->posts = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->isVerified = false;
        $this->avatar = 'default.jpg';
        $this->conversationsAsUser1 = new ArrayCollection();
        $this->conversationsAsUser2 = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getConversations(): array
    {
        return array_merge(
            $this->conversationsAsUser1->toArray(),
            $this->conversationsAsUser2->toArray()
        );
    }

    public function getConversationsAsUser1(): Collection
    {
        return $this->conversationsAsUser1;
    }

    public function getConversationsAsUser2(): Collection
    {
        return $this->conversationsAsUser2;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function eraseCredentials(): void {}

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(?string $avatar): static
    {
        $this->avatar = $avatar;
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function getFname(): ?string
    {
        return $this->fname;
    }

    public function setFname(?string $fname): static
    {
        $this->fname = $fname;
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function getLname(): ?string
    {
        return $this->lname;
    }

    public function setLname(?string $lname): static
    {
        $this->lname = $lname;
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function getFullName(): string
    {
        return trim($this->fname . ' ' . $this->lname);
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function setLanguage(string $language): static
    {
        $supportedLanguages = ['fr', 'en', 'es'];
        if (!in_array($language, $supportedLanguages)) {
            throw new \InvalidArgumentException('Langue non supportée: ' . $language);
        }

        $this->language = $language;
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function getLanguageName(): string
    {
        return match ($this->language) {
            'fr' => 'Français',
            'en' => 'English',
            'es' => 'Español',
            default => 'Français'
        };
    }

    public function isVerified(): ?bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getVerificationCode(): ?string
    {
        return $this->verificationCode;
    }

    public function setVerificationCode(?string $verificationCode): self
    {
        $this->verificationCode = $verificationCode;
        $this->verificationCodeExpiresAt = (new \DateTime())->add(new \DateInterval('PT1H'));
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function getVerificationCodeExpiresAt(): ?\DateTimeInterface
    {
        return $this->verificationCodeExpiresAt;
    }

    public function setVerificationCodeExpiresAt(?\DateTimeInterface $verificationCodeExpiresAt): self
    {
        $this->verificationCodeExpiresAt = $verificationCodeExpiresAt;
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function getFavorites(): Collection
    {
        return $this->favorites;
    }

    public function addFavorite(Establishment $favorite): static
    {
        if (!$this->favorites->contains($favorite)) {
            $this->favorites->add($favorite);
            $this->updatedAt = new \DateTimeImmutable();
        }
        return $this;
    }

    public function removeFavorite(Establishment $favorite): static
    {
        if ($this->favorites->removeElement($favorite)) {
            $this->updatedAt = new \DateTimeImmutable();
        }
        return $this;
    }

    public function getEstablishment(): ?Establishment
    {
        return $this->establishment;
    }

    public function setEstablishment(?Establishment $establishment): static
    {
        if ($this->establishment !== null && $this->establishment !== $establishment) {
            $this->establishment->setOwner(null);
        }
        $this->establishment = $establishment;
        if ($establishment !== null) {
            $establishment->setOwner($this);
        }
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function getRatings(): Collection
    {
        return $this->ratings;
    }

    public function addRating(Rating $rating): static
    {
        if (!$this->ratings->contains($rating)) {
            $this->ratings->add($rating);
            $rating->setUser($this);
            $this->updatedAt = new \DateTimeImmutable();
        }
        return $this;
    }

    public function removeRating(Rating $rating): static
    {
        if ($this->ratings->removeElement($rating)) {
            if ($rating->getUser() === $this) {
                $rating->setUser(null);
            }
            $this->updatedAt = new \DateTimeImmutable();
        }
        return $this;
    }



    public function addPostLike(PostLike $postLike): static
    {
        if (!$this->postLikes->contains($postLike)) {
            $this->postLikes->add($postLike);
            $postLike->setUser($this);
            $this->updatedAt = new \DateTimeImmutable();
        }
        return $this;
    }

    public function removePostLike(PostLike $postLike): static
    {
        if ($this->postLikes->removeElement($postLike)) {
            if ($postLike->getUser() === $this) {
                $postLike->setUser(null);
            }
            $this->updatedAt = new \DateTimeImmutable();
        }
        return $this;
    }

    public function getPostComments(): Collection
    {
        return $this->postComments;
    }

    public function addPostComment(PostComment $postComment): static
    {
        if (!$this->postComments->contains($postComment)) {
            $this->postComments->add($postComment);
            $postComment->setUser($this);
            $this->updatedAt = new \DateTimeImmutable();
        }
        return $this;
    }

    public function removePostComment(PostComment $postComment): static
    {
        if ($this->postComments->removeElement($postComment)) {
            if ($postComment->getUser() === $this) {
                $postComment->setUser(null);
            }
            $this->updatedAt = new \DateTimeImmutable();
        }
        return $this;
    }

    /**
     * @return Collection<int, Post>
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function addPost(Post $post): static
    {
        if (!$this->posts->contains($post)) {
            $this->posts->add($post);
            $post->setUser($this);
        }
        return $this;
    }

    public function removePost(Post $post): static
    {
        if ($this->posts->removeElement($post)) {
            if ($post->getUser() === $this) {
                $post->setUser(null);
            }
        }
        return $this;
    }
}
