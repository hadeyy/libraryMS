<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 1/9/2018
 * Time: 10:05 AM
 */

namespace App\Repository;


use App\Entity\User;
use Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository
{
    /**
     * Finds all users by role.
     *
     * @param string $role
     *
     * @return User[]|null
     */
    public function findUsersByRole(string $role)
    {
        return $this->createQueryBuilder('u')
            ->where('u.roles LIKE :roles')
            ->setParameter('roles', '%"' . $role . '"%')
            ->orderBy('u.registeredAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Looks for a user and it's book reservations by status.
     *
     * @param User $user
     * @param string $status
     *
     * @return User|null
     */
    public function findUserJoinedToReservationsByStatus(User $user, string $status)
    {
        return $this->createQueryBuilder('u')
            ->addSelect('u, br, book, a')
            ->leftJoin('u.bookReservations', 'br')
            ->leftJoin('br.book', 'book')
            ->leftJoin('book.author', 'a')
            ->where('u = :user')
            ->andWhere('br.status = :status')
            ->setParameters([
                'user' => $user,
                'status' => $status,
            ])
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Looks for a user and it's favorite book.
     *
     * @param User $user
     *
     * @return User|null
     */
    public function findUserJoinedToFavoriteBooks(User $user)
    {
        return $this->createQueryBuilder('u')
            ->select('u, f')
            ->leftJoin('u.favorites', 'f')
            ->where('u = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
