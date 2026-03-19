<?php

namespace App\Entity;

use App\Repository\AbonneRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AbonneRepository::class)]
// UniqueEntity vérifie l'unicité en BDD — pas juste en PHP
#[UniqueEntity(fields: ['email'], message: 'Cet email est déjà inscrit à la newsletter.')]
class Abonne
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank(message: "L'email est obligatoire.")]
    #[Assert\Email(message: "Format email invalide.")]
    private ?string $email = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $prenom = null;

    // Date d'inscription
    #[ORM\Column]
    private ?\DateTimeImmutable $inscritLe = null;

    // true = confirmé, false = en attente
    #[ORM\Column]
    private bool $actif = true;

    // ===== GETTERS & SETTERS =====

    public function getId(): ?int { return $this->id; }

    public function getEmail(): ?string { return $this->email; }
    public function setEmail(string $email): static { $this->email = $email; return $this; }

    public function getPrenom(): ?string { return $this->prenom; }
    public function setPrenom(?string $prenom): static { $this->prenom = $prenom; return $this; }

    public function getInscritLe(): ?\DateTimeImmutable { return $this->inscritLe; }
    public function setInscritLe(\DateTimeImmutable $inscritLe): static { $this->inscritLe = $inscritLe; return $this; }

    public function isActif(): bool { return $this->actif; }
    public function setActif(bool $actif): static { $this->actif = $actif; return $this; }
}