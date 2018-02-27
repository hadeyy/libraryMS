<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 1/9/2018
 * Time: 10:06 AM
 */

namespace App\Repository;


use Doctrine\ORM\EntityRepository;

class AuthorRepository extends EntityRepository
{
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
