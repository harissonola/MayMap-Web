<?php

namespace App\Entity;

use App\Repository\EstablishmentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation\Translatable;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: EstablishmentRepository::class)]
class Establishment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user_profile', 'establishment_manage', 'establishment_public', 'establishment:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['user_profile', 'establishment_manage', 'establishment_public', 'establishment:read'])]
    #[Translatable()]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['user_profile','establishment_manage', 'establishment_public'])]
    private ?string $slug = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['user_profile','establishment_manage', 'establishment_public', 'establishment:read'])]
    #[Translatable()]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    #[Groups(['user_profile','establishment_manage', 'establishment_public', 'establishment:read'])]
    private ?string $address = null;

    #[ORM\Column(length: 255)]
    #[Groups(['user_profile','establishment_manage', 'establishment_public', 'establishment:read'])]
    private ?string $location = null;

    #[ORM\Column]
    #[Groups(['user_profile','establishment_manage', 'establishment_public', 'establishment:read'])]
    private ?bool $isVerified = null;

    #[ORM\Column]
    #[Groups(['user_profile','establishment_manage', 'establishment_public'])]
    private ?bool $isPremium = null;

    #[ORM\Column]
    #[Groups(['user_profile','establishment_manage', 'establishment_public'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'establishments')]
    private ?User $user = null;

   #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'favorites')]
   #[Groups(['user_profile', 'establishment_public'])]
   private Collection $followers;

    #[ORM\OneToOne(inversedBy: 'establishment')]
    #[Groups(['establishment_manage', 'user_profile'])] // Limitez les groupes
    private ?User $owner = null;

    #[ORM\ManyToOne(inversedBy: 'establishments')]
    #[Groups(['establishment_manage', 'user_profile', 'establishment_public', 'establishment:read'])]
    private ?TypeEstablishment $type = null;

    #[ORM\OneToMany(targetEntity: Rating::class, mappedBy: 'establishment')]
    #[Groups(['establishment_manage', ])]
    private Collection $ratings;

    #[ORM\OneToMany(targetEntity: Post::class, mappedBy: 'establishment')]
    #[Groups(['establishment_manage', 'user_profile', 'establishment_public'])]
    private Collection $posts;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[Groups(['establishment_manage', 'user_profile', 'establishment_public'])]
    private ?Subscription $subscription = null;

    #[ORM\OneToMany(targetEntity: EstablishmentImage::class, mappedBy: 'establishment')]
   #[Groups(['establishment_manage', 'user_profile', 'establishment_public', 'establishment:read'])]
    private Collection $images;

    #[ORM\OneToMany(mappedBy: 'etablissement', targetEntity: Horaire::class, cascade: ['persist', 'remove'])]
    #[Groups(['establishment_manage', 'user_profile', 'establishment_public'])]
    private Collection $horaires;

    #[ORM\Column(length: 255)]
    #[Groups(['establishment_manage', 'establishment_public', 'user_profile'])]
    private ?string $telephone = null;

    public function __construct()
    {
        $this->followers = new ArrayCollection();
        $this->ratings = new ArrayCollection();
        $this->messages = new ArrayCollection();
        $this->posts = new ArrayCollection();
        $this->images = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->isVerified = false;
        $this->isPremium = false;
        $this->horaires = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): static
    {
        $this->telephone = $telephone;
        return $this;
    }

    public function getHoraires(): Collection
    {
        return $this->horaires;
    }

    public function addHoraire(Horaire $horaire): static
    {
        if (!$this->horaires->contains($horaire)) {
            $this->horaires->add($horaire);
            $horaire->setEtablissement($this);
        }
        return $this;
    }

    public function removeHoraire(Horaire $horaire): static
    {
        if ($this->horaires->removeElement($horaire)) {
            if ($horaire->getEtablissement() === $this) {
                $horaire->setEtablissement(null);
            }
        }
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): static
    {
        $this->slug = $slug;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;
        return $this;
    }

    public function isVerified(): ?bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;
        return $this;
    }

    public function isPremium(): ?bool
    {
        return $this->isPremium;
    }

    public function setIsPremium(bool $isPremium): static
    {
        $this->isPremium = $isPremium;
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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getFollowers(): Collection
    {
        return $this->followers;
    }

    public function addFollower(User $follower): static
    {
        if (!$this->followers->contains($follower)) {
            $this->followers->add($follower);
            $follower->addFavorite($this);
        }
        return $this;
    }

    public function removeFollower(User $follower): static
    {
        if ($this->followers->removeElement($follower)) {
            $follower->removeFavorite($this);
        }
        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;
        return $this;
    }

    public function getType(): ?TypeEstablishment
    {
        return $this->type;
    }

    public function setType(?TypeEstablishment $type): static
    {
        $this->type = $type;
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
            $rating->setEstablishment($this);
        }
        return $this;
    }

    public function removeRating(Rating $rating): static
    {
        if ($this->ratings->removeElement($rating)) {
            if ($rating->getEstablishment() === $this) {
                $rating->setEstablishment(null);
            }
        }
        return $this;
    }

    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function addPost(Post $post): static
    {
        if (!$this->posts->contains($post)) {
            $this->posts->add($post);
            $post->setEstablishment($this);
        }
        return $this;
    }

    public function removePost(Post $post): static
    {
        if ($this->posts->removeElement($post)) {
            if ($post->getEstablishment() === $this) {
                $post->setEstablishment(null);
            }
        }
        return $this;
    }

    public function getSubscription(): ?Subscription
    {
        return $this->subscription;
    }

    public function setSubscription(?Subscription $subscription): static
    {
        $this->subscription = $subscription;
        return $this;
    }

    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(EstablishmentImage $image): static
    {
        if (!$this->images->contains($image)) {
            $this->images->add($image);
            $image->setEstablishment($this);
        }
        return $this;
    }

    public function removeImage(EstablishmentImage $image): static
    {
        if ($this->images->removeElement($image)) {
            if ($image->getEstablishment() === $this) {
                $image->setEstablishment(null);
            }
        }
        return $this;
    }

    public function getLocation()
    {
        return $this->location;
    }

    public function setLocation($location)
    {
        $this->location = $location;
        return $this;
    }

    public function getLatitude(): ?float
    {
        $location = json_decode($this->location, true);
        return $location['latitude'] ?? null;
    }

    public function getLongitude(): ?float
    {
        $location = json_decode($this->location, true);
        return $location['longitude'] ?? null;
    }
}