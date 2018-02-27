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
    public function findRecentActivity(int $limit)
    {
        return $this->createQueryBuilder('a')
            ->select('a, b, u')
            ->innerJoin('a.book', 'b')
            ->innerJoin('a.user', 'u')
            ->orderBy('a.time', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
