<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 1/16/2018
 * Time: 2:26 PM
 */

namespace App\Controller\librarian;


use App\Entity\Author;
use App\Entity\Book;
use App\Entity\BookReservation;
use App\Form\AuthorType;
use App\Form\BookType;
use App\Form\GenreType;
use App\Service\ActivityManager;
use App\Service\AuthorManager;
use App\Service\BookManager;
use App\Service\BookReservationManager;
use App\Service\GenreManager;
use App\Service\UserManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

/**
 * @Security("has_role('ROLE_LIBRARIAN')")
 */
class LibraryController extends Controller
{
    private $bookManager;
    private $bookReservationManager;
    private $activityManager;
    private $authorManager;
    private $genreManager;
    private $userManager;
    private $user;

    public function __construct(
        BookManager $bookManager,
        BookReservationManager $bookReservationManager,
        ActivityManager $activityManager,
        AuthorManager $authorManager,
        GenreManager $genreManager,
        UserManager $userManager,
        TokenStorage $tokenStorage
    ) {
        $this->bookManager = $bookManager;
        $this->bookReservationManager = $bookReservationManager;
        $this->genreManager = $genreManager;
        $this->activityManager = $activityManager;
        $this->authorManager = $authorManager;
        $this->userManager = $userManager;
        $this->user = $tokenStorage->getToken()->getUser();
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function newBook(Request $request)
    {
        $book = $this->bookManager->create();

        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->bookManager->submit($book);
            $this->activityManager->log($this->user, $book, 'Added a book');

            return $this->redirectToRoute('catalog-books');
        }

        return $this->render(
            'catalog/book/new.html.twig',
            ['form' => $form->createView()]
        );
    }

    /**
     * @param Request $request
     * @param Book $book
     *
     * @return RedirectResponse|Response
     */
    public function editBook(Request $request, Book $book)
    {
        $this->bookManager->changePhotoFromPathToFile($book);

        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->bookManager->updateBook($book);
            $this->activityManager->log($this->user, $book, 'Updated a book');

            return $this->redirectToRoute('show-book', ['id' => $book->getId()]);
        }

        return $this->render('catalog/book/edit.html.twig', ['form' => $form->createView()]);
    }

    public function deleteBook(Book $book)
    {
        $this->bookManager->remove($book);

        return $this->redirectToRoute('catalog');
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse|Response
     */
    public function newAuthor(Request $request)
    {
        $author = $this->authorManager->create();

        $form = $this->createForm(AuthorType::class, $author);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->authorManager->save($author);

            return $this->redirectToRoute('catalog-books');
        }

        return $this->render(
            'catalog/author/new.html.twig',
            ['form' => $form->createView()]
        );
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse|Response
     */
    public function newGenre(Request $request)
    {
        $genre = $this->genreManager->create();

        $form = $this->createForm(GenreType::class, $genre);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->genreManager->save($genre);

            return $this->redirectToRoute('catalog-books');
        }

        return $this->render(
            'catalog/genre/new.html.twig',
            ['form' => $form->createView()]
        );
    }

    /**
     * @param BookReservationManager $brm
     *
     * @return Response
     */
    public function reservations()
    {
        return $this->render(
            'librarian/reservations.html.twig',
            [
                'reserved' => $this->bookReservationManager->getByStatus('reserved'),
                'reading' => $this->bookReservationManager->getByStatus('reading'),
                'returned' => $this->bookReservationManager->getByStatus('returned'),
                'canceled' => $this->bookReservationManager->getByStatus('canceled'),
            ]
        );
    }

    /**
     * @param BookReservation $reservation
     * @param string $status New reservation status.
     *
     * @return RedirectResponse
     */
    public function updateReservationStatus(BookReservation $reservation, string $status)
    {
        $this->bookReservationManager->updateStatus($reservation, $status, new \DateTime());

        return $this->redirectToRoute('reservations');
    }

    /**
     * @return Response
     */
    public function readers()
    {
        return $this->render(
            '/librarian/readers.html.twig',
            ['readers' => $this->userManager->findUsersByRole('ROLE_READER')]
        );
    }

    /**
     * @param Request $request
     * @param Author $author
     *
     * @return RedirectResponse|Response
     */
    public function editAuthor(Request $request, Author $author)
    {
        $this->authorManager->changePhotoFromPathToFile($author);

        $form = $this->createForm(AuthorType::class, $author);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->authorManager->updateAuthor($author);

            return $this->redirectToRoute('show-author', ['id' => $author->getId()]);
        }

        return $this->render(
            'catalog/author/edit.html.twig',
            ['form' => $form->createView()]
        );
    }

    public function deleteAuthor(Author $author)
    {
        $this->authorManager->remove($author);

        return $this->redirectToRoute('catalog');
    }
}
