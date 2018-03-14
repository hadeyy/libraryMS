<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 1/25/2018
 * Time: 1:25 PM
 */

namespace App\Controller\reader;


use App\Entity\Book;
use App\Entity\BookReservation;
use App\Form\BookReservationType;
use App\Service\ActivityManager;
use App\Service\BookReservationManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

/**
 * @Security("has_role('ROLE_READER')")
 */
class LibraryController extends Controller
{
    private $bookReservationManager;
    private $activityManager;
    private $user;

    public function __construct(
        BookReservationManager $bookReservationManager,
        ActivityManager $activityManager,
        TokenStorage $tokenStorage
    ) {
        $this->bookReservationManager = $bookReservationManager;
        $this->activityManager = $activityManager;
        $this->user = $tokenStorage->getToken()->getUser();
    }

    /**
     * @param Request $request
     * @param Book $book
     *
     * @ParamConverter("book", class="App\Entity\Book", options={"mapping": {"bookSlug": "slug"}})
     *
     * @return Response
     */
    public function reserveBook(Request $request, Book $book)
    {
        $form = $this->createForm(BookReservationType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $reservation = $this->bookReservationManager->create($book, $this->user, $form->getData());

            $this->bookReservationManager->save($reservation);
            $this->activityManager->log($this->user, $book, 'Reserved a book');

            $author = $book->getAuthor();

            return $this->redirectToRoute(
                'show-book',
                [
                    'bookSlug' => $book->getSlug(),
                    'authorSlug' => $author->getSlug(),
                ]
            );
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
     * @param BookReservation $reservation
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function cancelReservation(BookReservation $reservation)
    {
        $this->bookReservationManager->updateStatus($reservation, 'canceled', new \DateTime());

        return $this->redirectToRoute('show-user-reservations');
    }
}
