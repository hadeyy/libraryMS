<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 1/25/2018
 * Time: 1:25 PM
 */

namespace App\Controller\reader;


use App\Entity\Book;
use App\Form\BookReservationType;
use App\Service\ActivityManager;
use App\Service\BookManager;
use App\Service\BookReservationManager;
use App\Service\LibraryManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Security("has_role('ROLE_READER')")
 */
class LibraryController extends Controller
{
    private $user;
    private $libraryManager;

    public function __construct(ContainerInterface $container, LibraryManager $libraryManager)
    {
        $this->user = $container->get('security.token_storage')->getToken()->getUser();
        $this->libraryManager = $libraryManager;
    }

    /**
     * @param Request $request
     * @param Book $book
     * @param BookReservationManager $brm
     * @param ActivityManager $activityManager
     *
     * @ParamConverter("book", class="App\Entity\Book")
     *
     * @return Response
     */
    public function reserveBook(
        Request $request,
        Book $book,
        BookReservationManager $brm,
        ActivityManager $activityManager
    ) {
        $reservation = $brm->createReservation($book, $this->user);

        $form = $this->createForm(BookReservationType::class, $reservation);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $brm->reserve($reservation, $book, $this->user);
            $activityManager->log($this->user, $book, 'Reserved a book');

            return $this->redirectToRoute('show-book', ['id' => $book->getId()]);
        }

        return $this->render(
            'catalog/book/reservation.html.twig',
            [
                'book' => $book,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @param Book $book
     * @param BookManager $bookManager
     *
     * @ParamConverter("book", class="App\Entity\Book")
     *
     * @return RedirectResponse
     */
    public function toggleFavorite(Book $book, BookManager $bookManager)
    {
        $bookManager->toggleFavorite($this->user, $book);

        return $this->redirectToRoute('show-book', ['id' => $book->getId()]);
    }
}
