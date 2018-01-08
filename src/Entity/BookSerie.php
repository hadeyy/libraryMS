<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 1/5/2018
 * Time: 11:06 AM
 */

namespace App\Entity;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class BookSerie
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Author", mappedBy="bookSeries")
     */
    private $authors;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Book", mappedBy="serie")
     */
    private $book;

    /**
     * @ORM\Column(type="integer")
     */
    private $part;

    public function __construct()
    {
        $this->authors = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id): void
    {
        $this->id = $id;
    }

    public function getAuthors()
    {
        return $this->authors;
    }

    public function addAuthors($author): void
    {
        $this->authors->add($author);
    }

    public function getBook()
    {
        return $this->book;
    }

    public function setBook($book): void
    {
        $this->book = $book;
    }

    public function getPart()
    {
        return $this->part;
    }

    public function setPart($part): void
    {
        $this->part = $part;
    }

}
