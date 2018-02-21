<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 1/16/2018
 * Time: 2:26 PM
 */

namespace App\Controller\librarian;


use App\Entity\BookReservation;
use App\Form\AuthorType;
use App\Form\BookType;
use App\Form\GenreType;
use App\Service\ActivityManager;
use App\Service\AuthorManager;
use App\Service\BookManager;
use App\Service\BookReservationManager;
use App\Service\GenreManager;
use App\Service\LibraryManager;
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

    public function __construct(ContainerInterface $container, LibraryManager $libraryManager)
    {
        $this->user = $container->get('security.token_storage')->getToken()->getUser();
    }

    /**
     * @param Request $request
     * @param BookManager $bookManager
     * @param ActivityManager $activityManager
     *
     * @return Response
     */
    public function newBook(
        Request $request,
        BookManager $bookManager,
        ActivityManager $activityManager
    ) {
        $book = $bookManager->create();

        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $bookManager->submit($book);
            $activityManager->log($this->user, $book, 'Added a new book');

            return $this->redirectToRoute('catalog-books');
        }

        return $this->render(
            'catalog/book/new.html.twig',
            ['form' => $form->createView()]
        );
    }

    /**
     * @param Request $request
     * @param AuthorManager $authorManager
     *
     * @return RedirectResponse|Response
     */
    public function newAuthor(Request $request, AuthorManager $authorManager)
    {
        $author = $authorManager->create();

        $form = $this->createForm(AuthorType::class, $author);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $authorManager->save($author);

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
}
