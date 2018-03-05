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
     * @param User $user
     * @param string $status
     *
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findUserJoinedToReservations(User $user, string $status)
    {
        return $this->createQueryBuilder('u')
            ->select('u, br, book, a')
            ->innerJoin('u.bookReservations', 'br')
            ->innerJoin('br.book', 'book')
            ->innerJoin('book.author', 'a')
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
     * @param User $user
     *
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findUserJoinedToFavoriteBooks(User $user)
    {
        return $this->createQueryBuilder('u')
            ->select('u, f')
            ->innerJoin('u.favorites', 'f')
            ->where('u = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
