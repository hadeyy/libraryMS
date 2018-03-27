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
     * Creates a new instance of Activity and saves it to the database.
     *
     * @param User $user User who performed the activity.
     * @param Book $book Book on which the activity was performed.
     * @param string $title Short activity description.
     *
     * @return void
     */
    public function log(User $user, Book $book, string $title)
    {
        $activity = new Activity($user, $book, $title);

        $this->save($activity);
    }

    /**
     * Looks for all activities in the database.
     *
     * @param integer|null $limit The maximum number of results to retrieve.
     *
     * @return Activity[]|null
     */
    public function findAllActivity($limit = null)
    {
        return $this->repository->findRecentActivity($limit);
    }

    /**
     * Looks for all activities that have been created after the given date.
     *
     * @param string $filter Date filter.
     *
     * @return Activity[]|null
     */
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
     * Looks for all activities that have been performed by the given user.
     *
     * @param User $user
     *
     * @return Activity[]|null
     */
    public function findUserActivity(User $user)
    {
        return $this->repository->findUserActivities($user);
    }

    /**
     * Looks for all activities that have been performed by
     * the given user and have been created after the given date.
     *
     * @param User $user
     * @param string $filter Date filter.
     *
     * @return Activity[]|null
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
     * Calls entity manager to make the instance managed and persistent and
     * to save all changes made to objects to the database.
     *
     * @param Activity $activity
     *
     * @return void
     */
    public function save(Activity $activity)
    {
        $this->em->persist($activity);
        $this->em->flush();
    }
}
