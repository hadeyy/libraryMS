<?php

namespace App\Repository;


use App\Entity\Book;
use App\Entity\Rating;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;

class RatingRepository extends EntityRepository
{
    /**
     * Finds all ratings by book.
     *
     * @param Book $book
     *
     * @return Rating[]|null
     */
    public function findRatingsByBook(Book $book)
    {
        return $this->createQueryBuilder('r')
            ->where('r.book = :book')
            ->setParameter('book', $book)
            ->getQuery()
            ->getResult();
    }

    /**
     * Looks for a rating by book and user.
     *
     * @param Book $book
     * @param User $user
     *
     * @return Rating|null
     */
    public function findRatingByBookAndUser(Book $book, User $user)
    {
        return $this->createQueryBuilder('r')
            ->where('r.book = :book')
            ->andWhere('r.user = :user')
            ->setParameters(['book' => $book, 'user' => $user])
            ->getQuery()
            ->getOneOrNullResult();
    }
}
