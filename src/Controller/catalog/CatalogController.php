<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 2/26/2018
 * Time: 3:54 PM
 */

namespace App\Controller\catalog;


use App\Entity\Author;
use App\Entity\Genre;
use App\Service\AuthorManager;
use App\Service\GenreManager;
use App\Service\LibraryManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class CatalogController extends AbstractController
{
    private $libraryManager;
    private $authorManager;
    private $genreManager;

    public function __construct(
        LibraryManager $libraryManager,
        AuthorManager $authorManager,
        GenreManager $genreManager
    ) {
        $this->libraryManager = $libraryManager;
        $this->authorManager = $authorManager;
        $this->genreManager = $genreManager;
    }

    /**
     * @param int $page Result page number.
     * @param int $limit Result limit for a page.
     * @param string $filter
     *
     * @return Response
     */
    public function showCatalog(int $page = 1, int $limit = 18, string $filter = 'main')
    {
        $books = $this->libraryManager->getPaginatedBookCatalog($page, $limit);

        return $this->render(
            'catalog/index.html.twig',
            [
                'books' => $books,
                'maxPages' => $this->libraryManager->getMaxPages($books, $limit),
                'currentPage' => $page,
                'authors' => $this->libraryManager->getAllAuthors(),
                'genres' => $this->libraryManager->getAllGenres(),
                'filter' => $filter,
            ]
        );
    }

    /**
     * @param Author $author
     * @param int $page Result page number.
     * @param int $limit Result limit for a page.
     * @param string $filter
     *
     * @ParamConverter("author", class="App\Entity\Author")
     *
     * @return Response
     */
    public function showAuthorCatalog(
        Author $author,
        int $page = 1,
        int $limit = 12,
        string $filter = 'author'
    ) {
        $books = $this->authorManager->getPaginatedCatalog($author, $page, $limit);

        return $this->render(
            'catalog/_books_by_author.html.twig',
            [
                'authors' => $this->libraryManager->getAllAuthors(),
                'genres' => $this->libraryManager->getAllGenres(),
                'author' => $author,
                'books' => $books,
                'maxPages' => $this->libraryManager->getMaxPages($books, $limit),
                'currentPage' => $page,
                'filter' => $filter,
            ]
        );
    }

    /**
     * @param Genre $genre
     * @param int $page Result page number.
     * @param int $limit Result limit for a page.
     * @param string $filter
     *
     * @ParamConverter("genre", class="App\Entity\Genre")
     *
     * @return Response
     */
    public function showGenreCatalog(
        Genre $genre,
        int $page = 1,
        int $limit = 12,
        string $filter = 'genre'
    ) {
        $books = $this->genreManager->getPaginatedCatalog($genre, $page, $limit);

        return $this->render('catalog/_books_by_genre.html.twig', [
            'authors' => $this->libraryManager->getAllAuthors(),
            'genres' => $this->libraryManager->getAllGenres(),
            'genre' => $genre,
            'books' => $books,
            'maxPages' => $this->libraryManager->getMaxPages($books, $limit),
            'currentPage' => $page,
            'filter' => $filter,
        ]);
    }
}
