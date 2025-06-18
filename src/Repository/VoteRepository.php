<?php

namespace App\Repository;

use App\Entity\Vote;
use App\Entity\User;
use App\Entity\Reponse;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Vote>
 */
class VoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vote::class);
    }

    /**
     * Trouve un vote spécifique d'un utilisateur pour une réponse
     */
    public function findUserVoteForReponse(User $user, Reponse $reponse): ?Vote
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.user = :user')
            ->andWhere('v.reponse = :reponse')
            ->setParameter('user', $user)
            ->setParameter('reponse', $reponse)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Compte les upvotes reçus par un utilisateur
     */
    public function countUpvotesForUser(User $user): int
    {
        return $this->createQueryBuilder('v')
            ->select('COUNT(v.id)')
            ->leftJoin('v.reponse', 'r')
            ->andWhere('r.auteur = :user')
            ->andWhere('v.type = :upvote')
            ->setParameter('user', $user)
            ->setParameter('upvote', 'upvote')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Compte les downvotes reçus par un utilisateur
     */
    public function countDownvotesForUser(User $user): int
    {
        return $this->createQueryBuilder('v')
            ->select('COUNT(v.id)')
            ->leftJoin('v.reponse', 'r')
            ->andWhere('r.auteur = :user')
            ->andWhere('v.type = :downvote')
            ->setParameter('user', $user)
            ->setParameter('downvote', 'downvote')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Calcule le score de réputation d'un utilisateur
     */
    public function calculateReputationScore(User $user): int
    {
        $upvotes = $this->countUpvotesForUser($user);
        $downvotes = $this->countDownvotesForUser($user);
        
        // Calcul simple : upvotes * 10 - downvotes * 2
        return ($upvotes * 10) - ($downvotes * 2);
    }
}