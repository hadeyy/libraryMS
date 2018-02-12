<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 1/25/2018
 * Time: 1:25 PM
 */

namespace App\Controller\reader;


use App\Entity\Activity;
use App\Entity\Book;
use App\Entity\BookReservation;
use App\Entity\User;
use App\Form\BookReservationType;
use Doctrine\Common\Collections\ArrayCollection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\RedirectResponse;
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
            $activity = $this->createReservationActivity($book, $this->getUser());

            $em = $this->getDoctrine()->getManager();
            $em->persist($reservation);
            $em->persist($activity);
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

    private function createReservationActivity(Book $book, User $user)
    {
        /** @var Activity $activity */
        $activity = new Activity();
        $activity->setBook($book);
        $activity->setUser($user);
        $activity->setTitle('Reserved a book');
        $book->addActivity($activity);
        $user->addActivity($activity);

        return $activity;
    }

    /**
     * @Route("/catalog/books/{id}/toggle-favorite", name="toggle-favorite", requirements={"id"="\d+"})
     *
     * @param int $id Book ID
     *
     * @return RedirectResponse
     */
    public function toggleFavorite(int $id)
    {
        $bookRepo = $this->getDoctrine()->getRepository(Book::class);
        /** @var Book $book */
        $book = $bookRepo->find($id);
        /** @var User $reader */
        $reader = $this->getUser();
        /** @var ArrayCollection $favorites */
        $favorites = $reader->getFavorites();
        /** @var bool $isAFavorite */
        $isAFavorite = $favorites->contains($book);

        $action = $isAFavorite ?: 'add';

        /** @var Activity $activity */
        $activity = new Activity();
        $activity->setBook($book);
        $activity->setUser($reader);

        if ('add' === $action) {
            $activity->setTitle('Added a book to favorites');
            $reader->addFavorite($book);
        } else {
            $activity->setTitle('Removed a book from favorites');
            $reader->removeFavorite($book);
        }

        $book->addActivity($activity);
        $reader->addActivity($activity);

        $em = $this->getDoctrine()->getManager();
        $em->persist($activity);
        $em->flush();

        return $this->redirectToRoute('show-book', ['id' => $id]);
    }

    /**
     * @Route("/catalog/books/{id}/rate", name="rate-book", requirements={"id"="\d+"})
     *
     * @param Request $request
     * @param int $id
     *
     * @return Response
     */
    public function rateBook(Request $request, int $id)
    {
        $bookRepo = $this->getDoctrine()->getRepository(Book::class);
        /** @var Book $book */
        $book = $bookRepo->find($id);

        $defaultData = ['message' => 'Select rating'];
        $form = $this->createFormBuilder($defaultData)
            ->add('rating', ChoiceType::class, [
                'choices' => [
                    '5' => '5',
                    '4' => '4',
                    '3' => '3',
                    '2' => '2',
                    '1' => '1',
                ],
            ])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            $rating = (int)$formData['rating'];

            $book->addRating($rating);

            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return $this->redirectToRoute('show-book', ['id' => $book->getId()]);
        }

        return $this->render(
            'catalog/book/rating.html.twig',
            [
                'book' => $book,
                'form' => $form->createView(),
            ]
        );
    }
}
