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
use Doctrine\Common\Persistence\ManagerRegistry;

class CommentManager
{
    private $em;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->em = $doctrine->getManager();
    }

    public function create(User $user, Book $book)
    {
        $comment = new Comment();

        $comment->setAuthor($user);
        $comment->setBook($book);

        return $comment;
    }

    public function updateRelatedEntitiesAndSave(Comment $comment, Book $book, User $user)
    {
        /** @TODO refactor this */
        $book->addComment($comment);
        $user->addComment($comment);

        $this->save($comment);
    }

    private function save(Comment $comment)
    {
        $this->em->persist($comment);
        $this->em->flush();
    }

}
