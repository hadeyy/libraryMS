<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 1/8/2018
 * Time: 3:56 PM
 */

namespace App\Controller;


use App\Entity\Author;
use App\Entity\Book;
use App\Entity\Comment;
use App\Entity\Genre;
use App\Entity\User;
use App\Form\CommentType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="index")
     */
    public function index()
    {
        return $this->render('index.html.twig');
    }

    /**
     * @Route("/catalog", name="catalog")
     * @Route("/catalog/books", name="catalog-books")
     */
    public function catalog()
    {
        $bookRepo = $this->getDoctrine()->getRepository(Book::class);
        $authorRepo = $this->getDoctrine()->getRepository(Author::class);
        $genreRepo = $this->getDoctrine()->getRepository(Genre::class);

        $allBooks = $bookRepo->findAll();
        $allAuthors = $authorRepo->findAll();
        $allGenres = $genreRepo->findAll();

        return $this->render('catalog/index.html.twig', [
            'books' => $allBooks,
            'authors' => $allAuthors,
            'genres' => $allGenres,
        ]);
    }

    /**
     * @Route("/catalog/books/{id}", name="show-book", requirements={"id"="\d+"})
     *
     * @param Request $request
     * @param int $id Book id.
     *
     * @return Response
     */
    public function showBook(Request $request, int $id): Response
    {
        $bookRepo = $this->getDoctrine()->getRepository(Book::class);
        /** @var Book $book */
        $book = $bookRepo->find($id);

        $comment = new Comment();
        /** @var User $user */
        $user = $this->getUser();
        $comment->setAuthor($user);
        $comment->setBook($book);

        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $book->addComment($comment);
            $user->addComment($comment);

            $em = $this->getDoctrine()->getManager();
            $em->persist($comment);
            $em->flush();
        }

        return $this->render(
            'catalog/book/show.html.twig',
            [
                'book' => $book,
                'form' => $form->createView(),
            ]
        );
    }
}
