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
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

/**
 * @Security("has_role('ROLE_READER')")
 */
class LibraryController extends Controller
{
    private $libraryManager;
    private $bookManager;
    private $bookReservationManager;
    private $activityManager;
    private $user;

    public function __construct(
        LibraryManager $libraryManager,
        BookManager $bookManager,
        BookReservationManager $bookReservationManager,
        ActivityManager $activityManager,
        TokenStorage $tokenStorage
    ) {
        $this->libraryManager = $libraryManager;
        $this->bookManager = $bookManager;
        $this->bookReservationManager = $bookReservationManager;
        $this->activityManager = $activityManager;
        $this->user = $tokenStorage->getToken()->getUser();
    }

    /**
     * @param Request $request
     * @param Book $book
     *
     * @ParamConverter("book", class="App\Entity\Book")
     *
     * @return Response
     */
    public function reserveBook(Request $request, Book $book)
    {
        $reservation = $this->bookReservationManager->create($book, $this->user);

        $form = $this->createForm(BookReservationType::class, $reservation);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->bookReservationManager->reserve($reservation, $book, $this->user);
            $this->activityManager->log($this->user, $book, 'Reserved a book');

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
     *
     * @ParamConverter("book", class="App\Entity\Book")
     *
     * @return RedirectResponse
     */
    public function toggleFavorite(Book $book)
    {
        $this->bookManager->toggleFavorite($this->user, $book);

        return $this->redirectToRoute('show-book', ['id' => $book->getId()]);
    }
}
