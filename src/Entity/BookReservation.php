<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 1/5/2018
 * Time: 11:08 AM
 */

namespace App\Entity;


use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="app_book_reservations")
 * @ORM\Entity(repositoryClass="App\Repository\BookReservationRepository")
 */
class BookReservation
{
    /**
     * @ORM\Id
     * @ORM\Column(type="guid")
     * @Assert\NotBlank()
     * @Assert\Uuid
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Book", inversedBy="reservations")
     * @ORM\JoinColumn(name="book_id", referencedColumnName="id")
     * @Assert\NotBlank()
     * @Assert\Valid
     */
    private $book;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\NotBlank()
     * @Assert\Date()
     * @Assert\GreaterThanOrEqual(
     *     value="today",
     *     message="Earliest book reservation start date can be today."
     * )
     */
    private $dateFrom;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\NotBlank()
     * @Assert\Date()
     * @Assert\Expression(
     *     "this.getDateFrom() < value",
     *     message="End date cannot be before start date!"
     * )
     */
    private $dateTo;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="bookReservations")
     * @ORM\JoinColumn(name="reader_id", referencedColumnName="id")
     * @Assert\NotBlank()
     * @Assert\Valid
     */
    private $reader;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     */
    private $status;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\NotBlank()
     * @Assert\Date()
     */
    private $updatedAt;

    public function __construct(
        Book $book,
        User $reader,
        \DateTime $dateFrom,
        \DateTime $dateTo
    ) {
        $this->id = Uuid::uuid4();
        $this->book = $book;
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->reader = $reader;
        $this->status = 'reserved';
        $this->updatedAt = new \DateTime();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getBook(): Book
    {
        return $this->book;
    }

    public function getDateFrom(): \DateTime
    {
        return $this->dateFrom;
    }

    public function getDateTo(): \DateTime
    {
        return $this->dateTo;
    }

    public function getReader(): User
    {
        return $this->reader;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }
}
