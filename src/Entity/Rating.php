<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 3/6/2018
 * Time: 12:02 PM
 */

namespace App\Entity;


use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="app_ratings")
 * @ORM\Entity(repositoryClass="App\Repository\RatingRepository")
 */
class Rating
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer", options={"unsigned"=true})
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $value;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Book", inversedBy="ratings", cascade={"persist"})
     * @ORM\JoinColumn(name="book_id", referencedColumnName="id")
     * @Assert\NotBlank()
     */
    private $book;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="ratings", cascade={"persist"})
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * @Assert\NotBlank()
     */
    private $user;

    public function __construct(int $value, Book $book, User $user)
    {
        $this->value = $value;
        $this->book = $book;
        $this->user = $user;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value): void
    {
        $this->value = $value;
    }

    public function getBook()
    {
        return $this->book;
    }

    public function setBook($book): void
    {
        $this->book = $book;
    }

    public function getRater()
    {
        return $this->user;
    }

    public function setRater($rater): void
    {
        $this->user = $rater;
    }


}
