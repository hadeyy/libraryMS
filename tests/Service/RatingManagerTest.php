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
    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function testRateCreatesNewRating()
    {
        $entityManager = $this->createMock(EntityManager::class);
        $entityManager->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(Rating::class));
        $entityManager->expects($this->once())
            ->method('flush');

        $ratingRepository = $this->createMock(RatingRepository::class);
        $ratingRepository->expects($this->once())
            ->method('findRatingByBookAndUser')
            ->with(
                $this->isInstanceOf(Book::class),
                $this->isInstanceOf(User::class)
            );

        $doctrine = $this->createMock(ManagerRegistry::class);
        $doctrine->expects($this->once())
            ->method('getManager')
            ->willReturn($entityManager);
        $doctrine->expects($this->once())
            ->method('getRepository')
            ->with($this->isType('string'))
            ->willReturn($ratingRepository);

        $ratingManager = new RatingManager($doctrine);

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

        $entityManager = $this->createMock(EntityManager::class);
        $entityManager->expects($this->once())
            ->method('flush');

        $ratingRepository = $this->createMock(RatingRepository::class);
        $ratingRepository->expects($this->once())
            ->method('findRatingByBookAndUser')
            ->with(
                $this->isInstanceOf(Book::class),
                $this->isInstanceOf(User::class)
            )
        ->willReturn($rating);

        $doctrine = $this->createMock(ManagerRegistry::class);
        $doctrine->expects($this->once())
            ->method('getManager')
            ->willReturn($entityManager);
        $doctrine->expects($this->once())
            ->method('getRepository')
            ->with($this->isType('string'))
            ->willReturn($ratingRepository);

        $ratingManager = new RatingManager($doctrine);

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

        $ratingManager = new RatingManager($doctrine);

        $average = $ratingManager->getAverageRating($book);
        $this->assertEquals(
            5, $average,
            'Average book rating result matches expected.'
        );
    }
}
