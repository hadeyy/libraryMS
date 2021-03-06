<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 1/9/2018
 * Time: 10:06 AM
 */

namespace App\Repository;


use App\Entity\Author;
use Doctrine\ORM\EntityRepository;

class AuthorRepository extends EntityRepository
{
    /**
     * Finds all authors.
     *
     * @return Author[]|null
     */
    public function findAllAuthorsJoinedToBooks()
    {
        return $this->createQueryBuilder('a')
            ->select('a, b')
            ->innerJoin('a.books', 'b')
            ->orderBy('a.firstName')
            ->getQuery()
            ->getResult();
    }
}
