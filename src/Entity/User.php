<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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

    // Collections
    /**
     * @var Collection<int, Vote>
     */
    #[ORM\OneToMany(targetEntity: Vote::class, mappedBy: 'user')]
    private Collection $votes;

    /**
     * @var Collection<int, Reponse>
     */
    #[ORM\OneToMany(targetEntity: Reponse::class, mappedBy: 'auteur')]
    private Collection $reponses;

    public function __construct()
    {
        $this->votes = new ArrayCollection();
        $this->reponses = new ArrayCollection();
    }

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
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function eraseCredentials(): void
    {
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
        return in_array('ROLE_USER', $this->roles);
    }

    public function isActive(): bool
    {
        return $this->statut_validation === 'validé';
    }

    public function isPending(): bool
    {
        return $this->statut_validation === 'en_attente';
    }


    // Collections
    public function getVotes(): Collection
    {
        return $this->votes;
    }

    public function addVote(Vote $vote): static
    {
        if (!$this->votes->contains($vote)) {
            $this->votes->add($vote);
            $vote->setUser($this);
        }
        return $this;
    }

    public function removeVote(Vote $vote): static
    {
        if ($this->votes->removeElement($vote)) {
            if ($vote->getUser() === $this) {
                $vote->setUser(null);
            }
        }
        return $this;
    }

    public function getReponses(): Collection
    {
        return $this->reponses;
    }

    public function addReponse(Reponse $reponse): static
    {
        if (!$this->reponses->contains($reponse)) {
            $this->reponses->add($reponse);
            $reponse->setAuteur($this);
        }
        return $this;
    }

    public function removeReponse(Reponse $reponse): static
    {
        if ($this->reponses->removeElement($reponse)) {
            if ($reponse->getAuteur() === $this) {
                $reponse->setAuteur(null);
            }
        }
        return $this;
    }

    /**
     * Calcule automatiquement le score de fiabilité
     */
    public function calculerScoreFiabilite(): float
    {
        // Les journalistes n'ont pas de score calculé
        if ($this->isJournaliste()) {
            return 0.0;
        }

        $score = 0.0;

        // 1. Points basés sur les votes reçus sur les réponses
        foreach ($this->reponses as $reponse) {
            // ✅ Utilisation des méthodes pondérées au lieu des compteurs simples
            $votesPositifsPonderes = $reponse->getVotesUtilesPondered();
            $votesNegatifsPonderes = $reponse->getVotesPasUtilesPondered();
            
            // Coefficients adaptés à la pondération (journaliste = 100x)
            $score += $votesPositifsPonderes * 0.02;  // 2 points pour 100 (= 1 journaliste)
            $score -= $votesNegatifsPonderes * 0.01;  // 1 point pour 100 (= 1 journaliste)
            
            // Bonus pour les réponses très appréciées (seuil adapté à la pondération)
            if ($votesPositifsPonderes >= 100) {  // Équivaut à ~1 journaliste ou 100 utilisateurs
                $score += 5;
            }
            
            // Bonus qualité : ratio adapté aux votes pondérés
            $totalVotesPonderes = $votesPositifsPonderes + $votesNegatifsPonderes;
            if ($totalVotesPonderes >= 50) {  // Seuil réduit car les votes sont pondérés
                $ratio = $votesPositifsPonderes / max(1, $votesNegatifsPonderes);
                if ($ratio >= 3) {
                    $score += 3;
                }
            }
        }

        // 2. Points d'activité
        $score += $this->reponses->count() * 1; // +1 point par réponse postée

        // 3. Points d'ancienneté
        if ($this->date_inscription) {
            $joursAnciennete = $this->date_inscription->diff(new \DateTimeImmutable())->days;
            $moisAnciennete = floor($joursAnciennete / 30);
            $score += min(10, $moisAnciennete * 0.5); // +0.5 par mois, max 10 points
        }

        // 4. Malus de modération
        if ($this->statut_moderation === 'banni_temporaire') {
            $score -= 10;
        } elseif ($this->statut_moderation === 'banni') {
            $score -= 20;
        }

        // Garder le score entre 0 et 100
        return max(0, min(100, $score));
    }

    /**
     * Met à jour le score de réputation en base
     */
    public function mettreAJourScore(): void
    {
        $this->scoreReputation = $this->calculerScoreFiabilite();
    }

    /**
     * Détermine si l'utilisateur est encore "nouveau"
     */
    public function isNouveau(): bool
    {
        // Les journalistes ne sont jamais "nouveaux"
        if ($this->isJournaliste()) {
            return false;
        }

        // Nouveau si :
        // - 0 contribution (réponse)
        // - Moins de 7 jours d'inscription
        // - Score = 0
        $ancienneteJours = $this->date_inscription 
            ? $this->date_inscription->diff(new \DateTimeImmutable())->days 
            : 0;

        return $this->reponses->count() === 0 
            && $ancienneteJours < 7 
            && ($this->scoreReputation ?? 0) === 0;
    }

    /**
     * Retourne le niveau basé sur le score (seuils réalistes)
     */
    public function getNiveau(): string
    {
        if ($this->isJournaliste()) {
            return 'Journaliste';
        }

        if ($this->isNouveau()) {
            return 'Nouveau';
        }

        $score = $this->scoreReputation ?? 0;

        // Seuils réalistes (plus bas qu'avant)
        if ($score >= 50) return 'Expert';      // Au lieu de 80
        if ($score >= 30) return 'Fiable';      // Au lieu de 60  
        if ($score >= 15) return 'Correct';     // Au lieu de 30
        if ($score > 0) return 'Débutant';
        
        return 'Nouveau';
    }

    /**
     * Retourne la couleur CSS pour le niveau
     */
    public function getCouleurNiveau(): string
    {
        return match($this->getNiveau()) {
            'Journaliste' => '#f59e0b',  // Jaune
            'Expert' => '#10b981',       // Vert
            'Fiable' => '#667eea',       // Bleu
            'Correct' => '#f59e0b',      // Jaune
            'Débutant' => '#f97316',     // Orange
            'Nouveau' => '#e5e7eb',      // Gris
            default => '#e5e7eb'
        };
    }

    /**
     * Retourne l'icône Font Awesome pour le niveau
     */
    public function getIconeNiveau(): string
    {
        return match($this->getNiveau()) {
            'Journaliste' => 'fas fa-newspaper',
            'Expert' => 'fas fa-star',
            'Fiable' => 'fas fa-thumbs-up',
            'Correct' => 'fas fa-user-check',
            'Débutant' => 'fas fa-exclamation-triangle',
            'Nouveau' => 'fas fa-user-plus',
            default => 'fas fa-user'
        };
    }
}