<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 1/9/2018
 * Time: 2:04 PM
 */

namespace App\Repository;


use App\Entity\User;
use Doctrine\ORM\EntityRepository;

class ActivityRepository extends EntityRepository
{
    public function findAllUserActivities(User $user)
    {
        $em = $this->getEntityManager();

        $query = $em->createQuery(
            'SELECT a
          FROM App\Entity\Activity a
          WHERE a.user = :user
          ORDER BY a.time DESC'
        )->setParameter('user', $user);

        return $query->execute();
    }

    public function findRecentActivity(int $limit)
    {
        return $this->createQueryBuilder('a')
            ->orderBy('a.time', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
