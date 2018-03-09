<?php

namespace App\Repository;

use App\Entity\PhoneNumber;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method PhoneNumber|null find($id, $lockMode = null, $lockVersion = null)
 * @method PhoneNumber|null findOneBy(array $criteria, array $orderBy = null)
 * @method PhoneNumber[]    findAll()
 * @method PhoneNumber[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PhoneNumberRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, PhoneNumber::class);
    }

    /*
    public function findBySomething($value)
    {
        return $this->createQueryBuilder('p')
            ->where('p.something = :value')->setParameter('value', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    public function findByUserId($userId)
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
        SELECT * FROM phone_numbers
        WHERE phone_numbers.user_id = :userId
        ';
        $stmt = $conn->prepare($sql);
        $stmt->execute(['userId' => $userId]);

        return $stmt->fetchAll();
    }


    public function DeleteByUserId($userId)
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
        DELETE FROM phone_numbers
        WHERE phone_numbers.user_id = :userId
        ';
        $stmt = $conn->prepare($sql);

        return $stmt->execute(['userId' => $userId]);
    }
}
