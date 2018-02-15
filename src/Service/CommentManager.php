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

class CommentManager extends EntityManager
{
    public function create(User $user, Book $book)
    {
        $comment = new Comment();

        $comment->setAuthor($user);
        $comment->setBook($book);

        return $comment;
    }

    public function updateRelatedEntitiesAndSave(Comment $comment, Book $book, User $user)
    {
        $book->addComment($comment);
        $user->addComment($comment);

        $this->save($comment);
    }
}
