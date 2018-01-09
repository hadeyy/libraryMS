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
 * @ORM\Table(name="app_authors")
 * @ORM\Entity(repositoryClass="App\Repository\AuthorRepository")
 */
class Author
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
    private $firstName;

    /**
     * @ORM\Column(type="string")
     */
    private $lastName; // optional (in case of one name alias)

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Book", inversedBy="authors")
     * @ORM\JoinTable(name="authors_books")
     */
    private $books;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\BookSerie", inversedBy="authors")
     * @ORM\JoinTable(name="authors_bookseries")
     */
    private $bookSeries;

    /**
     * @ORM\Column(type="string")
     */
    private $country;

    public function __construct()
    {
        $this->books = new ArrayCollection();
        $this->bookSeries = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id): void
    {
        $this->id = $id;
    }

    public function getFirstName()
    {
        return $this->firstName;
    }

    public function setFirstName($firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getLastName()
    {
        return $this->lastName;
    }

    public function setLastName($lastName): void
    {
        $this->lastName = $lastName;
    }

    public function getBooks()
    {
        return $this->books;
    }

    public function addBook($book): void
    {
        $this->books->add($book);
    }

    public function getBookSeries()
    {
        return $this->bookSeries;
    }

    public function addBookSeries($bookSerie): void
    {
        $this->bookSeries->add($bookSerie);
    }

    public function getCountry()
    {
        return $this->country;
    }

    public function setCountry($country): void
    {
        $this->country = $country;
    }

}
