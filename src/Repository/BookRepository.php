<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 1/9/2018
 * Time: 10:07 AM
 */

namespace App\Repository;


use App\Entity\Author;
use App\Entity\Genre;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

class BookRepository extends EntityRepository
{
    public function findAllAndPaginate(int $currentPage = 1, int $limit = 18)
    {
        $query = $this->createQueryBuilder('b')
            ->select('b, a, g')
            ->innerJoin('b.author', 'a')
            ->innerJoin('b.genres', 'g')
            ->orderBy('b.title', 'ASC')
            ->getQuery();

        $paginator = $this->paginate($query, $currentPage, $limit);

        return $paginator;
    }

    public function findAuthorBooksAndPaginate(Author $author, int $currentPage = 1, int $limit = 18)
    {
        $query = $this->createQueryBuilder('b')
            ->select('b, a, g')
            ->innerJoin('b.author', 'a')
            ->innerJoin('b.genres', 'g')
            ->where('b.author = :author')
            ->setParameter('author', $author)
            ->getQuery();

        $paginator = $this->paginate($query, $currentPage, $limit);

        return $paginator;
    }

    public function findGenreBooksAndPaginate(Genre $genre, int $currentPage = 1, int $limit = 18)
    {
        $query = $this->createQueryBuilder('b')
            ->select('b, a, g')
            ->innerJoin('b.author', 'a')
            ->innerJoin('b.genres', 'g')
            ->where('g = :genre')
            ->setParameter('genre', $genre)
            ->getQuery();

        $paginator = $this->paginate($query, $currentPage, $limit);

        return $paginator;
    }

    private function paginate($query, $page, $limit)
    {
        $paginator = new Paginator($query);

        $paginator->getQuery()
            ->setFirstResult($limit * ($page - 1))
            ->setMaxResults($limit);

        return $paginator;
    }

    public function findAllOrderedByTimesBorrowed()
    {
        return $this->createQueryBuilder('b')
            ->select('b, a')
            ->innerJoin('b.author', 'a')
            ->orderBy('b.timesBorrowed', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findAllOrderedByPublicationDate()
    {
        return $this->createQueryBuilder('b')
            ->select('b, a')
            ->innerJoin('b.author', 'a')
            ->orderBy('b.publicationDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

}
