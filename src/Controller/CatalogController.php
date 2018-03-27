<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 2/26/2018
 * Time: 3:54 PM
 */

namespace App\Controller;


use App\Entity\Author;
use App\Entity\Book;
use App\Entity\Genre;
use App\Entity\User;
use App\Form\CommentType;
use App\Service\ActivityManager;
use App\Service\AuthorManager;
use App\Service\BookReservationManager;
use App\Service\CatalogManager;
use App\Service\CommentManager;
use App\Service\GenreManager;
use App\Service\RatingManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CatalogController extends AbstractController
{
    private $authorManager;
    private $genreManager;
    private $catalogManager;
    private $commentManager;
    private $activityManager;
    private $ratingManager;
    private $bookReservationManager;
    private $user;

    public function __construct(
        AuthorManager $authorManager,
        GenreManager $genreManager,
        CatalogManager $catalogManager,
        CommentManager $commentManager,
        ActivityManager $activityManager,
        RatingManager $ratingManager,
        BookReservationManager $bookReservationManager,
        TokenStorageInterface $tokenStorage
    )
    {
        $this->authorManager = $authorManager;
        $this->genreManager = $genreManager;
        $this->catalogManager = $catalogManager;
        $this->commentManager = $commentManager;
        $this->activityManager = $activityManager;
        $this->ratingManager = $ratingManager;
        $this->bookReservationManager = $bookReservationManager;
        $this->user = $tokenStorage->getToken()->getUser();
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
    )
    {
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
    )
    {
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

    /**
     * @param Request $request
     * @param Book $book
     *
     * @ParamConverter("book", class="App\Entity\Book", options={"mapping": {"bookSlug": "slug"}})
     *
     * @return Response
     */
    public function showBook(Request $request, Book $book)
    {
        if ($this->user instanceof User) {
            /** Comment form */
            $commentForm = $this->createForm(CommentType::class);
            $commentForm->handleRequest($request);
            if ($commentForm->isSubmitted() && $commentForm->isValid()) {
                $data = $commentForm->getData();
                $this->commentManager->create($this->user, $book, $data['content']);

                $this->activityManager->log($this->user, $book, "Commented on a book's page");
            }

            /** Rating form */
            $defaultData = ['message' => 'Select rating'];
            $ratingForm = $this->createFormBuilder($defaultData)
                ->add('rating', ChoiceType::class, [
                    'placeholder' => '- Choose rating -',
                    'choices' => [
                        '5' => '5',
                        '4' => '4',
                        '3' => '3',
                        '2' => '2',
                        '1' => '1',
                    ],
                ])
                ->getForm();

            $ratingForm->handleRequest($request);
            if ($ratingForm->isSubmitted() && $ratingForm->isValid()) {
                $formData = $ratingForm->getData();
                $value = (int)$formData['rating'];

                $this->ratingManager->rate($book, $this->user, $value);
                $this->activityManager->log($this->user, $book, 'Rated a book');
            }

            $isAvailable = $this->bookReservationManager->checkIfIsAvailable($book, $this->user);
        }

        return $this->render(
            'catalog/book/show.html.twig',
            [
                'book' => $book,
                'bookRating' => $this->ratingManager->getAverageRating($book),
                'commentForm' => isset($commentForm) ? $commentForm->createView() : null,
                'ratingForm' => isset($ratingForm) ? $ratingForm->createView() : null,
                'isAvailable' => isset($isAvailable) ? $isAvailable : null,
            ]
        );
    }

    /**
     * @param Author $author
     *
     * @ParamConverter("author", class="App\Entity\Author")
     *
     * @return Response
     */
    public function showAuthor(Author $author)
    {
        return $this->render(
            'catalog/author/show.html.twig',
            [
                'author' => $author,
            ]
        );
    }
}
