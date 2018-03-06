<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 1/9/2018
 * Time: 4:05 PM
 */

namespace App\DataFixtures;


use App\Entity\Book;
use App\Entity\BookReservation;
use App\Entity\User;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class BookReservationFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $statuses = ['reserved', 'reading', 'returned', 'canceled'];

        for ($i = 0; $i < 50; $i++) {
            /** @var Book $book */
            $book = $this->getReference('book' . mt_rand(0, 99));
            /** @var User $user */
            $user = $this->getReference('user' . mt_rand(0, 3));

            $reservation = new BookReservation($book, $user);

            $reservation->setDateFrom(DateTime::createFromFormat(
                'd-m-Y',
                $this->randomDate('+2 days', '+8 days'))
            );
            $reservation->setDateTo(DateTime::createFromFormat(
                'd-m-Y',
                $this->randomDate('+9 days', '+20 days'))
            );
            $reservation->setStatus($statuses[mt_rand(0, count($statuses) - 1)]);
            $reservation->getStatus() !== 'reading' ?: $reservation->setFine(mt_rand(0, 50) / 10);

            $availableCopies = $book->getAvailableCopies();
            $book->setAvailableCopies($availableCopies - 1);
            $reservedCopies = $book->getReservedCopies();
            $book->setReservedCopies($reservedCopies + 1);
            $timesBorrowed = $book->getTimesBorrowed();
            $book->setTimesBorrowed($timesBorrowed + 1);

            $manager->persist($reservation);
        }

        $manager->flush();
    }

    private function randomDate($startDate, $endDate)
    {
        $min = strtotime($startDate);
        $max = strtotime($endDate);

        $randomDate = rand($min, $max);

        return date('d-m-Y', $randomDate);
    }

    public function getDependencies()
    {
        return [BookFixtures::class, UserFixtures::class];
    }
}
