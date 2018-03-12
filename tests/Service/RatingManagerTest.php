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
        $book = $this->createMock(Book::class);
        $user = $this->createMock(User::class);
        $value = 7;

        $ratingManager = $this->getMockBuilder(RatingManager::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['create'])
            ->getMock();

        $rating = $ratingManager->create($book, $user, $value);
        $this->assertTrue(
            $rating instanceof Rating,
            'Result is an instance of Rating class.'
        );
        $this->assertEquals($book, $rating->getBook());
        $this->assertEquals($user, $rating->getRater());
        $this->assertEquals($value, $rating->getValue());
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

        $book = $this->createMock(Book::class);
        $user = $this->createMock(User::class);

        $ratingManager->rate($book, $user, 7);
    }

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function testRateUpdatesExistingRating()
    {
        $book = $this->createMock(Book::class);
        $user = $this->createMock(User::class);

        $rating = new Rating(3, $book, $user);

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
        $ratingManager->rate($book, $user, 7);
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

        $book = $this->createMock(Book::class);
        $user = $this->createMock(User::class);

        $ratingManager->checkIfRatingExists($book, $user);
    }

    public function testGetAverageRating()
    {
        $book = $this->createMock(Book::class);
        $user = $this->createMock(User::class);

        $rating1 = new Rating(3, $book, $user);
        $rating2 = new Rating(7, $book, $user);
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

        $average = $ratingManager->getAverageRating($book);
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
        $entityManager->expects($this->exactly(2))
            ->method('clear');

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
