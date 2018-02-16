<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 1/8/2018
 * Time: 3:56 PM
 */

namespace App\Controller;


use App\Entity\Author;
use App\Entity\Book;
use App\Entity\Genre;
use App\Form\CommentType;
use App\Service\ActivityManager;
use App\Service\AuthorManager;
use App\Service\CommentManager;
use App\Service\GenreManager;
use App\Service\LibraryManager;
use App\Service\RatingManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends AbstractController
{
    private $libraryManager;
    private $user;

    public function __construct(LibraryManager $libraryManager, ContainerInterface $container)
    {
        $this->libraryManager = $libraryManager;
        $this->user = $container->get('security.token_storage')->getToken()->getUser();
    }

    public function index()
    {
        return $this->render(
            'index.html.twig',
            [
                'popularBooks' => $this->libraryManager->getPopularBooks(),
                'newBooks' => $this->libraryManager->getNewestBooks(),
            ]
        );
    }

    /**
     * @param int $page Result page number.
     * @param int $limit Result limit for a page.
     *
     * @return Response
     */
    public function catalog($page = 1, $limit = 18)
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
                'filter' => 'main',
            ]
        );
    }

    /**
     * @param Author $author
     * @param int $page Result page number.
     * @param int $limit Result limit for a page.
     * @param AuthorManager $authorManager
     * @param string $filter
     *
     * @ParamConverter("author", class="App\Entity\Author")
     *
     * @return Response
     */
    public function authorCatalog(
        Author $author,
        int $page = 1,
        int $limit = 12,
        AuthorManager $authorManager,
        string $filter = 'author'
    ) {
        $books = $authorManager->getPaginatedCatalog($author, $page, $limit);

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
     * @param GenreManager $genreManager
     * @param string $filter
     *
     * @ParamConverter("genre", class="App\Entity\Genre")
     *
     * @return Response
     */
    public function genreCatalog(
        Genre $genre,
        int $page = 1,
        int $limit = 12,
        GenreManager $genreManager,
        string $filter = 'genre'
    ) {
        $books = $genreManager->getPaginatedCatalog($genre, $page, $limit);

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

    /**
     * @param Request $request
     * @param Book $book
     * @param CommentManager $commentManager
     * @param RatingManager $ratingManager
     * @param ActivityManager $activityManager
     *
     * @ParamConverter("book", class="App\Entity\Book")
     *
     * @return Response
     */
    public function showBook(
        Request $request,
        Book $book,
        CommentManager $commentManager,
        RatingManager $ratingManager,
        ActivityManager $activityManager
    ) {
        $comment = $commentManager->create($this->user, $book);

//        Comment form
        $commentForm = $this->createForm(CommentType::class, $comment);
        $commentForm->handleRequest($request);
        if ($commentForm->isSubmitted() && $commentForm->isValid()) {
            $commentManager->updateRelatedEntitiesAndSave($comment, $book, $this->user);
            $activityManager->log($this->user, $book, "Commented on a book's page");
        }

//        Rating form
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
            $rating = (int)$formData['rating'];

            $ratingManager->rate($book, $rating);
            $activityManager->log($this->user, $book, 'Rated a book');
        }

        return $this->render(
            'catalog/book/show.html.twig',
            [
                'book' => $book,
                'commentForm' => $commentForm->createView(),
                'ratingForm' => $ratingForm->createView(),
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
