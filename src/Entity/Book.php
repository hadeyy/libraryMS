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
 * @ORM\Table(name="app_books")
 * @ORM\Entity(repositoryClass="App\Repository\BookRepository")
 */
class Book
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer", options={"unsigned"=true})
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Author", inversedBy="books")
     * @ORM\JoinColumn(name="author_id", referencedColumnName="id")
     */
    private $author;

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
     * @ORM\JoinTable(name="books_and_genres",
     *     joinColumns={@ORM\JoinColumn(name="bookId", referencedColumnName="id", unique=false)},
     *     inverseJoinColumns={@ORM\JoinColumn(name="genreId", referencedColumnName="id", unique=false)}
     * )
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
     * @ORM\Column(type="json_array")
     */
    private $ratings;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $timesBorrowed;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\BookReservation", mappedBy="book")
     */
    private $reservations;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="book")
     */
    private $comments;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Activity", mappedBy="book")
     */
    private $activities;

    public function __construct()
    {
        $this->genres = new ArrayCollection();
        $this->ratings = [];
        $this->reservedCopies = 0;
        $this->timesBorrowed = 0;
        $this->reservations = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->activities = new ArrayCollection();
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

    public function getAuthor()
    {
        return $this->author;
    }

    public function setAuthor($author): void
    {
        $this->author = $author;
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

    public function getRatings(): array
    {
        return $this->ratings;
    }

    public function addRating(float $rating): void
    {
        $this->ratings[] = $rating;
    }

    public function getTimesBorrowed(): int
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

    public function addReservation($reservation): void
    {
        $this->reservations->add($reservation);
    }

    public function getComments()
    {
        return $this->comments;
    }

    public function addComment($comment): void
    {
        $this->comments->add($comment);
    }

    public function getActivities()
    {
        return $this->activities;
    }

    public function addActivity($activity): void
    {
        $this->activities->add($activity);
    }
}
