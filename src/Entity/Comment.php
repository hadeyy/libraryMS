<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 1/5/2018
 * Time: 12:11 PM
 */

namespace App\Entity;


use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="app_comments")
 * @ORM\Entity(repositoryClass="App\Repository\CommentRepository")
 */
class Comment
{
    /**
     * @ORM\Id
     * @ORM\Column(type="guid")
     * @Assert\NotBlank()
     * @Assert\Uuid
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Book", inversedBy="comments")
     * @ORM\JoinColumn(name="book_id", referencedColumnName="id")
     * @Assert\NotBlank()
     * @Assert\Valid
     */
    private $book;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="comments")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * @Assert\NotBlank()
     * @Assert\Valid
     */
    private $author;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\NotBlank()
     * @Assert\Date()
     */
    private $publishedAt;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     * @Assert\Length(
     *     min = 2,
     *     max = 350,
     *     minMessage="Comment must be at least {{ limit }} characters long.",
     *     maxMessage="Comment cannot be longer than {{ limit }} characters."
     * )
     */
    private $content;

    public function __construct(
        User $user,
        Book $book,
        string $content
    ) {
        $this->id = Uuid::uuid4();
        $this->author = $user;
        $this->book = $book;
        $this->content = $content;
        $this->publishedAt = new \DateTime();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getBook(): Book
    {
        return $this->book;
    }

    public function getAuthor(): User
    {
        return $this->author;
    }

    public function getPublishedAt(): \DateTime
    {
        return $this->publishedAt;
    }

    public function getContent(): string
    {
        return $this->content;
    }
}
