<?php

namespace App\Entity;

use App\Repository\HoraireRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation\Translatable;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: HoraireRepository::class)]
class Horaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user_profile', 'establishment_public'])]
    private ?int $id = null;

    #[ORM\Column(length: 30)]
    #[Groups(['user_profile', 'establishment_public'])]
    #[Translatable()]
    private ?string $jour = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    #[Groups(['user_profile', 'establishment_public'])]
    private ?\DateTime $heureOuverture = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    #[Groups(['user_profile', 'establishment_public'])]
    private ?\DateTime $heureFermeture = null;

     #[ORM\ManyToOne(inversedBy: 'horaires')]
    private ?Establishment $etablissement = null;
    public function __construct()
    {
        $this->heureOuverture = new \DateTime();
        $this->heureFermeture = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getJour(): ?string
    {
        return $this->jour;
    }

    public function getEtablissement(): ?Establishment
    {
        return $this->etablissement;
    }

    public function setEtablissement(?Establishment $etablissement): static
    {
        $this->etablissement = $etablissement;

        return $this;
    }

    public function setJour(string $jour): static
    {
        $this->jour = $jour;

        return $this;
    }

    public function getHeureOuverture(): ?\DateTime
    {
        return $this->heureOuverture;
    }

    public function setHeureOuverture(\DateTime $heureOuverture): static
    {
        $this->heureOuverture = $heureOuverture;

        return $this;
    }

    public function getHeureFermeture(): ?\DateTime
    {
        return $this->heureFermeture;
    }

    public function setHeureFermeture(\DateTime $heureFermeture): static
    {
        $this->heureFermeture = $heureFermeture;

        return $this;
    }
}
