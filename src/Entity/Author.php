<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 1/5/2018
 * Time: 11:01 AM
 */

namespace App\Entity;


class Author
{
    private $id;
    private $firstName;
    private $lastName; // optional (in case of one name alias)
    private $books;
    private $country;
}
