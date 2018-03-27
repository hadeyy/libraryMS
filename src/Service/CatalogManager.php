<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 3/20/2018
 * Time: 11:22 AM
 */

namespace App\Service;


use App\Entity\Author;
use App\Entity\Book;
use App\Entity\Genre;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;

class CatalogManager
{
    private $bookRepository;

    public function __construct(
        ManagerRegistry $doctrine
    )
    {
        $this->bookRepository = $doctrine->getRepository(Book::class);
    }

    /**
     * Looks for all books and paginates the results.
     *
     * @param int $currentPage Current page number.
     * @param int $booksPerPage Number of results to display in a page.
     *
     * @return Paginator
     */
    public function getPaginatedBookCatalog(int $currentPage, int $booksPerPage)
    {
        return $this->bookRepository->findAllAndPaginate($currentPage, $booksPerPage);
    }

    /**
     * Looks for all books that have the given author and paginates the results.
     *
     * @param Author $author
     * @param int $currentPage Current page number.
     * @param int $booksPerPage Number of results to display in a page.
     *
     * @return Paginator
     */
    public function getPaginatedAuthorCatalog(Author $author, int $currentPage, int $booksPerPage)
    {
        return $this->bookRepository->findAuthorBooksAndPaginate($author, $currentPage, $booksPerPage);
    }

    /**
     * Looks for all books that have the given genre and paginates the results.
     *
     * @param Genre $genre
     * @param int $currentPage Current page number.
     * @param int $booksPerPage Number of results to display in a page.
     *
     * @return Paginator
     */
    public function getPaginatedGenreCatalog(Genre $genre, int $currentPage, int $booksPerPage)
    {
        return $this->bookRepository->findGenreBooksAndPaginate($genre, $currentPage, $booksPerPage);
    }

    /**
     * Returns the maximum number of pages based on number of books and book limit per page.
     *
     * @param Paginator $books All books paginated.
     * @param int $limit Book limit per page.
     *
     * @return int Maximum number of pages.
     */
    public function getMaxPages(Paginator $books, int $limit)
    {
        return ceil($books->count() / $limit);
    }
}
