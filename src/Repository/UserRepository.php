<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }


    /**
     * @param \DateTime $sinceDate
     * @return float|int|mixed|string
     */
    public function findActiveUsersCreatedSince(\DateTime $sinceDate)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.createdAt >= :sinceDate')
            ->andWhere('u.active = :isActive')
            ->setParameter('sinceDate', $sinceDate)
            ->setParameter('isActive', true)
            ->getQuery()
            ->getResult();
    }

}