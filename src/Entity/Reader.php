<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 1/5/2018
 * Time: 11:03 AM
 */

namespace App\Entity;


class Reader extends User
{
    private $registeredAt;
    private $photo;
    private $notifications;
    private $bookReservations;
}
