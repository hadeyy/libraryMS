<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 2/15/2018
 * Time: 3:40 PM
 */

namespace App\Service;


use App\Entity\Book;

class RatingManager
{
    public function rate(Book $book, int $rating)
    {
        $book->addRating($rating);
    }
}
