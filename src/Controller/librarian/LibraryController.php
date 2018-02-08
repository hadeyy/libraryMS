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
use App\Entity\Genre;
use App\Form\AuthorType;
use App\Form\BookType;
use App\Form\GenreType;
use App\Repository\BookReservationRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Security("has_role('ROLE_LIBRARIAN')")
 */
class LibraryController extends Controller
{
    /**
     * @Route("/catalog/books/new", name="new-book")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function newBook(Request $request)
    {
        $book = new Book();

        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $file */
            $file = $book->getCover();

            $extension = $file->guessExtension();
            $filename = md5(uniqid()) . '_' . (string)date('mdYHms') . '.' . $extension;
            $path = $this->getParameter('book_cover_directory');

            $file->move($path, $filename);

            $book->setCover($filename);

            $em = $this->getDoctrine()->getManager();
            $em->persist($book);
            $em->flush();

            return $this->redirectToRoute('catalog-books');
        }

        return $this->render('catalog/book/new.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/catalog/authors/new", name="new-author")
     *
     * @param Request $request
     *
     * @return RedirectResponse|Response
     */
    public function newAuthor(Request $request)
    {
        $author = new Author();

        $form = $this->createForm(AuthorType::class, $author);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($author);
            $em->flush();

            return $this->redirectToRoute('catalog-books');
        }

        return $this->render('catalog/author/new.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/catalog/genres/new", name="new-genre")
     *
     * @param Request $request
     *
     * @return RedirectResponse|Response
     */
    public function newGenre(Request $request)
    {
        $genre = new Genre();

        $form = $this->createForm(GenreType::class, $genre);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($genre);
            $em->flush();

            return $this->redirectToRoute('catalog-books');
        }

        return $this->render('catalog/genre/new.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/reservations", name="reservations")
     */
    public function reservations()
    {
        /** @var BookReservationRepository $reservationRepo */
        $reservationRepo = $this->getDoctrine()->getRepository(BookReservation::class);

        $reserved = $reservationRepo->findReservationsByStatus('reserved');
        $reading = $reservationRepo->findReservationsByStatus('reading');
        $returned = $reservationRepo->findReservationsByStatus('returned');
        $canceled = $reservationRepo->findReservationsByStatus('canceled');

        return $this->render(
            'librarian/reservations.html.twig',
            [
                'reserved' => $reserved,
                'reading' => $reading,
                'returned' => $returned,
                'canceled' => $canceled,
            ]
        );
    }

    /**
     * @Route("/reservations/update/{id}/{status}", name="reservation-update", defaults={"id" = 1, "status" = "reserved"})
     * @Method("GET")
     *
     * @param int $id Reservation id.
     * @param string $status New reservation status.
     *
     * @return RedirectResponse
     */
    public function updateReservationStatus(int $id, string $status)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var BookReservation $reservation */
        $reservation = $em->getRepository(BookReservation::class)->find($id);
        $reservation->setStatus($status);
        $reservation->setUpdatedAt(new \DateTime());

        if ($status === 'returned' || 'canceled') {
            $reservation->getFine() < 0 ?: $reservation->setFine(0);

            /** @var Book $book */
            $book = $reservation->getBook();

            $this->updateReservationBook($book);
        }

        $em->flush();

        return $this->redirectToRoute('reservations');
    }

    private function updateReservationBook(Book $book)
    {
        $availableCopies = $book->getAvailableCopies();
        $book->setAvailableCopies($availableCopies + 1);
        $reservedCopies = $book->getReservedCopies();
        $book->setReservedCopies($reservedCopies - 1);
    }

}
