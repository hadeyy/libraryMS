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
use App\Repository\AuthorRepository;
use App\Repository\BookRepository;
use App\Repository\GenreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LibraryManager extends EntityManager
{
    /** @var BookRepository */
    private $bookRepository;
    /** @var AuthorRepository */
    private $authorRepository;
    /** @var GenreRepository */
    private $genreRepository;

    public function __construct(EntityManagerInterface $manager, ContainerInterface $container)
    {
        parent::__construct($manager, $container);

        $this->bookRepository = $this->getRepository(Book::class);
        $this->authorRepository = $this->getRepository(Author::class);
        $this->genreRepository = $this->getRepository(Genre::class);
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
        return $this->authorRepository->findAll();
    }

    public function getAllGenres()
    {
        return $this->genreRepository->findAll();
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

    public function getBook(int $id)
    {
        return $this->bookRepository->find($id);
    }
}
