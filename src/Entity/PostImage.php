<?php

namespace App\Entity;

use App\Repository\PostImageRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PostImageRepository::class)]
class PostImage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
     #[Groups(['user_profile', 'establishment_public'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['user_profile', 'establishment_public'])]
    private ?string $imageUrl = null;

    #[ORM\ManyToOne(inversedBy: 'images')]
    private ?Post $post = null;

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

    public function getPost(): ?Post
    {
        return $this->post;
    }

    public function setPost(?Post $post): static
    {
        $this->post = $post;

        return $this;
    }
}
