<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 2/28/2018
 * Time: 3:05 PM
 */

namespace App\Tests\Service;


use App\Entity\Activity;
use App\Entity\Book;
use App\Entity\User;
use App\Repository\ActivityRepository;
use App\Service\ActivityManager;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ActivityManagerTest extends WebTestCase
{
    private $user;
    private $book;
    private $activities;

    public function setUp()
    {
        $this->user = $this->createMock(User::class);
        $this->book = $this->createMock(Book::class);

        $this->activities = new ArrayCollection([
            new Activity($this->user, $this->book, 'activity 1'),
            new Activity($this->user, $this->book, 'activity 2'),
        ]);
    }

    public function testLog()
    {
        $entityManager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $entityManager->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(Activity::class));
        $entityManager->expects($this->once())
            ->method('flush');

        $doctrine = $this->createMock(ManagerRegistry::class);
        $doctrine->expects($this->once())
            ->method('getManager')
            ->willReturn($entityManager);

        $activityManager = new ActivityManager($doctrine);

        $user = $this->createMock(User::class);
        $book = $this->createMock(Book::class);

        $activityManager->log($user, $book, 'title');
    }

    public function testGetRecentActivity()
    {
        $activityRepository = $this->createMock(ActivityRepository::class);
        $activityRepository->expects($this->once())
            ->method('findRecentActivity')
            ->with($this->isType('int'))
            ->willReturn($this->activities);

        $doctrine = $this->createMock(ManagerRegistry::class);
        $doctrine->expects($this->once())
            ->method('getRepository')
            ->willReturn($activityRepository);

        $activityManager = new ActivityManager($doctrine);

        $result = $activityManager->getRecentActivity(2);

        $this->assertEquals($this->activities, $result);
    }

    public function testFindUserActivity()
    {
        $activityRepository = $this->createMock(ActivityRepository::class);
        $activityRepository->expects($this->once())
            ->method('findUserActivities')
            ->with($this->isInstanceOf(User::class))
            ->willReturn($this->activities);

        $doctrine = $this->createMock(ManagerRegistry::class);
        $doctrine->expects($this->once())
            ->method('getRepository')
            ->willReturn($activityRepository);

        $activityManager = new ActivityManager($doctrine);

        $result = $activityManager->findUserActivity($this->user);

        $this->assertEquals($this->activities, $result);
    }

    /**
     * @param $filter
     * @param $date
     * @dataProvider dateFilterProvider
     */
    public function testFindUserActivityByDateLimit($filter, $date)
    {
        $activityRepository = $this->createMock(ActivityRepository::class);
        $activityRepository->expects($this->once())
            ->method('findUserActivitiesByDateLimit')
            ->with($this->isInstanceOf(User::class), $date)
            ->willReturn($this->activities);

        $doctrine = $this->createMock(ManagerRegistry::class);
        $doctrine->expects($this->once())
            ->method('getRepository')
            ->willReturn($activityRepository);

        $activityManager = new ActivityManager($doctrine);

        $result = $activityManager->findUserActivityByDateLimit($this->user, $filter);

        $this->assertEquals($this->activities, $result);
    }

    public function dateFilterProvider()
    {
        return [
            ['today', 'today'],
            ['this-week', 'monday this week'],
            ['this-month', 'first day of this month'],
            ['this-year', 'first day of January this year'],
        ];
    }
}
