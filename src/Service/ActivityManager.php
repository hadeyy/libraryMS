<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 2/15/2018
 * Time: 4:10 PM
 */

namespace App\Service;


use App\Entity\Activity;
use App\Entity\Book;
use App\Entity\User;
use Doctrine\Common\Persistence\ManagerRegistry;

class ActivityManager
{
    private $em;
    private $repository;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->em = $doctrine->getManager();
        $this->repository = $doctrine->getRepository(Activity::class);
    }

    /**
     * @param User $user
     * @param Book $book
     * @param string $title
     */
    public function log(User $user, Book $book, string $title)
    {
        $activity = new Activity($user, $book, $title);

        $this->save($activity);
    }

    public function findAllActivity($limit = null)
    {
        return $this->repository->findRecentActivity($limit);
    }

    public function findActivityByDateLimit(string $filter)
    {
        $dates = [
            'today' => 'today',
            'this-week' => 'monday this week',
            'this-month' => 'first day of this month',
            'this-year' => 'first day of January this year',
        ];

        return $this->repository->findActivityByDateLimit($dates[$filter]);
    }

    /**
     * @param User $user
     *
     * @return mixed
     */
    public function findUserActivity(User $user)
    {
        return $this->repository->findUserActivities($user);
    }

    /**
     * @param User $user
     * @param string $filter
     *
     * @return mixed
     */
    public function findUserActivityByDateLimit(User $user, string $filter)
    {
        $dates = [
            'today' => 'today',
            'this-week' => 'monday this week',
            'this-month' => 'first day of this month',
            'this-year' => 'first day of January this year',
        ];

        return $this->repository->findUserActivitiesByDateLimit($user, $dates[$filter]);
    }

    /**
     * @param Activity $activity The instance to make managed and persistent.
     */
    public function save(Activity $activity)
    {
        $this->em->persist($activity);
        $this->em->flush();
    }
}
