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
use App\Repository\ActivityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ActivityManager extends EntityManager
{
    /** @var ActivityRepository */
    private $activityRepository;

    public function __construct(EntityManagerInterface $manager, ContainerInterface $container)
    {
        parent::__construct($manager, $container);

        $this->activityRepository = $this->getRepository(Activity::class);
    }

    public function log(User $user, Book $book, string $title)
    {
        $activity = new Activity();

        $activity->setUser($user);
        $activity->setBook($book);
        $activity->setTitle($title);
        $book->addActivity($activity);
        $user->addActivity($activity);

        $this->save($activity);
    }

    public function getRecentActivity(int $limit = 7)
    {
        return $this->activityRepository->findRecentActivity($limit);
    }
}
