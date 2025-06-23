<?php
// src/Entity/Report.php

namespace App\Entity;

use App\Repository\ReportRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReportRepository::class)]
class Report
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 20)]
    private ?string $typeContenu = null;

    #[ORM\Column]
    private ?int $idContenu = null;

    #[ORM\Column(length: 50)]
    private ?string $raison = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $commentaire = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $signalePar = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $auteurContenu = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $dateReport = null;

    #[ORM\Column(length: 20)]
    private ?string $statut = null;

    public function __construct()
    {
        $this->dateReport = new \DateTimeImmutable();
        $this->statut = 'en_attente';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTypeContenu(): ?string
    {
        return $this->typeContenu;
    }

    public function setTypeContenu(string $typeContenu): static
    {
        $this->typeContenu = $typeContenu;
        return $this;
    }

    public function getIdContenu(): ?int
    {
        return $this->idContenu;
    }

    public function setIdContenu(int $idContenu): static
    {
        $this->idContenu = $idContenu;
        return $this;
    }

    public function getRaison(): ?string
    {
        return $this->raison;
    }

    public function setRaison(string $raison): static
    {
        $this->raison = $raison;
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

    public function getSignalePar(): ?User
    {
        return $this->signalePar;
    }

    public function setSignalePar(?User $signalePar): static
    {
        $this->signalePar = $signalePar;
        return $this;
    }

    public function getAuteurContenu(): ?User
    {
        return $this->auteurContenu;
    }

    public function setAuteurContenu(?User $auteurContenu): static
    {
        $this->auteurContenu = $auteurContenu;
        return $this;
    }

    public function getDateReport(): ?\DateTimeImmutable
    {
        return $this->dateReport;
    }

    public function setDateReport(\DateTimeImmutable $dateReport): static
    {
        $this->dateReport = $dateReport;
        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;
        return $this;
    }
}