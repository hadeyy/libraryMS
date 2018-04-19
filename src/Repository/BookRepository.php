<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 1/9/2018
 * Time: 10:07 AM
 */

namespace App\Repository;


use App\Entity\Author;
use App\Entity\Book;
use App\Entity\Genre;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator;

class BookRepository extends EntityRepository
{
    /**
     * Finds all books and paginates the results.
     *
     * @param int $currentPage Current active page.
     * @param int $limit Maximum results per page.
     *
     * @return Paginator
     */
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

    /**
     * Finds all books by author and paginates results.
     *
     * @param Author $author
     * @param int $currentPage Current active page.
     * @param int $limit Maximum results per page.
     *
     * @return Paginator
     */
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

    /**
     * Finds all books by genre and paginates results.
     *
     * @param Genre $genre
     * @param int $currentPage Current active page.
     * @param int $limit Maximum results per page.
     *
     * @return Paginator
     */
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

    /**
     * Paginates the results of a query.
     *
     * @param Query $query Doctrine ORM query or query builder.
     * @param int $page Current active page number.
     * @param int $limit Maximum results per page.
     *
     * @return Paginator
     */
    private function paginate(Query $query, int $page, int $limit)
    {
        $paginator = new Paginator($query);

        $paginator->getQuery()
            ->setFirstResult($limit * ($page - 1))
            ->setMaxResults($limit);

        return $paginator;
    }

    /**
     * Finds all books and orders results by the number a book has been borrowed.
     *
     * @return Book[]|null
     */
    public function findAllOrderedByTimesBorrowed()
    {
        return $this->createQueryBuilder('b')
            ->select('b, a')
            ->innerJoin('b.author', 'a')
            ->orderBy('b.timesBorrowed', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Finds all books and orders results by publication date.
     *
     * @return Book[]|null
     */
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
