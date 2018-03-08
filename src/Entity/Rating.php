<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 3/6/2018
 * Time: 12:02 PM
 */

namespace App\Entity;


use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="app_ratings")
 * @ORM\Entity(repositoryClass="App\Repository\RatingRepository")
 */
class Rating
{
    /**
     * @ORM\Id
     * @ORM\Column(type="guid")
     * @Assert\NotBlank()
     * @Assert\Uuid
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank()
     * @Assert\Type(
     *     type="integer",
     *     message="The value {{ value }} is not a valid {{ type }}."
     * )
     * @Assert\Range(
     *     min = 1,
     *     max = 5,
     *     minMessage="This value should be greater than or equal to {{ limit }}.",
     *     maxMessage="This value should be less than or equal to {{ limit }}."
     * )
     */
    private $value;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Book", inversedBy="ratings", cascade={"persist"})
     * @ORM\JoinColumn(name="book_id", referencedColumnName="id")
     * @Assert\NotBlank()
     * @Assert\Valid
     */
    private $book;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="ratings", cascade={"persist"})
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * @Assert\NotBlank()
     * @Assert\Valid
     */
    private $user;

    public function __construct(int $value, Book $book, User $user)
    {
        $this->id = Uuid::uuid4();
        $this->value = $value;
        $this->book = $book;
        $this->user = $user;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function setValue(int $value): void
    {
        $this->value = $value;
    }

    public function getBook(): Book
    {
        return $this->book;
    }

    public function getRater(): User
    {
        return $this->user;
    }
}
