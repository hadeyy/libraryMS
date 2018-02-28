<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 2/28/2018
 * Time: 3:05 PM
 */

namespace App\Tests\Service;


use App\Entity\Activity;
use App\Repository\ActivityRepository;
use App\Service\ActivityManager;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ActivityManagerTest extends WebTestCase
{
    public function testGetRecentActivityCallsActivityRepository()
    {
        $activityRepository = $this->createMock(ActivityRepository::class);
        $activityRepository->expects($this->once())
            ->method('findRecentActivity')
            ->with($this->isType('int'))
            ->willReturn(new ArrayCollection());

        $doctrine = $this->createMock(ManagerRegistry::class);
        $doctrine->expects($this->once())
            ->method('getManager');
        $doctrine->expects($this->once())
            ->method('getRepository')
            ->willReturn($activityRepository);

        $activityManager = $this->getMockBuilder(ActivityManager::class)
            ->setConstructorArgs([$doctrine])
            ->setMethodsExcept(['getRecentActivity'])
            ->getMock();

        $result = $activityManager->getRecentActivity();

        $this->assertTrue(
            $result instanceof ArrayCollection,
            'Result is an instance of ArrayCollection.'
        );
    }

    public function testSaveCallsEntityManager()
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

        $activityManager = $this->getMockBuilder(ActivityManager::class)
            ->setConstructorArgs([$doctrine])
            ->setMethodsExcept(['save'])
            ->getMock();

        $activityManager->save(new Activity());
    }
}
