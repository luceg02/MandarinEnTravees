<?php

namespace App\Entity;

use App\Repository\DemandeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DemandeRepository::class)]
class Demande
{   
    // ===== CONSTANTES POUR LES VERDICTS =====
    const VERDICT_VRAI = 'vrai';
    const VERDICT_FAUX = 'faux';
    const VERDICT_TROMPEUR = 'trompeur';
    const VERDICT_NON_IDENTIFIABLE = 'non_identifiable';
    const VERDICT_CONTROVERSE = 'controverse';
    
    // ===== PROPRIÉTÉS EXISTANTES =====
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $titre = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $liensSources = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $statut = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $dateCreation = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $dateModification = null;

    #[ORM\Column]
    private ?int $nbReponses = null;

     // ===== 🆕 NOUVELLES PROPRIÉTÉS POUR LE FACT-CHECKING =====
    
    #[ORM\Column(length: 50, nullable: true)]
    private ?string $verdict_automatique = null;

    #[ORM\Column(nullable: true)]
    private ?float $score_confiance = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $date_derniere_evaluation = null;

    #[ORM\ManyToOne(inversedBy: 'demandes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $auteur = null;

    #[ORM\ManyToOne(inversedBy: 'demandes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Categorie $categorie = null;

        // ===== 🆕 NOUVELLE RELATION POUR LES VOTES DE VÉRACITÉ =====
    
    /**
     * Collection des votes de véracité sur cette demande
     * @var Collection<int, Vote>
     */
    #[ORM\OneToMany(targetEntity: Vote::class, mappedBy: 'demande')]
    private Collection $votesVeracite;

    /**
     * @var Collection<int, Reponse>
     */
    #[ORM\OneToMany(targetEntity: Reponse::class, mappedBy: 'demande')]
    private Collection $reponses;

