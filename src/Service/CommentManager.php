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

    /**
     * Creates a new instance of Comment and saves it to the database.
     *
     * @param User $user
     * @param Book $book
     * @param string $content
     *
     * @return void
     */
    public function create(User $user, Book $book, string $content)
    {
        $comment = new Comment($user, $book, $content);

        $this->save($comment);
    }

    /**
     * Calls entity manager to make the instance managed and persistent and
     * to save all changes made to objects to the database.
     *
     * @param Comment $comment
     *
     * @return void
     */
    public function save(Comment $comment)
    {
        $this->em->persist($comment);
        $this->em->flush();
    }

}
