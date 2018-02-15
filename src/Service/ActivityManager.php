<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 2/15/2018
 * Time: 4:10 PM
 */

namespace App\Service;


use App\Entity\Activity;
use App\Entity\Book;
use App\Entity\User;

class ActivityManager extends EntityManager
{
    public function logCommenting(User $user, Book $book, string $title = "Commented on a book's page")
    {
        $activity = new Activity();

        $activity->setUser($user);
        $activity->setBook($book);
        $activity->setTitle($title);
        $book->addActivity($activity);
        $user->addActivity($activity);

        $this->save($activity);
    }

    public function logRating(User $user, Book $book, string $title = 'Rated a book')
    {
        $activity = new Activity();

        $activity->setUser($user);
        $activity->setBook($book);
        $activity->setTitle($title);
        $book->addActivity($activity);
        $user->addActivity($activity);

        $this->save($activity);
    }
}
