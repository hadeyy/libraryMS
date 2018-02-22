<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 1/9/2018
 * Time: 10:05 AM
 */

namespace App\Repository;


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
}
