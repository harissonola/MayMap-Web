<?php

namespace App\Entity;

use App\Repository\PostRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation\Translatable;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PostRepository::class)]
class Post
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user_profile', 'establishment_public'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['user_profile', 'establishment_public'])]
    #[Translatable()]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['user_profile', 'establishment_public'])]
    #[Translatable()]
    private ?string $content = null;

    #[ORM\Column]
    #[Groups(['user_profile', 'establishment_public'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'posts')]
    #[Groups(['user_profile'])]
    private ?Establishment $establishment = null;

    #[ORM\ManyToOne(inversedBy: 'posts')]
    #[Groups(['user_profile', 'establishment_public'])]
    private ?User $user = null;

    #[ORM\Column]
    #[Groups(['user_profile', 'establishment_public'])]
    private bool $isUserPost = false;

    #[ORM\OneToMany(targetEntity: PostImage::class, mappedBy: 'post')]
    #[Groups(['user_profile', 'establishment_public'])]
    private Collection $images;

    #[ORM\OneToMany(targetEntity: PostLike::class, mappedBy: 'post')]
    #[Groups(['user_profile', 'establishment_public'])]
    private Collection $likes;

    #[ORM\OneToMany(targetEntity: PostComment::class, mappedBy: 'post')]
    #[Groups(['user_profile', 'establishment_public'])]
    private Collection $comments;

    public function __construct()
    {
        $this->images = new ArrayCollection();
        $this->likes = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;
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

    public function getEstablishment(): ?Establishment
    {
        return $this->establishment;
    }

    public function setEstablishment(?Establishment $establishment): static
    {
        $this->establishment = $establishment;
        if ($establishment !== null) {
            $this->setIsUserPost(false);
        }
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        if ($user !== null) {
            $this->setIsUserPost(true);
        }
        return $this;
    }

    public function isUserPost(): bool
    {
        return $this->isUserPost;
    }

    public function setIsUserPost(bool $isUserPost): static
    {
        $this->isUserPost = $isUserPost;
        return $this;
    }

    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(PostImage $image): static
    {
        if (!$this->images->contains($image)) {
            $this->images->add($image);
            $image->setPost($this);
        }
        return $this;
    }

    public function removeImage(PostImage $image): static
    {
        if ($this->images->removeElement($image)) {
            if ($image->getPost() === $this) {
                $image->setPost(null);
            }
        }
        return $this;
    }

    public function getLikes(): Collection
    {
        return $this->likes;
    }

    public function addLike(PostLike $like): static
    {
        if (!$this->likes->contains($like)) {
            $this->likes->add($like);
            $like->setPost($this);
        }
        return $this;
    }

    public function removeLike(PostLike $like): static
    {
        if ($this->likes->removeElement($like)) {
            if ($like->getPost() === $this) {
                $like->setPost(null);
            }
        }
        return $this;
    }

    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(PostComment $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setPost($this);
        }
        return $this;
    }

    public function removeComment(PostComment $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            if ($comment->getPost() === $this) {
                $comment->setPost(null);
            }
        }
        return $this;
    }
}