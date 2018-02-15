<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 2/15/2018
 * Time: 3:36 PM
 */

namespace App\Service;


use App\Entity\Book;
use App\Entity\Comment;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CommentManager extends EntityManager
{
    private $comment;

    public function __construct(EntityManagerInterface $manager, ContainerInterface $container)
    {
        parent::__construct($manager, $container);

        $this->comment = new Comment();
    }

    public function create(User $user, Book $book)
    {
        $this->comment->setAuthor($user);
        $this->comment->setBook($book);

        return $this->comment;
    }

    public function updateRelatedEntitiesAndSave(Comment $comment, Book $book, User $user)
    {
        $book->addComment($comment);
        $user->addComment($comment);

        $this->save($comment);
    }
}
