<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'Il existe déjà un compte avec cet email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nom = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $prenom = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $date_inscription = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $statut_moderation = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $statut_validation = null;

    #[ORM\Column(nullable: true)]
    private ?int $numeroCartePresse = null;

    #[ORM\Column(nullable: true)]
    private ?float $scoreReputation = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): static
    {
        $this->nom = $nom;
        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(?string $prenom): static
    {
        $this->prenom = $prenom;
        return $this;
    }

    public function getDateInscription(): ?\DateTimeImmutable
    {
        return $this->date_inscription;
    }

    public function setDateInscription(?\DateTimeImmutable $date_inscription): static
    {
        $this->date_inscription = $date_inscription;
        return $this;
    }

    public function getStatutModeration(): ?string
    {
        return $this->statut_moderation;
    }

    public function setStatutModeration(?string $statut_moderation): static
    {
        $this->statut_moderation = $statut_moderation;
        return $this;
    }

    public function getStatutValidation(): ?string
    {
        return $this->statut_validation;
    }

    public function setStatutValidation(?string $statut_validation): static
    {
        $this->statut_validation = $statut_validation;
        return $this;
    }

    public function getNumeroCartePresse(): ?int
    {
        return $this->numeroCartePresse;
    }

    public function setNumeroCartePresse(?int $numeroCartePresse): static
    {
        $this->numeroCartePresse = $numeroCartePresse;
        return $this;
    }

    public function getScoreReputation(): ?float
    {
        return $this->scoreReputation;
    }

    public function setScoreReputation(?float $scoreReputation): static
    {
        $this->scoreReputation = $scoreReputation;
        return $this;
    }

    // Méthodes utilitaires
    public function isJournaliste(): bool
    {
        return in_array('ROLE_JOURNALISTE', $this->roles);
    }

    public function isContributeur(): bool
    {
        return in_array('ROLE_CONTRIBUTEUR', $this->roles);
    }

    public function isActive(): bool
    {
        return $this->statut_validation === 'validé';
    }

    public function isPending(): bool
    {
        return $this->statut_validation === 'en_attente';
    }
}