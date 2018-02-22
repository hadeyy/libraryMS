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
use App\Service\EntityManager;
use App\Service\GenreManager;
use App\Service\LibraryManager;
use App\Service\UserManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Security("has_role('ROLE_LIBRARIAN')")
 */
class LibraryController extends Controller
{
    private $user;
    private $bookManager;
    private $activityManager;
    private $entityManager;
    private $authorManager;

    public function __construct(
        ContainerInterface $container,
        LibraryManager $libraryManager,
        BookManager $bookManager,
        ActivityManager $activityManager,
        EntityManager $entityManager,
        AuthorManager $authorManager
    ) {
        $this->user = $container->get('security.token_storage')->getToken()->getUser();
        $this->bookManager = $bookManager;
        $this->activityManager = $activityManager;
        $this->entityManager = $entityManager;
        $this->authorManager = $authorManager;
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
     * @param AuthorManager $authorManager
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
     * @param GenreManager $genreManager
     *
     * @return RedirectResponse|Response
     */
    public function newGenre(Request $request, GenreManager $genreManager)
    {
        $genre = $genreManager->create();

        $form = $this->createForm(GenreType::class, $genre);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $genreManager->save($genre);

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
    public function reservations(BookReservationManager $brm)
    {
        return $this->render(
            'librarian/reservations.html.twig',
            [
                'reserved' => $brm->getByStatus('reserved'),
                'reading' => $brm->getByStatus('reading'),
                'returned' => $brm->getByStatus('returned'),
                'canceled' => $brm->getByStatus('canceled'),
            ]
        );
    }

    /**
     * @param BookReservation $reservation
     * @param string $status New reservation status.
     * @param BookReservationManager $brm
     *
     * @return RedirectResponse
     */
    public function updateReservationStatus(
        BookReservation $reservation,
        string $status,
        BookReservationManager $brm
    ) {
        $brm->updateStatus($reservation, $status, new \DateTime());

        return $this->redirectToRoute('reservations');
    }

    /**
     * @param UserManager $userManager
     *
     * @return Response
     */
    public function readers(UserManager $userManager)
    {
        return $this->render(
            '/librarian/readers.html.twig',
            ['readers' => $userManager->findUsersByRole('ROLE_READER')]
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
