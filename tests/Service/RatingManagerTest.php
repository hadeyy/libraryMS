<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 2/20/2018
 * Time: 4:12 PM
 */

namespace App\Tests\Service;


use App\Entity\Book;
use App\Entity\Rating;
use App\Entity\User;
use App\Repository\RatingRepository;
use App\Service\RatingManager;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RatingManagerTest extends WebTestCase
{
    public function testCreate()
    {
        $book = new Book();
        $user = new User();
        $value = 7;

        $expected = new Rating($value, $book, $user);

        $ratingManager = $this->getMockBuilder(RatingManager::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['create'])
            ->getMock();

        $actual = $ratingManager->create($book, $user, $value);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function testRateCreatesNewRating()
    {
        $doctrine = $this->createMock(ManagerRegistry::class);

        $ratingManager = $this->getMockBuilder(RatingManager::class)
            ->setConstructorArgs([$doctrine])
            ->setMethodsExcept(['rate'])
            ->getMock();
        $ratingManager->expects($this->once())
            ->method('checkIfRatingExists')
            ->with($this->isInstanceOf(Book::class), $this->isInstanceOf(User::class))
            ->willReturn(null);
        $ratingManager->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(Rating::class));
        $ratingManager->expects($this->exactly(0))
            ->method('setValue');
        $ratingManager->expects($this->exactly(0))
            ->method('saveChanges');

        $ratingManager->rate(new Book(), new User(), 7);
    }

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function testRateUpdatesExistingRating()
    {
        $rating = new Rating(3, new Book(), new User());

        $doctrine = $this->createMock(ManagerRegistry::class);

        $ratingManager = $this->getMockBuilder(RatingManager::class)
            ->setConstructorArgs([$doctrine])
            ->setMethodsExcept(['rate', 'setValue'])
            ->getMock();
        $ratingManager->expects($this->once())
            ->method('checkIfRatingExists')
            ->with($this->isInstanceOf(Book::class), $this->isInstanceOf(User::class))
            ->willReturn($rating);
        $ratingManager->expects($this->once())
            ->method('saveChanges');
        $ratingManager->expects($this->exactly(0))
            ->method('save');

        $this->assertEquals(
            3, $rating->getValue(),
            'Initial rating value is 3.'
        );
        $ratingManager->rate(new Book(), new User(), 7);
        $this->assertEquals(
            7, $rating->getValue(),
            'Rating value has been successfully changed to 7.'
        );
    }

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function testCheckIfRatingExistsCallsRepository()
    {
        $ratingRepository = $this->createMock(RatingRepository::class);
        $ratingRepository->expects($this->once())
            ->method('findRatingByBookAndUser')
            ->with($this->isInstanceOf(Book::class), $this->isInstanceOf(User::class));

        $doctrine = $this->createMock(ManagerRegistry::class);
        $doctrine->expects($this->once())
            ->method('getRepository')
            ->with($this->isType('string'))
            ->willReturn($ratingRepository);

        $ratingManager = $this->getMockBuilder(RatingManager::class)
            ->setConstructorArgs([$doctrine])
            ->setMethodsExcept(['checkIfRatingExists'])
            ->getMock();

        $ratingManager->checkIfRatingExists(new Book(), new User());
    }

    public function testGetAverageRating()
    {
        $rating1 = new Rating(3, new Book(), new User());
        $rating2 = new Rating(7, new Book(), new User());
        $ratings = new ArrayCollection([$rating1, $rating2]);

        $ratingRepository = $this->createMock(RatingRepository::class);
        $ratingRepository->expects($this->once())
            ->method('findRatingsByBook')
            ->with($this->isInstanceOf(Book::class))
            ->willReturn($ratings);

        $doctrine = $this->createMock(ManagerRegistry::class);
        $doctrine->expects($this->once())
            ->method('getRepository')
            ->with($this->isType('string'))
            ->willReturn($ratingRepository);

        $ratingManager = $this->getMockBuilder(RatingManager::class)
            ->setConstructorArgs([$doctrine])
            ->setMethodsExcept(['getAverageRating', 'getValue'])
            ->getMock();

        $average = $ratingManager->getAverageRating(new Book());
        $this->assertEquals(
            5, $average,
            'Average book rating result matches expected.'
        );
    }

    public function testSavingMethodsCallEntityManagerMethods()
    {
        $entityManager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $entityManager->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(Rating::class));
        $entityManager->expects($this->exactly(2))
            ->method('flush');

        $doctrine = $this->getMockBuilder(ManagerRegistry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $doctrine->expects($this->once())
            ->method('getManager')
            ->willReturn($entityManager);

        $ratingManager = $this->getMockBuilder(RatingManager::class)
            ->setConstructorArgs([$doctrine])
            ->setMethodsExcept(['save', 'saveChanges'])
            ->getMock();

        $rating = $this->createMock(Rating::class);

        $ratingManager->save($rating);
        $ratingManager->saveChanges();
    }
}
