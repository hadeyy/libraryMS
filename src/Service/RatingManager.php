<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 2/15/2018
 * Time: 3:40 PM
 */

namespace App\Service;


use App\Entity\Book;
use App\Entity\Rating;
use App\Entity\User;
use Doctrine\Common\Persistence\ManagerRegistry;

class RatingManager
{
    private $em;
    private $repository;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->em = $doctrine->getManager();
        $this->repository = $doctrine->getRepository(Rating::class);
    }

    public function create(Book $book, User $user, int $value)
    {
        return new Rating($value, $book, $user);
    }

    /**
     * @param Book $book
     * @param User $user
     * @param int $value
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function rate(Book $book, User $user, int $value)
    {
        $rating = $this->checkIfRatingExists($book, $user);

        if (null === $rating) {
            $rating = new Rating($value, $book, $user);
            $this->save($rating);
        } else {
            $this->setValue($rating, $value);
            $this->saveChanges();
        }
    }

    public function getAverageRating(Book $book): float
    {
        $ratings = $this->repository->findRatingsByBook($book);

        $average = 0;
        foreach ($ratings as $rating) {
            $average += $this->getValue($rating);
        }

        return 0 === count($ratings) ? 0 : $average / count($ratings);
    }

    /**
     * @param Book $book
     * @param User $user
     *
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function checkIfRatingExists(Book $book, User $user)
    {
        return $this->repository->findRatingByBookAndUser($book, $user);
    }

    public function getValue(Rating $rating)
    {
        return $rating->getValue();
    }

    public function setValue(Rating $rating, int $value)
    {
        $rating->setValue($value);
    }

    public function save(Rating $rating)
    {
        $this->em->persist($rating);
        $this->saveChanges();
    }

    public function saveChanges()
    {
        $this->em->flush();
        $this->em->clear();
    }
}
