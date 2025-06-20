<?php

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation\Translatable;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
class Category
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['category_list'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['category_list'])]
    #[Translatable()]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['category_list'])]
    private ?string $slug = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * @var Collection<int, TypeEstablishment>
     */
    #[ORM\OneToMany(targetEntity: TypeEstablishment::class, mappedBy: 'category')]
    private Collection $typeEstablishments;

    public function __construct()
    {
        $this->typeEstablishments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return Collection<int, TypeEstablishment>
     */
    public function getTypeEstablishments(): Collection
    {
        return $this->typeEstablishments;
    }

    public function addTypeEstablishment(TypeEstablishment $typeEstablishment): static
    {
        if (!$this->typeEstablishments->contains($typeEstablishment)) {
            $this->typeEstablishments->add($typeEstablishment);
            $typeEstablishment->setCategory($this);
        }

        return $this;
    }

    public function removeTypeEstablishment(TypeEstablishment $typeEstablishment): static
    {
        if ($this->typeEstablishments->removeElement($typeEstablishment)) {
            // set the owning side to null (unless already changed)
            if ($typeEstablishment->getCategory() === $this) {
                $typeEstablishment->setCategory(null);
            }
        }

        return $this;
    }
}
