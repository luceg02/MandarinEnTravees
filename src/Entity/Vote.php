<?php

namespace App\Entity;

use App\Repository\VoteRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * ENTITÉ VOTE - GÈRE DEUX TYPES DE VOTES DISTINCTS :
 * 
 * 1. VÉRACITÉ des DEMANDES : 
 *    - Un utilisateur vote UNE FOIS par demande (quand il contribue)
 *    - Vote stocké avec demande_id (reponse_id = null)
 *    - Types : vrai, faux, trompeur, non_identifiable
 * 
 * 2. UTILITÉ des RÉPONSES :
 *    - Un utilisateur peut voter sur TOUTES les réponses (sauf la sienne)
 *    - Vote stocké avec reponse_id (demande_id = null)  
 *    - Types : utile, pas_utile
 */
#[ORM\Entity(repositoryClass: VoteRepository::class)]
#[ORM\UniqueConstraint(name: 'uniq_user_reponse', columns: ['user_id', 'reponse_id'])]
#[ORM\UniqueConstraint(name: 'uniq_user_demande', columns: ['user_id', 'demande_id'])]
class Vote
{
    // ===== TYPES DE VOTES SUR L'UTILITÉ DES RÉPONSES =====
    const TYPE_UTILE = 'utile';
    const TYPE_PAS_UTILE = 'pas_utile';
    
    // ===== TYPES DE VOTES SUR LA VÉRACITÉ DES DEMANDES =====
    const TYPE_VRAI = 'vrai';
    const TYPE_FAUX = 'faux';
    const TYPE_TROMPEUR = 'trompeur';
    const TYPE_NON_IDENTIFIABLE = 'non_identifiable';
    
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $type_vote = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $commentaire = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $date_vote = null;

    // ===== RELATIONS =====
    
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'votes')]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $user = null;

    // Pour les votes d'UTILITÉ des réponses
    #[ORM\ManyToOne(targetEntity: Reponse::class, inversedBy: 'votes')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Reponse $reponse = null;

    // Pour les votes de VÉRACITÉ des demandes
    #[ORM\ManyToOne(targetEntity: Demande::class, inversedBy: 'votesVeracite')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Demande $demande = null;

    // ===== GETTERS / SETTERS =====
    
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTypeVote(): ?string
    {
        return $this->type_vote;
    }

    public function setTypeVote(?string $type_vote): static
    {
        $this->type_vote = $type_vote;
        return $this;
    }

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(?string $commentaire): static
    {
        $this->commentaire = $commentaire;
        return $this;
    }

    public function getDateVote(): ?\DateTimeImmutable
    {
        return $this->date_vote;
    }

    public function setDateVote(\DateTimeImmutable $date_vote): static
    {
        $this->date_vote = $date_vote;
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

    public function getReponse(): ?Reponse
    {
        return $this->reponse;
    }

    public function setReponse(?Reponse $reponse): static
    {
        $this->reponse = $reponse;
        return $this;
    }

    public function getDemande(): ?Demande
    {
        return $this->demande;
    }

    public function setDemande(?Demande $demande): static
    {
        $this->demande = $demande;
        return $this;
    }

    // ===== MÉTHODES UTILITAIRES =====
    
    /**
     * Détermine si c'est un vote sur la véracité d'une demande
     */
    public function isVoteVeracite(): bool
    {
        return in_array($this->type_vote, [
            self::TYPE_VRAI,
            self::TYPE_FAUX, 
            self::TYPE_TROMPEUR,
            self::TYPE_NON_IDENTIFIABLE
        ]);
    }
    
    /**
     * Détermine si c'est un vote sur l'utilité d'une réponse
     */
    public function isVoteUtilite(): bool
    {
        return in_array($this->type_vote, [
            self::TYPE_UTILE,
            self::TYPE_PAS_UTILE
        ]);
    }

    /**
     * Retourne la couleur CSS associée au type de vote
     */
    public function getCouleurVote(): string
    {
        return match($this->type_vote) {
            self::TYPE_VRAI => '#10b981',           // Vert
            self::TYPE_FAUX => '#ef4444',           // Rouge  
            self::TYPE_TROMPEUR => '#f59e0b',       // Orange
            self::TYPE_NON_IDENTIFIABLE => '#6b7280', // Gris
            self::TYPE_UTILE => '#3b82f6',          // Bleu
            self::TYPE_PAS_UTILE => '#f87171',      // Rouge clair
            default => '#e5e7eb'                    // Gris clair
        };
    }

    /**
     * Retourne l'icône Font Awesome associée au type de vote
     */
    public function getIconeVote(): string
    {
        return match($this->type_vote) {
            self::TYPE_VRAI => 'fas fa-check-circle',
            self::TYPE_FAUX => 'fas fa-times-circle',
            self::TYPE_TROMPEUR => 'fas fa-exclamation-triangle',
            self::TYPE_NON_IDENTIFIABLE => 'fas fa-question-circle',
            self::TYPE_UTILE => 'fas fa-thumbs-up',
            self::TYPE_PAS_UTILE => 'fas fa-thumbs-down',
            default => 'fas fa-circle'
        };
    }

    /**
     * Retourne le libellé lisible du type de vote
     */
    public function getLibelleVote(): string
    {
        $types = [
            self::TYPE_VRAI => 'Vrai',
            self::TYPE_FAUX => 'Faux',
            self::TYPE_TROMPEUR => 'Trompeur',
            self::TYPE_NON_IDENTIFIABLE => 'Véracité non identifiable',
            self::TYPE_UTILE => 'Utile',
            self::TYPE_PAS_UTILE => 'Pas utile'
        ];
        
        return $types[$this->type_vote] ?? 'Inconnu';
    }
}