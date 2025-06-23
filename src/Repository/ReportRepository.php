<?php
// src/Repository/ReportRepository.php

namespace App\Repository;

use App\Entity\Report;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ReportRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Report::class);
    }

    public function findByStatut(string $statut): array
    {
        return $this->createQueryBuilder('r')
            ->leftJoin('r.signalePar', 's')
            ->leftJoin('r.auteurContenu', 'a')
            ->addSelect('s', 'a')
            ->andWhere('r.statut = :statut')
            ->setParameter('statut', $statut)
            ->orderBy('r.dateReport', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findExistingReport(string $type, int $idContenu, User $user): ?Report
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.typeContenu = :type')
            ->andWhere('r.idContenu = :idContenu')
            ->andWhere('r.signalePar = :user')
            ->setParameter('type', $type)
            ->setParameter('idContenu', $idContenu)
            ->setParameter('user', $user)
            ->getQuery()
            ->getOneOrNullResult();
    }
}