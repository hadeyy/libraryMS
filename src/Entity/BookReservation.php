<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 1/5/2018
 * Time: 11:08 AM
 */

namespace App\Entity;


class BookReservation
{
    private $id;
    private $book;
    private $dateFrom;
    private $dateTo;
    private $reader;
    private $status;
    private $fine;
}
