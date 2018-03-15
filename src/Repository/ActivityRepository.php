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
    /**
     * @param integer|null $limit
     *
     * @return mixed
     */
    public function findRecentActivity($limit = null)
    {
        return $this->createQueryBuilder('a')
            ->select('a, b, u, author')
            ->innerJoin('a.book', 'b')
            ->innerJoin('b.author', 'author')
            ->innerJoin('a.user', 'u')
            ->orderBy('a.time', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findActivityByDateLimit(string $date)
    {
        return $this->createQueryBuilder('a')
            ->select('a, b, u, author')
            ->innerJoin('a.book', 'b')
            ->innerJoin('b.author', 'author')
            ->innerJoin('a.user', 'u')
            ->where('a.time >= :date')
            ->setParameter('date', new \DateTime($date))
            ->orderBy('a.time', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param User $user
     * @param integer|null $limit
     *
     * @return mixed
     */
    public function findUserActivities(User $user, $limit = null)
    {
        return $this->createQueryBuilder('a')
            ->select('a, b, author')
            ->innerJoin('a.book', 'b')
            ->innerJoin('b.author', 'author')
            ->innerJoin('a.user', 'u')
            ->where('u = :user')
            ->setParameter('user', $user)
            ->orderBy('a.time', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param User $user
     * @param string $date
     *
     * @return mixed
     */
    public function findUserActivitiesByDateLimit(User $user, string $date)
    {
        return $this->createQueryBuilder('a')
            ->select('a, b, author')
            ->innerJoin('a.book', 'b')
            ->innerJoin('b.author', 'author')
            ->innerJoin('a.user', 'u')
            ->where('a.time >= :date')
            ->andwhere('u = :user')
            ->setParameters([
                'user' => $user,
                'date' => new \DateTime($date)
            ])
            ->orderBy('a.time', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
