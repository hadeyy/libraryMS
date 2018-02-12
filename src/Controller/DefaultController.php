<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 1/8/2018
 * Time: 3:56 PM
 */

namespace App\Controller;


use App\Entity\Activity;
use App\Entity\Author;
use App\Entity\Book;
use App\Entity\Comment;
use App\Entity\Genre;
use App\Entity\User;
use App\Form\CommentType;
use App\Repository\BookRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="index")
     */
    public function index()
    {
        /** @var BookRepository $bookRepo */
        $bookRepo = $this->getDoctrine()->getRepository(Book::class);

        $popularBooks = $bookRepo->findAllOrderedByTimesBorrowed();
        $newBooks = $bookRepo->findAllOrderedByPublicationDate();

        return $this->render(
            'index.html.twig',
            [
                'popularBooks' => $popularBooks,
                'newBooks' => $newBooks,
            ]
        );
    }

    /**
     * @Route(
     *     "/catalog/page/{page}",
     *     name="catalog",
     *     requirements={"page"="\d+"},
     *     defaults={"page":"1"}
     * )
     * @Route(
     *     "/catalog/books/page/{page}",
     *     name="catalog-books",
     *     requirements={"page"="\d+"},
     *     defaults={"page":"1"}
     * )
     *
     * @param int $page Result page number.
     * @param int $limit Result limit for a page.
     *
     * @return Response
     */
    public function catalog($page = 1, $limit = 18)
    {
        /** @var BookRepository $bookRepo */
        $bookRepo = $this->getDoctrine()->getRepository(Book::class);
        $authorRepo = $this->getDoctrine()->getRepository(Author::class);
        $genreRepo = $this->getDoctrine()->getRepository(Genre::class);

        $books = $bookRepo->findAllAndPaginate($page, $limit);
        $allBooks = $books->count();
        $maxPages = ceil($allBooks / $limit);

        return $this->render('catalog/index.html.twig', [
            'books' => $books,
            'maxPages' => $maxPages,
            'currentPage' => $page,
            'authors' => $authorRepo->findAll(),
            'genres' => $genreRepo->findAll(),
            'filter' => 'main',
        ]);
    }

    /**
     * @Route(
     *     "/catalog/author/{id}/page/{page}",
     *     name="books-by-author",
     *     requirements={"id"="\d+", "page"="\d+"},
     *     defaults={"page":"1"}
     * )
     *
     * @param int $id Author id.
     * @param int $page Result page number.
     * @param int $limit Result limit for a page.
     *
     * @return Response
     */
    public function authorCatalog(int $id, $page = 1, $limit = 12)
    {
        /** @var BookRepository $bookRepo */
        $bookRepo = $this->getDoctrine()->getRepository(Book::class);
        $authorRepo = $this->getDoctrine()->getRepository(Author::class);
        $genreRepo = $this->getDoctrine()->getRepository(Genre::class);

        /** @var Author $author */
        $author = $authorRepo->find($id);

        $books = $bookRepo->findAuthorBooksAndPaginate($author, $page, $limit);
        $allBooks = $author->getBooks()->count();
        $maxPages = ceil($allBooks / $limit);

        return $this->render('catalog/_books_by_author.html.twig', [
            'authors' => $authorRepo->findAll(),
            'genres' => $genreRepo->findAll(),
            'author' => $author,
            'books' => $books,
            'maxPages' => $maxPages,
            'currentPage' => $page,
            'filter' => 'author',
        ]);
    }

    /**
     * @Route(
     *     "/catalog/genre/{id}/page/{page}",
     *     name="books-by-genre",
     *     requirements={"id"="\d+", "page"="\d+"},
     *     defaults={"page":"1"}
     *
     * )
     *
     * @param int $id Genre id.
     * @param int $page Result page number.
     * @param int $limit Result limit for a page.
     *
     * @return Response
     */
    public function genreCatalog(int $id, $page = 1, $limit = 12)
    {
        /** @var BookRepository $bookRepo */
        $bookRepo = $this->getDoctrine()->getRepository(Book::class);
        $authorRepo = $this->getDoctrine()->getRepository(Author::class);
        $genreRepo = $this->getDoctrine()->getRepository(Genre::class);

        /** @var Genre $genre */
        $genre = $genreRepo->find($id);

        $books = $bookRepo->findGenreBooksAndPaginate($genre, $page, $limit);
        $allBooks = $genre->getBooks()->count();
        $maxPages = ceil($allBooks / $limit);

        return $this->render('catalog/_books_by_genre.html.twig', [
            'authors' => $authorRepo->findAll(),
            'genres' => $genreRepo->findAll(),
            'genre' => $genre,
            'books' => $books,
            'maxPages' => $maxPages,
            'currentPage' => $page,
            'filter' => 'genre',
        ]);
    }

    /**
     * @Route("/catalog/books/{id}", name="show-book", requirements={"id"="\d+"})
     *
     * @param Request $request
     * @param int $id Book id.
     *
     * @return Response
     */
    public function showBook(Request $request, int $id): Response
    {
        $bookRepo = $this->getDoctrine()->getRepository(Book::class);
        /** @var Book $book */
        $book = $bookRepo->find($id);

        $comment = new Comment();
        /** @var User $user */
        $user = $this->getUser();
        $comment->setAuthor($user);
        $comment->setBook($book);

//        Comment form
        $commentForm = $this->createForm(CommentType::class, $comment);
        $commentForm->handleRequest($request);
        if ($commentForm->isSubmitted() && $commentForm->isValid()) {
            $book->addComment($comment);
            $user->addComment($comment);

            $em = $this->getDoctrine()->getManager();
            $em->persist($comment);
            $em->flush();
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

            $book->addRating($rating);
            $activity = $this->createRatingActivity($book, $user);

            $em = $this->getDoctrine()->getManager();
            $em->persist($activity);
            $em->flush();
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

    public function createRatingActivity(Book $book, User $user)
    {
        /** @var Activity $activity */
        $activity = new Activity();
        $activity->setBook($book);
        $activity->setUser($user);
        $activity->setTitle('Rated a book');
        $book->addActivity($activity);
        $user->addActivity($activity);

        return $activity;
    }

    /**
     * @Route("catalog/authors/{id}", name="show-author", requirements={"id"="\d+"})
     *
     * @param int $id Author id.
     *
     * @return Response
     */
    public function showAuthor(int $id)
    {
        $authorRepo = $this->getDoctrine()->getRepository(Author::class);
        /** @var Author $author */
        $author = $authorRepo->find($id);

        return $this->render(
            'catalog/author/show.html.twig',
            [
                'author' => $author,
            ]
        );
    }
}
