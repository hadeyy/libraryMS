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

    public function create(User $user, Book $book, string $content): Comment
    {
        return new Comment($user, $book, $content);
    }

    public function save(Comment $comment)
    {
        $this->em->persist($comment);
        $this->em->flush();
        $this->em->clear();
    }

}
