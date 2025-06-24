<?php

namespace App\Repository;

use App\Entity\Demande;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Demande>
 */
class DemandeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Demande::class);
    }

    /**
     * Trouve les demandes pour la page d'accueil avec pagination
     */
    public function findDemandesForHomepage(int $page, int $limit): array
    {
        $offset = ($page - 1) * $limit;
        
        return $this->createQueryBuilder('d')
            ->orderBy('d.dateCreation', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Recherche des demandes par titre et description
     */
    public function rechercherDemandes(string $query, int $page, int $limit): array
    {
        $offset = ($page - 1) * $limit;
        
        return $this->createQueryBuilder('d')
            ->where('d.titre LIKE :query OR d.description LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('d.dateCreation', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Compte les résultats de recherche
     */
    public function compterResultatsRecherche(string $query): int
    {
        return $this->createQueryBuilder('d')
            ->select('COUNT(d.id)')
            ->where('d.titre LIKE :query OR d.description LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Trouve les demandes par catégorie avec pagination
     */
    public function findByCategorie($categorie, int $page, int $limit): array
    {
        $offset = ($page - 1) * $limit;
        
        return $this->createQueryBuilder('d')
            ->where('d.categorie = :categorie')
            ->setParameter('categorie', $categorie) // Peut être ID ou entité
            ->orderBy('d.dateCreation', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Compte les demandes par catégorie
     */
    public function countByCategorie($categorie): int
    {
        return $this->createQueryBuilder('d')
            ->select('COUNT(d.id)')
            ->where('d.categorie = :categorie')
            ->setParameter('categorie', $categorie) // Peut être ID ou entité
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Trouve les demandes récentes (derniers X jours)
     */
    public function findRecentDemandes(int $days = 7, int $limit = 10): array
    {
        $date = new \DateTime();
        $date->modify("-{$days} days");
        
        return $this->createQueryBuilder('d')
            ->where('d.dateCreation >= :date')
            ->setParameter('date', $date)
            ->orderBy('d.dateCreation', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les demandes par statut
     */
    public function findByStatut(string $statut, int $limit = null): array
    {
        $qb = $this->createQueryBuilder('d')
            ->where('d.statut = :statut')
            ->setParameter('statut', $statut)
            ->orderBy('d.dateCreation', 'DESC');
            
        if ($limit) {
            $qb->setMaxResults($limit);
        }
        
        return $qb->getQuery()->getResult();
    }

    /**
     * Compte les demandes par statut
     */
    public function countByStatut(string $statut): int
    {
        return $this->createQueryBuilder('d')
            ->select('COUNT(d.id)')
            ->where('d.statut = :statut')
            ->setParameter('statut', $statut)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Trouve les demandes d'un utilisateur
     */
    public function findByUser($user, int $limit = null): array
    {
        $qb = $this->createQueryBuilder('d')
            ->where('d.demandeur = :user')
            ->setParameter('user', $user)
            ->orderBy('d.dateCreation', 'DESC');
            
        if ($limit) {
            $qb->setMaxResults($limit);
        }
        
        return $qb->getQuery()->getResult();
    }

    /**
     * Statistiques générales des demandes
     */
    public function getStatistiquesGenerales(): array
    {
        $total = $this->count([]);
        
        $statuts = $this->createQueryBuilder('d')
            ->select('d.statut, COUNT(d.id) as count')
            ->groupBy('d.statut')
            ->getQuery()
            ->getResult();
            
        $categories = $this->createQueryBuilder('d')
            ->select('d.categorie, COUNT(d.id) as count')
            ->groupBy('d.categorie')
            ->getQuery()
            ->getResult();
            
        // Demandes par mois (12 derniers mois)
        $demandesParMois = $this->createQueryBuilder('d')
            ->select('YEAR(d.dateCreation) as annee, MONTH(d.dateCreation) as mois, COUNT(d.id) as count')
            ->where('d.dateCreation >= :date')
            ->setParameter('date', new \DateTime('-12 months'))
            ->groupBy('annee, mois')
            ->orderBy('annee, mois')
            ->getQuery()
            ->getResult();
            
        return [
            'total' => $total,
            'par_statut' => $statuts,
            'par_categorie' => $categories,
            'par_mois' => $demandesParMois
        ];
    }

    /**
     * Recherche avancée avec filtres multiples
     */
    public function rechercheAvancee(array $criteres = []): array
    {
        $qb = $this->createQueryBuilder('d');
        
        // Filtre par texte (titre ou description)
        if (!empty($criteres['texte'])) {
            $qb->andWhere('d.titre LIKE :texte OR d.description LIKE :texte')
               ->setParameter('texte', '%' . $criteres['texte'] . '%');
        }
        
        // Filtre par catégorie
        if (!empty($criteres['categorie'])) {
            $qb->andWhere('d.categorie = :categorie')
               ->setParameter('categorie', $criteres['categorie']);
        }
        
        // Filtre par statut
        if (!empty($criteres['statut'])) {
            $qb->andWhere('d.statut = :statut')
               ->setParameter('statut', $criteres['statut']);
        }
        
        // Filtre par date de création
        if (!empty($criteres['date_debut'])) {
            $qb->andWhere('d.dateCreation >= :date_debut')
               ->setParameter('date_debut', $criteres['date_debut']);
        }
        
        if (!empty($criteres['date_fin'])) {
            $qb->andWhere('d.dateCreation <= :date_fin')
               ->setParameter('date_fin', $criteres['date_fin']);
        }
        
        // Filtre par utilisateur
        if (!empty($criteres['demandeur'])) {
            $qb->andWhere('d.demandeur = :demandeur')
               ->setParameter('demandeur', $criteres['demandeur']);
        }
        
        // Tri
        $orderBy = $criteres['order_by'] ?? 'dateCreation';
        $orderDirection = $criteres['order_direction'] ?? 'DESC';
        $qb->orderBy('d.' . $orderBy, $orderDirection);
        
        // Pagination
        if (!empty($criteres['limit'])) {
            $qb->setMaxResults($criteres['limit']);
        }
        
        if (!empty($criteres['offset'])) {
            $qb->setFirstResult($criteres['offset']);
        }
        
        return $qb->getQuery()->getResult();
    }

    /**
     * Trouve les demandes similaires basées sur le titre
     */
    public function findSimilaires(string $titre, ?int $excludeId = null, int $limit = 5): array
    {
        $qb = $this->createQueryBuilder('d')
            ->where('d.titre LIKE :titre')
            ->setParameter('titre', '%' . $titre . '%')
            ->orderBy('d.dateCreation', 'DESC')
            ->setMaxResults($limit);
            
        if ($excludeId) {
            $qb->andWhere('d.id != :excludeId')
               ->setParameter('excludeId', $excludeId);
        }
        
        return $qb->getQuery()->getResult();
    }

    /**
     * Trouve les demandes populaires (avec le plus de réponses)
     */
    public function findPopulaires(int $limit = 10): array
    {
        return $this->createQueryBuilder('d')
            ->leftJoin('d.reponses', 'r')
            ->groupBy('d.id')
            ->orderBy('COUNT(r.id)', 'DESC')
            ->addOrderBy('d.dateCreation', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les demandes sans réponse
     */
    public function findSansReponse(int $limit = null): array
    {
        $qb = $this->createQueryBuilder('d')
            ->leftJoin('d.reponses', 'r')
            ->where('r.id IS NULL')
            ->orderBy('d.dateCreation', 'ASC'); // Plus anciennes en premier
            
        if ($limit) {
            $qb->setMaxResults($limit);
        }
        
        return $qb->getQuery()->getResult();
    }

    //    /**
    //     * @return Demande[] Returns an array of Demande objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('d')
    //            ->andWhere('d.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('d.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Demande
    //    {
    //        return $this->createQueryBuilder('d')
    //            ->andWhere('d.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}