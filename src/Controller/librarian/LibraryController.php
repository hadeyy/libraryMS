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
use App\Form\BookEditType;
use App\Form\BookType;
use App\Form\GenreType;
use App\Service\ActivityManager;
use App\Service\AuthorManager;
use App\Service\BookManager;
use App\Service\BookReservationManager;
use App\Service\GenreManager;
use App\Service\UserManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
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
        $form = $this->createForm(BookType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $book = $this->bookManager->create($form->getData());

            $this->bookManager->save($book);
            $this->activityManager->log($this->user, $book, 'Added a book');

            return $this->redirectToRoute('show-catalog');
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
     * @ParamConverter("book", class="App\Entity\Book", options={"mapping": {"bookSlug": "slug"}})
     *
     * @return RedirectResponse|Response
     */
    public function editBook(Request $request, Book $book)
    {
        $data = $this->bookManager->createArrayFromBook($book);

        $form = $this->createForm(BookEditType::class, $data);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->bookManager->updateBook($book, $form->getData());
            $this->activityManager->log($this->user, $book, 'Updated a book');

            $author = $book->getAuthor();

            return $this->redirectToRoute(
                'show-book',
                [
                    'bookSlug' => $book->getSlug(),
                    'authorSlug' => $author->getSlug(),
                ]
            );
        }

        return $this->render('catalog/book/edit.html.twig', ['form' => $form->createView()]);
    }

    public function deleteBook(Book $book)
    {
        $this->bookManager->remove($book);

        return $this->redirectToRoute('show-catalog');
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse|Response
     */
    public function newAuthor(Request $request)
    {
        $form = $this->createForm(AuthorType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $author = $this->authorManager->create($form->getData());
            $this->authorManager->save($author);

            return $this->redirectToRoute('show-catalog');
        }

        return $this->render(
            'catalog/author/new.html.twig',
            ['form' => $form->createView()]
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
        $data = $this->authorManager->createArrayFromAuthor($author);

        $form = $this->createForm(AuthorType::class, $data);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->authorManager->updateAuthor($author, $data);

            return $this->redirectToRoute('show-author', ['slug' => $author->getSlug()]);
        }

        return $this->render(
            'catalog/author/edit.html.twig',
            ['form' => $form->createView()]
        );
    }

    public function deleteAuthor(Author $author)
    {
        $this->authorManager->remove($author);

        return $this->redirectToRoute('show-catalog');
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse|Response
     */
    public function newGenre(Request $request)
    {
        $form = $this->createForm(GenreType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $genre = $this->genreManager->create($form->getData());
            $this->genreManager->save($genre);

            return $this->redirectToRoute('show-catalog');
        }

        return $this->render(
            'catalog/genre/new.html.twig',
            ['form' => $form->createView()]
        );
    }

    /**
     * @return Response
     */
    public function showReservations()
    {
        return $this->render(
            'librarian/reservations.html.twig',
            [
                'reserved' => $this->bookReservationManager->findByStatus('reserved'),
                'reading' => $this->bookReservationManager->findByStatus('reading'),
                'returned' => $this->bookReservationManager->findByStatus('returned'),
                'canceled' => $this->bookReservationManager->findByStatus('canceled'),
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

        return $this->redirectToRoute('show-reservations');
    }

    /**
     * @return Response
     */
    public function showReaders()
    {
        return $this->render(
            'librarian/users.html.twig',
            ['users' => $this->userManager->findUsersByRole('ROLE_READER')]
        );
    }
}
