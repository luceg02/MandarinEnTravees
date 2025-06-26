<?php

namespace App\Entity;

use App\Repository\ReponseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReponseRepository::class)]
class Reponse
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $contenu = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $sources = null;

    #[ORM\Column(type: Types::INTEGER, options: ['default' => 0])]
    private int $nbVotesPositifs = 0;

    #[ORM\Column(type: Types::INTEGER, options: ['default' => 0])]
    private int $nbVotesNegatifs = 0;

    #[ORM\Column]
    private ?\DateTimeImmutable $dateCreation = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $dateModification = null;

    #[ORM\ManyToOne(inversedBy: 'reponses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $auteur = null;

    #[ORM\ManyToOne(inversedBy: 'reponses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Demande $demande = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $verdict = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $statut = null;

    /**
     * @var Collection<int, Vote>
     */
    #[ORM\OneToMany(targetEntity: Vote::class, mappedBy: 'reponse', cascade: ['remove'])]
    private Collection $votes;

    public function __construct()
    {
        $this->votes = new ArrayCollection();
        $this->dateCreation = new \DateTimeImmutable();
        //S'assurer que les compteurs sont initialisés
        $this->nbVotesPositifs = 0;
        $this->nbVotesNegatifs = 0;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContenu(): ?string
    {
        return $this->contenu;
    }

    public function setContenu(string $contenu): static
    {
        $this->contenu = $contenu;

        return $this;
    }

    public function getSources(): ?string
    {
        return $this->sources;
    }

    public function setSources(?string $sources): static
    {
        $this->sources = $sources;

        return $this;
    }

    public function getNbVotesPositifs(): int
    {
        return $this->nbVotesPositifs ?? 0;
    }

    public function setNbVotesPositifs(int $nbVotesPositifs): static
    {
        $this->nbVotesPositifs = max(0, $nbVotesPositifs); // Empêcher les valeurs négatives
        return $this;
    }

    public function getNbVotesNegatifs(): int
    {
        return $this->nbVotesNegatifs ?? 0;
    }

    public function setNbVotesNegatifs(int $nbVotesNegatifs): static
    {
        $this->nbVotesNegatifs = max(0, $nbVotesNegatifs); // Empêcher les valeurs négatives
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

    public function getAuteur(): ?User
    {
        return $this->auteur;
    }

    public function setAuteur(?User $auteur): static
    {
        $this->auteur = $auteur;

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

    public function getVerdict(): ?string
    {
        return $this->verdict;
    }

    public function setVerdict(?string $verdict): static
    {
        $this->verdict = $verdict;

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
     // ===== GESTION DES VOTES =====

    /**
     * @return Collection<int, Vote>
     */
    public function getVotes(): Collection
    {
        return $this->votes;
    }

    public function addVote(Vote $vote): static
    {
        if (!$this->votes->contains($vote)) {
            $this->votes->add($vote);
            $vote->setReponse($this);
        }
        return $this;
    }

    public function removeVote(Vote $vote): static
    {
        if ($this->votes->removeElement($vote)) {
            if ($vote->getReponse() === $this) {
                $vote->setReponse(null);
            }
        }
        return $this;
    }

    // ===== MÉTHODES DE CALCUL DES VOTES =====

    //Compte les votes "utile" depuis la collection (pour vérification)
    public function getVotesUtilesFromCollection(): int
    {
        return $this->votes->filter(fn($vote) => $vote->getTypeVote() === Vote::TYPE_UTILE)->count();
    }

    // Compte les votes "pas_utile" depuis la collection (pour vérification)
    public function getVotesPasUtilesFromCollection(): int
    {
        return $this->votes->filter(fn($vote) => $vote->getTypeVote() === Vote::TYPE_PAS_UTILE)->count();
    }

    /**
     * MÉTHODE PRINCIPALE : Utilise les colonnes pour la performance
     */
    public function getVotesUtiles(): int
    {
        return $this->getNbVotesPositifs();
    }

    /**
     * MÉTHODE PRINCIPALE : Utilise les colonnes pour la performance
     */
    public function getVotesPasUtiles(): int
    {
        return $this->getNbVotesNegatifs();
    }

    /**
     * Score pondéré selon le niveau des utilisateurs qui votent
     */
    public function getVotesUtilesPondered(): int
    {
        $score = 0;
        foreach ($this->votes as $vote) {
            if ($vote->getTypeVote() === Vote::TYPE_UTILE) {
                // Pondération selon le type d'utilisateur
                if ($vote->getUser()->isJournaliste() ?? false) {
                    $score += 10; // Journaliste = 10 points
                } else {
                    $poids = $vote->getUser()->getNiveau() ?? 1;
                    $score += $poids; // Utilisateur normal = son niveau
                }
            }
        }
        return $score;
    }

    public function getVotesPasUtilesPondered(): int
    {
        $score = 0;
        foreach ($this->votes as $vote) {
            if ($vote->getTypeVote() === Vote::TYPE_PAS_UTILE) {
                if ($vote->getUser()->isJournaliste() ?? false) {
                    $score += 10;
                } else {
                    $poids = $vote->getUser()->getNiveau() ?? 1;
                    $score += $poids;
                }
            }
        }
        return $score;
    }

    //Score pondéré net (positifs - négatifs)
    public function getScorePondere(): int
    {
        return $this->getVotesUtilesPondered() - $this->getVotesPasUtilesPondered();
    }

    //Score simple net (positifs - négatifs)
    public function getScoreNet(): int
    {
        return $this->getNbVotesPositifs() - $this->getNbVotesNegatifs();
    }

    /**
     * Recalcule les compteurs en cas de désynchronisation
     * À appeler si vous soupçonnez que les colonnes ne sont pas à jour
     */
    public function recalculerCompteurs(): void
    {
        $this->nbVotesPositifs = $this->getVotesUtilesFromCollection();
        $this->nbVotesNegatifs = $this->getVotesPasUtilesFromCollection();
    }

    /**
     * Vérifie si les compteurs sont synchronisés avec la collection
     */
    public function verifierCoherence(): bool
    {
        return $this->nbVotesPositifs === $this->getVotesUtilesFromCollection() &&
               $this->nbVotesNegatifs === $this->getVotesPasUtilesFromCollection();
    }
}