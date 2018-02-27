<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 1/9/2018
 * Time: 10:09 AM
 */

namespace App\Repository;


use Doctrine\ORM\EntityRepository;

class GenreRepository extends EntityRepository
{
    public function findAllGenresJoinedToBooks()
    {
        return $this->createQueryBuilder('g')
            ->select('g, b')
            ->innerJoin('g.books', 'b')
            ->orderBy('g.name')
            ->getQuery()
            ->getResult();
    }
}
