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
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="app_books")
 * @ORM\Entity(repositoryClass="App\Repository\BookRepository")
 * @UniqueEntity(fields="ISBN", message="Book with this ISBN already exists.")
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
     * @Assert\NotBlank()
     * @Assert\Length(
     *     min="10",
     *     max="13",
     *     exactMessage="ISBN must be either 10 or 13 characters long."
     * )
     */
    private $ISBN;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     * @Assert\Length(
     *     min = 1,
     *     max = 140,
     *     minMessage="Title must be at least {{ limit }} characters long.",
     *     maxMessage="Title cannot be longer than {{ limit }} characters."
     * )
     */
    private $title;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Author", inversedBy="books")
     * @ORM\JoinColumn(name="author_id", referencedColumnName="id")
     * @Assert\NotBlank()
     */
    private $author;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank()
     * @Assert\Range(
     *     min = 10,
     *     max = 13095,
     *     minMessage="This value should be greater than or equal to {{ limit }}.",
     *     maxMessage="This value should be less than or equal to {{ limit }}."
     * )
     */
    private $pages;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     */
    private $language;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Genre", inversedBy="books", cascade={"persist"})
     * @ORM\JoinTable(name="books_and_genres",
     *     joinColumns={@ORM\JoinColumn(name="bookId", referencedColumnName="id", unique=false)},
     *     inverseJoinColumns={@ORM\JoinColumn(name="genreId", referencedColumnName="id", unique=false)}
     * )
     * @Assert\NotNull()
     * @Assert\Count(
     *     min="1",
     *     max="5",
     *     minMessage="You must specify at least one genre.",
     *     maxMessage="You cannot specify more than {{ limit }} genres."
     * )
     */
    private $genres;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     * @Assert\Length(
     *     min = 2,
     *     max = 140,
     *     minMessage="Publisher name must be at least {{ limit }} characters long.",
     *     maxMessage="Publisher name cannot be longer than {{ limit }} characters."
     * )
     */
    private $publisher;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\NotBlank()
     * @Assert\Date()
     * @Assert\LessThan("today")
     */
    private $publicationDate;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank()
     * @Assert\Range(
     *     min = 1,
     *     max = 100,
     *     minMessage="This value should be greater than or equal to {{ limit }}.",
     *     maxMessage="This value should be less than or equal to {{ limit }}."
     * )
     */
    private $availableCopies;

    /**
     * @ORM\Column(type="integer")
     */
    private $reservedCopies;

    /**
     * @ORM\Column(type="string")
     * @Assert\Image(
     *     minWidth = 50,
     *     maxWidth = 5000,
     *     minHeight = 50,
     *     maxHeight = 5000,
     *     minWidthMessage="Minimum width expected is {{ min_width }}px.",
     *     maxWidthMessage="Allowed maximum width is {{ max_width }}px.",
     *     minHeightMessage="Minimum height expected is {{ min_height }}px.",
     *     maxHeightMessage="Allowed maximum height is {{ max_height }}px."
     * )
     */
    private $cover;

    /**
     * @ORM\Column(type="string")
     * @Assert\Length(
     *     min = 140,
     *     max = 2000,
     *     minMessage="Annotation must be at least {{ limit }} characters long.",
     *     maxMessage="Annotation cannot be longer than {{ limit }} characters.",
     * )
     */
    private $annotation;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Rating", mappedBy="book", cascade={"persist", "remove"})
     */
    private $ratings;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $timesBorrowed;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\BookReservation", mappedBy="book", cascade={"persist", "remove"})
     */
    private $reservations;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="book", cascade={"persist", "remove"})
     */
    private $comments;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Activity", mappedBy="book", cascade={"persist", "remove"})
     */
    private $activities;

    public function __construct()
    {
        $this->genres = new ArrayCollection();
        $this->ratings = new ArrayCollection();
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

    public function getISBN()
    {
        return $this->ISBN;
    }

    public function setISBN(string $ISBN): void
    {
        $this->ISBN = $ISBN;
    }

    public function getTitle()
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

    public function getPages()
    {
        return $this->pages;
    }

    public function setPages(int $pages): void
    {
        $this->pages = $pages;
    }

    public function getLanguage()
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

    public function getPublisher()
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

    public function getAvailableCopies()
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

    public function getAnnotation()
    {
        return $this->annotation;
    }

    public function setAnnotation(string $annotation): void
    {
        $this->annotation = $annotation;
    }

    public function getRatings()
    {
        return $this->ratings;
    }

    public function addRating($rating): void
    {
        $this->ratings->add($rating);
    }

    public function getTimesBorrowed()
    {
        return $this->timesBorrowed;
    }

    public function setTimesBorrowed(int $timesBorrowed): void
    {
        $this->timesBorrowed = $timesBorrowed;
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

    public function __toString()
    {
        return $this->title;
    }
}
