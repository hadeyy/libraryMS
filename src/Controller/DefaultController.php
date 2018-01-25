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
use App\Repository\BookRepository;
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
        /** @var BookRepository $bookRepo */
        $bookRepo = $this->getDoctrine()->getRepository(Book::class);

        $popularBooks = $bookRepo->findAllOrderedByTimesBorrowed();
        $newBooks = $bookRepo->findAllOrderedByPublicationDate();

        return $this->render(
            'index.html.twig',
            [
                'popularBooks' => $popularBooks,
                'newBooks' => $newBooks,
            ]
        );
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

        return $this->render('catalog/index.html.twig', [
            'books' => $bookRepo->findAll(),
            'authors' => $authorRepo->findAll(),
            'genres' => $genreRepo->findAll(),
        ]);
    }

    /**
     * @Route("/catalog/author/{id}", name="books-by-author", requirements={"id"="\d+"})
     *
     * @param int $id Author id.
     *
     * @return Response
     */
    public function authorCatalog(int $id)
    {
        $authorRepo = $this->getDoctrine()->getRepository(Author::class);
        $genreRepo = $this->getDoctrine()->getRepository(Genre::class);

        /** @var Author $author */
        $author = $authorRepo->find($id);

        return $this->render('catalog/_books_by_author.html.twig', [
            'authors' => $authorRepo->findAll(),
            'genres' => $genreRepo->findAll(),
            'author' => $author,
            'books' => $author->getBooks(),
        ]);
    }

    /**
     * @Route("/catalog/genre/{id}", name="books-by-genre", requirements={"id"="\d+"})
     *
     * @param int $id Genre id.
     *
     * @return Response
     */
    public function genreCatalog(int $id)
    {
        $authorRepo = $this->getDoctrine()->getRepository(Author::class);
        $genreRepo = $this->getDoctrine()->getRepository(Genre::class);

        /** @var Genre $genre */
        $genre = $genreRepo->find($id);

        return $this->render('catalog/_books_by_genre.html.twig', [
            'authors' => $authorRepo->findAll(),
            'genres' => $genreRepo->findAll(),
            'genre' => $genre,
            'books' => $genre->getBooks(),
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

    /**
     * @Route("catalog/authors/{id}", name="show-author", requirements={"id"="\d+"})
     *
     * @param int $id Author id.
     *
     * @return Response
     */
    public function showAuthor(int $id)
    {
        $authorRepo = $this->getDoctrine()->getRepository(Author::class);
        /** @var Author $author */
        $author = $authorRepo->find($id);

        return $this->render(
            'catalog/author/show.html.twig',
            [
                'author' => $author,
            ]
        );
    }
}
