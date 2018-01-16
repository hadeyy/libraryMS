<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 1/16/2018
 * Time: 2:26 PM
 */

namespace App\Controller\librarian;


use App\Entity\Book;
use App\Form\BookType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Security("has_role('ROLE_LIBRARIAN')")
 */
class LibraryController extends Controller
{
    /**
     * @Route("/catalog/books/new", name="new-book")
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
}
