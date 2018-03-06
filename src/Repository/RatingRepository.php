<?php

namespace App\Repository;


use App\Entity\Book;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;

class RatingRepository extends EntityRepository
{
    public function findRatingsByBook(Book $book)
    {
        return $this->createQueryBuilder('r')
            ->where('r.book = :book')
            ->setParameter('book', $book)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Book $book
     * @param User $user
     *
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
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
