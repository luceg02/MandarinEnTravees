<?php

namespace App\Repository;

use App\Entity\Reponse;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reponse>
 */
class ReponseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reponse::class);
    }

    /**
     * Trouve les réponses d'un utilisateur avec pagination
     */
    public function findByUserWithPagination(User $user, int $page = 1, int $limit = 10): array
    {
        return $this->createQueryBuilder('r')
            ->leftJoin('r.demande', 'd')
            ->addSelect('d')
            ->andWhere('r.auteur = :user')
            ->setParameter('user', $user)
            ->orderBy('r.createdAt', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Compte le nombre total de réponses d'un utilisateur
     */
    public function countByUser(User $user): int
    {
        return $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->andWhere('r.auteur = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Trouve les réponses vérifiées d'un utilisateur
     */
    public function findVerifiedByUser(User $user): array
    {
        return $this->createQueryBuilder('r')
            ->leftJoin('r.demande', 'd')
            ->addSelect('d')
            ->andWhere('r.auteur = :user')
            ->andWhere('r.estVerifiee = :verified')
            ->setParameter('user', $user)
            ->setParameter('verified', true)
            ->orderBy('r.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les réponses récentes d'un utilisateur
     */
    public function findRecentByUser(User $user, int $limit = 5): array
    {
        return $this->createQueryBuilder('r')
            ->leftJoin('r.demande', 'd')
            ->addSelect('d')
            ->andWhere('r.auteur = :user')
            ->setParameter('user', $user)
            ->orderBy('r.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Calcule le score total des réponses d'un utilisateur
     */
    public function calculateUserScore(User $user): int
    {
        $result = $this->createQueryBuilder('r')
            ->select('SUM(
                CASE 
                    WHEN v.type = :upvote THEN 1
                    WHEN v.type = :downvote THEN -1
                    ELSE 0
                END
            ) as score')
            ->leftJoin('r.votes', 'v')
            ->andWhere('r.auteur = :user')
            ->setParameter('user', $user)
            ->setParameter('upvote', 'upvote')
            ->setParameter('downvote', 'downvote')
            ->getQuery()
            ->getSingleScalarResult();

        return $result ?? 0;
    }
}