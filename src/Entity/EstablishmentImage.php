<?php

namespace App\Entity;

use App\Repository\EstablishmentImageRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: EstablishmentImageRepository::class)]
class EstablishmentImage
{
    #[ORM\Id]
        #[ORM\GeneratedValue]
        #[ORM\Column]
        #[Groups(['establishment_public', 'user_profile', 'establishment_manage', 'establishment:read'])]
        private ?int $id = null;

        #[ORM\Column(length: 255)]
        #[Groups(['establishment_public', 'user_profile', 'establishment_manage', 'establishment:read'])]
        private ?string $imageUrl = null;

        #[ORM\Column]
        #[Groups(['establishment_public', 'user_profile', 'establishment_manage'])]
        private ?\DateTimeImmutable $createdAt = null;

        #[ORM\ManyToOne(inversedBy: 'images')]
        private ?Establishment $establishment = null;

        #[ORM\Column]
        #[Groups(['establishment_public', 'user_profile', 'establishment_manage', 'establishment:read'])]
        private ?bool $isLogo = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->isLogo = false; // Par défaut, l'image n'est pas un logo
    }

    public function getIsLogo(): ?bool
    {
        return $this->isLogo;
    }
    public function setIsLogo(bool $isLogo): static
    {
        $this->isLogo = $isLogo;

        return $this;
    }



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    public function setImageUrl(string $imageUrl): static
    {
        $this->imageUrl = $imageUrl;

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

        return $this;
    }
}
