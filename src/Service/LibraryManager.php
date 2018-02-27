<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 2/15/2018
 * Time: 2:24 PM
 */

namespace App\Service;


use App\Entity\Author;
use App\Entity\Book;
use App\Entity\Genre;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;

class LibraryManager
{
    private $bookRepository;
    private $authorRepository;
    private $genreRepository;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->bookRepository = $doctrine->getRepository(Book::class);
        $this->authorRepository = $doctrine->getRepository(Author::class);
        $this->genreRepository = $doctrine->getRepository(Genre::class);
    }

    public function getPopularBooks()
    {
        return $this->bookRepository->findAllOrderedByTimesBorrowed();
    }

    public function getNewestBooks()
    {
        return $this->bookRepository->findAllOrderedByPublicationDate();
    }

    /**
     * @param int $currentPage
     * @param int $booksPerPage
     *
     * @return Paginator
     */
    public function getPaginatedBookCatalog(int $currentPage, int $booksPerPage)
    {
        return $this->bookRepository->findAllAndPaginate($currentPage, $booksPerPage);
    }

    public function getAllAuthors()
    {
        return $this->authorRepository->findAllAuthorsJoinedToBooks();
    }

    public function getAllGenres()
    {
        return $this->genreRepository->findAllGenresJoinedToBooks();
    }

    /**
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
