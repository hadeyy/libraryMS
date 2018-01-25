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
use App\Entity\User;
use App\Form\BookReservationType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Security("has_role('ROLE_READER')")
 */
class LibraryController extends Controller
{
    /**
     * @Route("/catalog/books/{id}/reserve", name="reserve-book", requirements={"id"="\d+"})
     *
     * @param Request $request
     * @param int $id Book id.
     *
     * @return Response
     */
    public function reserveBook(Request $request, int $id)
    {
        $bookRepo = $this->getDoctrine()->getRepository(Book::class);
        /** @var Book $book */
        $book = $bookRepo->find($id);
        /** @var User $reader */
        $reader = $this->getUser();

        $reservation = new BookReservation();
        $reservation->setBook($book);
        $reservation->setReader($reader);

        $form = $this->createForm(BookReservationType::class, $reservation);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $book->addReservation($reservation);
            $reader->addBookReservation($reservation);

            $this->updateBookAfterReservation($book);

            $em = $this->getDoctrine()->getManager();
            $em->persist($reservation);
            $em->flush();

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

    private function updateBookAfterReservation(Book $book)
    {
        $availableCopies = $book->getAvailableCopies();
        $book->setAvailableCopies($availableCopies - 1);
        $reservedCopies = $book->getReservedCopies();
        $book->setReservedCopies($reservedCopies + 1);
        $timesBorrowed = $book->getTimesBorrowed();
        $book->setTimesBorrowed($timesBorrowed + 1);
    }
}
