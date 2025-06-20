<?php

namespace App\Repository;

use App\Entity\Establishment;
use App\Entity\TypeEstablishment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Establishment>
 */
class EstablishmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Establishment::class);
    }

  public function findByLocationName(string $locationName): array
{
    return $this->createQueryBuilder('e')
        ->where('e.address LIKE :address')
        ->setParameter('address', '%'.$locationName.'%')
        ->getQuery()
        ->getResult();
}

public function findByTypeAndLocation(TypeEstablishment $type, string $location): array
{
    return $this->createQueryBuilder('e')
        ->where('e.type = :type')
        ->andWhere('e.address LIKE :address')
        ->setParameter('type', $type)
        ->setParameter('address', '%'.$location.'%')
        ->getQuery()
        ->getResult();
}

public function save(Establishment $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

// Ajoutez ces méthodes à EstablishmentRepository.php

public function findBySearchQuery(string $query, int $limit = 20): array
{
    return $this->createQueryBuilder('e')
        ->where('e.name LIKE :query OR e.address LIKE :query OR e.description LIKE :query')
        ->setParameter('query', '%'.$query.'%')
        ->orderBy('e.name', 'ASC')
        ->setMaxResults($limit)
        ->getQuery()
        ->getResult();
}

// Dans EstablishmentRepository.php

public function findNearby(float $latitude, float $longitude, float $radius, int $limit = 20)
{
    // Pour MySQL 5.7+ ou MariaDB 10.2+
    return $this->createQueryBuilder('e')
        ->select('e',
            '(6371 * ACOS(COS(RADIANS(:lat)) * COS(RADIANS(JSON_EXTRACT(e.location, \'$.latitude\'))) *
            COS(RADIANS(JSON_EXTRACT(e.location, \'$.longitude\') - RADIANS(:lng)) +
            SIN(RADIANS(:lat)) * SIN(RADIANS(JSON_EXTRACT(e.location, \'$.latitude\')))) AS distance'
        )
        ->setParameter('lat', $latitude)
        ->setParameter('lng', $longitude)
        ->having('distance < :radius')
        ->setParameter('radius', $radius)
        ->orderBy('distance', 'ASC')
        ->setMaxResults($limit)
        ->getQuery()
        ->getResult();
}

//    /**
//     * @return Establishment[] Returns an array of Establishment objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('e.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Establishment
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
