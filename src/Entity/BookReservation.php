<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 1/5/2018
 * Time: 11:08 AM
 */

namespace App\Entity;


use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="app_book_reservations")
 * @ORM\Entity(repositoryClass="App\Repository\BookReservationRepository")
 * @Assert\Expression(
 *     "this.getDateFrom() <= this.getDateTo()",
 *     message="End date cannot be before start date!"
 * )
 */
class BookReservation
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Book", inversedBy="reservations")
     * @ORM\JoinColumn(name="book_id", referencedColumnName="id")
     */
    private $book;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\Date()
     * @Assert\GreaterThan(
     *     value="today",
     *     message="Earliest book reservation start date can be the next day."
     * )
     */
    private $dateFrom;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\Date()
     * @Assert\GreaterThanOrEqual(
     *     value="+2 days",
     *     message="Earliest book reservation end date can be 2 days from today."
     * )
     */
    private $dateTo;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="bookReservations")
     * @ORM\JoinColumn(name="reader_id", referencedColumnName="id")
     */
    private $reader;

    /**
     * @ORM\Column(type="string")
     */
    private $status;

    /**
     * @ORM\Column(type="float")
     */
    private $fine;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\Date()
     */
    private $updatedAt;

    public function __construct(Book $book, User $user)
    {
        $this->book = $book;
        $this->reader = $user;
        $this->status = 'reserved';
        $this->fine = 0;
        $this->updatedAt = new \DateTime();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id): void
    {
        $this->id = $id;
    }

    public function getBook()
    {
        return $this->book;
    }

    public function setBook($book): void
    {
        $this->book = $book;
    }

    public function getDateFrom()
    {
        return $this->dateFrom;
    }

    public function setDateFrom($dateFrom): void
    {
        $this->dateFrom = $dateFrom;
    }

    public function getDateTo()
    {
        return $this->dateTo;
    }

    public function setDateTo($dateTo): void
    {
        $this->dateTo = $dateTo;
    }

    public function getReader()
    {
        return $this->reader;
    }

    public function setReader($reader): void
    {
        $this->reader = $reader;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status): void
    {
        $this->status = $status;
    }

    public function getFine()
    {
        return $this->fine;
    }

    public function setFine($fine): void
    {
        $this->fine = $fine;
    }

    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt($updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }
}
