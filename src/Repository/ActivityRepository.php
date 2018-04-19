<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 1/9/2018
 * Time: 2:04 PM
 */

namespace App\Repository;


use App\Entity\Activity;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;

class ActivityRepository extends EntityRepository
{
    /**
     * Finds all or given number of activities and orders results by time created.
     *
     * @param integer|null $limit Maximum results.
     *
     * @return Activity[]|null
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

    /**
     * Finds all or given number of activities
     * that have been created on or after the date
     * and orders results by time created.
     *
     * @param string $date Earliest date created.
     *
     * @return Activity[]|null
     */
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
     * Finds all or given number of activities by user
     * and orders results by time created.
     *
     * @param User $user
     * @param integer|null $limit Maximum results.
     *
     * @return Activity[]|null
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
     * Finds all activities that have been created on or after the date by user
     * and orders results by time created.
     *
     * @param User $user
     * @param string $date Earliest date created.
     *
     * @return Activity[]|null
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
