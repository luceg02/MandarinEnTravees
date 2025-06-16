<?php

namespace App\Entity;

use App\Repository\ReponseRepository;
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

    #[ORM\Column]
    private ?int $nbVotesPositifs = null;

    #[ORM\Column]
    private ?int $nbVotesNegatifs = null;

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

    public function getNbVotesPositifs(): ?int
    {
        return $this->nbVotesPositifs;
    }

    public function setNbVotesPositifs(int $nbVotesPositifs): static
    {
        $this->nbVotesPositifs = $nbVotesPositifs;

        return $this;
    }

    public function getNbVotesNegatifs(): ?int
    {
        return $this->nbVotesNegatifs;
    }

    public function setNbVotesNegatifs(int $nbVotesNegatifs): static
    {
        $this->nbVotesNegatifs = $nbVotesNegatifs;

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
}
