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
use App\Entity\Genre;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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
     * @Route("/catalog/books/{id}", name="show-book")
     *
     * @param int $id Book id.
     *
     * @return Response
     */
    public function showBook(int $id): Response
    {
        $bookRepo = $this->getDoctrine()->getRepository(Book::class);

        /** @var Book $book */
        $book = $bookRepo->find($id);

        return $this->render('catalog/book/show.html.twig', ['book' => $book]);
    }
}
