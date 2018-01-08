<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 1/5/2018
 * Time: 11:01 AM
 */

namespace App\Entity;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Book
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     */
    private $ISBN;

    /**
     * @ORM\Column(type="string")
     */
    private $title;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Author", mappedBy="books")
     */
    private $authors;

    /**
     * @ORM\Column(type="integer")
     */
    private $pages;

    /**
     * @ORM\Column(type="string")
     */
    private $language;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Genre", inversedBy="books")
     * @ORM\JoinTable(name="books_genres")
     */
    private $genres;

    /**
     * @ORM\Column(type="string")
     */
    private $publisher;

    /**
     * @ORM\Column(type="datetime")
     */
    private $publicationDate;

    /**
     * @ORM\Column(type="integer")
     */
    private $availableCopies;

    /**
     * @ORM\Column(type="integer")
     */
    private $reservedCopies;

    /**
     * @ORM\Column(type="string")
     */
    private $cover;

    /**
     * @ORM\Column(type="string")
     */
    private $annotation;

    /**
     * @ORM\Column(type="float")
     */
    private $rating;

    /**
     * @ORM\Column(type="integer")
     */
    private $timesBorrowed;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\BookSerie", inversedBy="book")
     * @ORM\JoinColumn(name="bookserie_id", referencedColumnName="id")
     */
    private $serie;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\BookReservation", mappedBy="book")
     */
    private $reservations;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="book")
     */
    private $comments;

    public function __construct()
    {
        $this->authors = new ArrayCollection();
        $this->genres = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getISBN(): string
    {
        return $this->ISBN;
    }

    public function setISBN(string $ISBN): void
    {
        $this->ISBN = $ISBN;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getAuthors()
    {
        return $this->authors;
    }

    public function addAuthor($author): void
    {
        $this->authors->add($author);
    }

    public function getPages(): int
    {
        return $this->pages;
    }

    public function setPages(int $pages): void
    {
        $this->pages = $pages;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function setLanguage(string $language): void
    {
        $this->language = $language;
    }

    public function getGenres()
    {
        return $this->genres;
    }

    public function addGenre($genre): void
    {
        $this->genres->add($genre);
    }

    public function getPublisher(): string
    {
        return $this->publisher;
    }

    public function setPublisher(string $publisher): void
    {
        $this->publisher = $publisher;
    }

    public function getPublicationDate()
    {
        return $this->publicationDate;
    }

    public function setPublicationDate($publicationDate): void
    {
        $this->publicationDate = $publicationDate;
    }

    public function getAvailableCopies(): int
    {
        return $this->availableCopies;
    }

    public function setAvailableCopies(int $availableCopies): void
    {
        $this->availableCopies = $availableCopies;
    }

    public function getReservedCopies(): int
    {
        return $this->reservedCopies;
    }

    public function setReservedCopies(int $reservedCopies): void
    {
        $this->reservedCopies = $reservedCopies;
    }

    public function getCover()
    {
        return $this->cover;
    }

    public function setCover($cover): void
    {
        $this->cover = $cover;
    }

    public function getAnnotation(): string
    {
        return $this->annotation;
    }

    public function setAnnotation(string $annotation): void
    {
        $this->annotation = $annotation;
    }

    public function getRating():float
    {
        return $this->rating;
    }

    public function setRating(float $rating): void
    {
        $this->rating = $rating;
    }

    public function getTimesBorrowed():int
    {
        return $this->timesBorrowed;
    }

    public function setTimesBorrowed(int $timesBorrowed): void
    {
        $this->timesBorrowed = $timesBorrowed;
    }

    public function getSerie()
    {
        return $this->serie;
    }

    public function setSerie($serie): void
    {
        $this->serie = $serie;
    }

    public function getReservations()
    {
        return $this->reservations;
    }

    public function setReservations($reservations): void
    {
        $this->reservations = $reservations;
    }

    public function getComments()
    {
        return $this->comments;
    }

    public function setComments($comments): void
    {
        $this->comments = $comments;
    }
}
