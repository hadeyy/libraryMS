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
use App\Service\CatalogManager;
use App\Service\GenreManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class CatalogController extends AbstractController
{
    private $authorManager;
    private $genreManager;
    private $catalogManager;

    public function __construct(
        AuthorManager $authorManager,
        GenreManager $genreManager,
        CatalogManager $catalogManager
    ) {
        $this->authorManager = $authorManager;
        $this->genreManager = $genreManager;
        $this->catalogManager = $catalogManager;
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
        $books = $this->catalogManager->getPaginatedBookCatalog($page, $limit);

        return $this->render(
            'catalog/index.html.twig',
            [
                'books' => $books,
                'maxPages' => $this->catalogManager->getMaxPages($books, $limit),
                'currentPage' => $page,
                'authors' => $this->authorManager->findAllAuthors(),
                'genres' => $this->genreManager->findAllGenres(),
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
        $books = $this->catalogManager->getPaginatedAuthorCatalog($author, $page, $limit);

        return $this->render(
            'catalog/_books_by_author.html.twig',
            [
                'authors' => $this->authorManager->findAllAuthors(),
                'genres' => $this->genreManager->findAllGenres(),
                'author' => $author,
                'books' => $books,
                'maxPages' => $this->catalogManager->getMaxPages($books, $limit),
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
        $books = $this->catalogManager->getPaginatedGenreCatalog($genre, $page, $limit);

        return $this->render('catalog/_books_by_genre.html.twig', [
            'authors' => $this->authorManager->findAllAuthors(),
            'genres' => $this->genreManager->findAllGenres(),
            'genre' => $genre,
            'books' => $books,
            'maxPages' => $this->catalogManager->getMaxPages($books, $limit),
            'currentPage' => $page,
            'filter' => $filter,
        ]);
    }
}
