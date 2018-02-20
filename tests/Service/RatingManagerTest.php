<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 2/20/2018
 * Time: 4:12 PM
 */

namespace App\Tests\Service;


use App\Entity\Book;
use App\Service\RatingManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RatingManagerTest extends WebTestCase
{
    public function testRate()
    {
        $book = new Book();

        $ratingManager = new RatingManager();
        $ratingManager->rate($book, 1);
        $ratingManager->rate($book, 7);
        $ratingManager->rate($book, 3);

        $this->assertEquals([1, 7, 3], $book->getRatings());
    }
}
