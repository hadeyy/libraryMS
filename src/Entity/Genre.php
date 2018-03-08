<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 1/5/2018
 * Time: 11:04 AM
 */

namespace App\Entity;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="app_genres")
 * @ORM\Entity(repositoryClass="App\Repository\GenreRepository")
 */
class Genre
{
    /**
     * @ORM\Id
     * @ORM\Column(type="guid")
     * @Assert\NotBlank()
     * @Assert\Uuid
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     * @Assert\Length(
     *     min = 2,
     *     max = 25,
     *     minMessage="Genre name must be at least {{ limit }} characters long.",
     *     maxMessage="Genre name cannot be longer than {{ limit }} characters."
     * )
     */
    private $name;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Book", mappedBy="genres")
     */
    private $books;

    public function __construct(string $name)
    {
        $this->id = Uuid::uuid4();
        $this->name = $name;
        $this->books = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getBooks(): ArrayCollection
    {
        return $this->books;
    }

    public function addBook(Book $book): void
    {
        $this->books->add($book);
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