    public function __construct()
    {
        $this->reponses = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;
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

    public function getLiensSources(): ?string
    {
        return $this->liensSources;
    }

    public function setLiensSources(?string $liensSources): static
    {
        $this->liensSources = $liensSources;
        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(?string $statut): static
    {
        $this->statut = $statut;
        return $this;
    }

    public function getDateCreation(): ?\DateTimeImmutable
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeImmutable $dateCreation): static
    {
        $this->dateCreation = $dateCreation;
        return $this;
    }

    public function getDateModification(): ?\DateTimeImmutable
    {
        return $this->dateModification;
    }

    public function setDateModification(?\DateTimeImmutable $dateModification): static
    {
        $this->dateModification = $dateModification;
        return $this;
    }

    public function getNbReponses(): ?int
    {
        return $this->nbReponses;
    }

    public function setNbReponses(int $nbReponses): static
    {
        $this->nbReponses = $nbReponses;
        return $this;
    }

    public function getAuteur(): ?User
    {
        return $this->auteur;
    }

    public function setAuteur(?User $auteur): static
    {
        $this->auteur = $auteur;
        return $this;
    }
    public function getCategorie(): ?Categorie
    {
        return $this->categorie;
    }

    public function setCategorie(?Categorie $categorie): static
    {
        $this->categorie = $categorie;
        return $this;
    }

    /**
     * @return Collection<int, Reponse>
     */
    public function getReponses(): Collection
    {
        return $this->reponses;
    }

    public function addReponse(Reponse $reponse): static
    {
        if (!$this->reponses->contains($reponse)) {
            $this->reponses->add($reponse);
            $reponse->setDemande($this);
        }
        return $this;
    }

    public function removeReponse(Reponse $reponse): static
    {
        if ($this->reponses->removeElement($reponse)) {
            if ($reponse->getDemande() === $this) {
                $reponse->setDemande(null);
            }
        }
        return $this;
    }

     // ===== 🆕 GETTERS / SETTERS POUR LE FACT-CHECKING =====
    
    public function getVerdictAutomatique(): ?string
    {
        return $this->verdict_automatique;
    }

    public function setVerdictAutomatique(?string $verdict_automatique): static
    {
        $this->verdict_automatique = $verdict_automatique;
        return $this;
    }

    public function getScoreConfiance(): ?float
    {
        return $this->score_confiance;
    }

    public function setScoreConfiance(?float $score_confiance): static
    {
        $this->score_confiance = $score_confiance;
        return $this;
    }

    public function getDateDerniereEvaluation(): ?\DateTimeImmutable
    {
        return $this->date_derniere_evaluation;
    }

    public function setDateDerniereEvaluation(?\DateTimeImmutable $date_derniere_evaluation): static
    {
        $this->date_derniere_evaluation = $date_derniere_evaluation;
        return $this;
    }

    // ===== 🆕 GESTION DES VOTES DE VÉRACITÉ =====
    
    /**
     * @return Collection<int, Vote>
     */
    public function getVotesVeracite(): Collection
    {
        return $this->votesVeracite;
    }

    public function addVoteVeracite(Vote $vote): static
    {
        if (!$this->votesVeracite->contains($vote)) {
            $this->votesVeracite->add($vote);
            $vote->setDemande($this);
        }
        return $this;
    }

    public function removeVoteVeracite(Vote $vote): static
    {
        if ($this->votesVeracite->removeElement($vote)) {
            if ($vote->getDemande() === $this) {
                $vote->setDemande(null);
            }
        }
        return $this;
    }

    // ===== 🆕 MÉTHODES UTILITAIRES POUR LE FACT-CHECKING =====
    
    /**
     * Calcule automatiquement le verdict basé sur les votes pondérés
     */
    public function calculerVerdictAutomatique(): void
    {
        $scores = $this->calculerScoresVeracite();
        
        $scoreTotal = array_sum($scores);
        
        // Pas assez de votes
        if ($scoreTotal < 1 || $this->votesVeracite->count() < 1) {
            $this->verdict_automatique = null;
            $this->score_confiance = 0;
            $this->date_derniere_evaluation = new \DateTimeImmutable();
            return;
        }
        
        // Trouver le verdict gagnant
        $verdictGagnant = array_keys($scores, max($scores))[0];
        $scoreMax = max($scores);
        
        // CALCUL DE CONFIANCE
        // Calculer le poids total de tous les votes
        $poidsTotal = 0;
        foreach ($this->votesVeracite as $vote) {
            $poidsTotal += $this->calculerPoidsVote($vote->getUser());
        }
        
        // Confiance = pourcentage du poids pour le verdict gagnant
        $confiance = $poidsTotal > 0 ? ($scoreMax / $poidsTotal) * 100 : 0;
        
        // Détection de controverse
        $scoreTrie = array_values($scores);
        rsort($scoreTrie);
        $margeAbsolue = $scoreTrie[0] - ($scoreTrie[1] ?? 0);
        $margeRelative = $scoreTotal > 0 ? ($margeAbsolue / $scoreTotal) * 100 : 0;
        
        // Si trop serré, marquer comme controversé
        if ($confiance < 60 || $margeRelative < 25) {
            $this->verdict_automatique = self::VERDICT_CONTROVERSE;
            $this->score_confiance = $margeRelative;
        } else {
            $this->verdict_automatique = $verdictGagnant;
            $this->score_confiance = $confiance;
        }
        
        $this->date_derniere_evaluation = new \DateTimeImmutable();
    }
    
    /**
     * Calcule les scores pondérés pour chaque type de verdict
     */
    private function calculerScoresVeracite(): array
    {
        $scores = [
            self::VERDICT_VRAI => 0,
            self::VERDICT_FAUX => 0,
            self::VERDICT_TROMPEUR => 0,
            self::VERDICT_NON_IDENTIFIABLE => 0,
        ];
        
        foreach ($this->votesVeracite as $vote) {
            if (!$vote->isVoteVeracite()) continue;
            
            $poids = $this->calculerPoidsVote($vote->getUser());
            $typeVote = $vote->getTypeVote();
            
            if (isset($scores[$typeVote])) {
                $scores[$typeVote] += $poids;
            }
        }
        
        return $scores;
    }
    
    /**
     * Calcule le poids d'un vote selon le profil de l'utilisateur
     */
    private function calculerPoidsVote(User $user): float
    {
        // Journalistes = poids fort
        if (method_exists($user, 'isJournaliste') && $user->isJournaliste()) {
            return 10.0;
        }
        
        // Utiliser directement le score de réputation
        $scoreReputation = 0;
        
        // Essayer plusieurs méthodes pour récupérer le score
        if (method_exists($user, 'getScoreReputation')) {
            $scoreReputation = $user->getScoreReputation() ?? 0;
        } elseif (method_exists($user, 'getNiveau')) {
            // Si pas de scoreReputation, déduire du niveau affiché
            $niveau = $user->getNiveau();
            if (is_string($niveau) && preg_match('/(\d+)%/', $niveau, $matches)) {
                $scoreReputation = (int)$matches[1];
            }
        }
        
        // Convertir le score (0-100) en poids de vote
        if ($scoreReputation >= 80) {
            return 5.0; // Expert
        } elseif ($scoreReputation >= 60) {
            return 3.0; // Fiable  
        } elseif ($scoreReputation >= 30) {
            return 2.0; // Correct
        } elseif ($scoreReputation > 0) {
            return 1.0; // Débutant
        } else {
            return 0.3; // Nouveau (anti-spam)
        }
    }
    
    /**
     * Retourne le nombre minimum de contributeurs requis pour un verdict fiable
     */
    public function getNombreMinimumContributeurs(): int
    {
        // Ajuster selon la complexité ou la catégorie de la demande
        return 3;
    }
    
    /**
     * Vérifie si la demande a assez de votes pour un verdict fiable
     */
    public function aAssezDeVotes(): bool
    {
        $utilisateursAyantVote = [];
        foreach ($this->votesVeracite as $vote) {
            $utilisateursAyantVote[$vote->getUser()->getId()] = true;
        }
        
        return count($utilisateursAyantVote) >= $this->getNombreMinimumContributeurs();
    }
    
    /**
     * Retourne la couleur CSS associée au verdict
     */
    public function getCouleurVerdict(): string
    {
        return match($this->verdict_automatique) {
            self::VERDICT_VRAI => '#10b981',           // Vert
            self::VERDICT_FAUX => '#ef4444',           // Rouge  
            self::VERDICT_TROMPEUR => '#f59e0b',       // Orange
            self::VERDICT_NON_IDENTIFIABLE => '#6b7280', // Gris
            self::VERDICT_CONTROVERSE => '#8b5cf6',     // Violet
            default => '#e5e7eb'                       // Gris clair
        };
    }
    
    /**
     * Retourne la classe CSS Bootstrap associée au verdict
     */
    public function getClasseBootstrapVerdict(): string
    {
        return match($this->verdict_automatique) {
            self::VERDICT_VRAI => 'success',
            self::VERDICT_FAUX => 'danger',
            self::VERDICT_TROMPEUR => 'warning',
            self::VERDICT_NON_IDENTIFIABLE => 'secondary',
            self::VERDICT_CONTROVERSE => 'info',
            default => 'light'
        };
    }
    
    /**
     * Retourne l'icône Font Awesome associée au verdict
     */
    public function getIconeVerdict(): string
    {
        return match($this->verdict_automatique) {
            self::VERDICT_VRAI => 'fas fa-check-circle',
            self::VERDICT_FAUX => 'fas fa-times-circle',
            self::VERDICT_TROMPEUR => 'fas fa-exclamation-triangle',
            self::VERDICT_NON_IDENTIFIABLE => 'fas fa-question-circle',
            self::VERDICT_CONTROVERSE => 'fas fa-balance-scale',
            default => 'fas fa-clock'
        };
    }
    
    /**
     * Retourne le libellé lisible du verdict
     */
    public function getLibelleVerdict(): string
    {
        $libelles = [
            self::VERDICT_VRAI => 'Information vraie',
            self::VERDICT_FAUX => 'Information fausse',
            self::VERDICT_TROMPEUR => 'Information trompeuse',
            self::VERDICT_NON_IDENTIFIABLE => 'Véracité non identifiable',
            self::VERDICT_CONTROVERSE => 'Information controversée'
        ];
        
        return $libelles[$this->verdict_automatique] ?? 'En cours de vérification';
    }
    
    /**
     * Retourne un résumé des statistiques de votes
     */
    public function getStatistiquesVotes(): array
    {
        $stats = [
            self::VERDICT_VRAI => 0,
            self::VERDICT_FAUX => 0,
            self::VERDICT_TROMPEUR => 0,
            self::VERDICT_NON_IDENTIFIABLE => 0,
        ];
        
        $total = $this->votesVeracite->count();
        
        foreach ($this->votesVeracite as $vote) {
            if ($vote->isVoteVeracite() && isset($stats[$vote->getTypeVote()])) {
                $stats[$vote->getTypeVote()]++;
            }
        }
        
        // Calculer les pourcentages
        $pourcentages = [];
        foreach ($stats as $type => $count) {
            $pourcentages[$type] = [
                'count' => $count,
                'percentage' => $total > 0 ? round(($count / $total) * 100) : 0
            ];
        }
        
        return [
            'total' => $total,
            'repartition' => $pourcentages
        ];
    }
    
    /**
     * Vérifie si un utilisateur a déjà voté sur la véracité de cette demande
     */
    public function utilisateurAVote(User $user): bool
    {
        foreach ($this->votesVeracite as $vote) {
            if ($vote->getUser() === $user) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Récupère le vote de véracité d'un utilisateur spécifique
     */
    public function getVoteVeraciteUtilisateur(User $user): ?Vote
    {
        foreach ($this->votesVeracite as $vote) {
            if ($vote->getUser() === $user) {
                return $vote;
            }
        }
        return null;
    }
    
    /**
     * Détermine si la demande nécessite une attention particulière
     */
    public function necessiteAttention(): bool
    {
        // Si controversée ou si beaucoup de votes négatifs
        if ($this->verdict_automatique === self::VERDICT_CONTROVERSE) {
            return true;
        }
        
        $stats = $this->getStatistiquesVotes();
        $totalNegatifs = $stats['repartition'][self::VERDICT_FAUX]['count'] + 
                        $stats['repartition'][self::VERDICT_TROMPEUR]['count'];
        
        return $totalNegatifs >= 3; // Seuil configurable
    }
    
    /**
     * Retourne tous les verdicts possibles avec leurs libellés
     */
    public static function getVerdictsDisponibles(): array
    {
        return [
            self::VERDICT_VRAI => 'Vrai',
            self::VERDICT_FAUX => 'Faux',
            self::VERDICT_TROMPEUR => 'Trompeur',
            self::VERDICT_NON_IDENTIFIABLE => 'Véracité non identifiable'
        ];
    }
}