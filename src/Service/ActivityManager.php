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
    public function log(User $user, Book $book, string $title)
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
