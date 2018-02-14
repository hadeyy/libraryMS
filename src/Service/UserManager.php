<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 2/14/2018
 * Time: 2:47 PM
 */

namespace App\Service;


use App\Entity\User;

class UserManager
{
    public function register(
        User $user,
        string $photoPath,
        string $password,
        string $role
    ) {
        $user->setPhoto($photoPath);
        $user->setPassword($password);
        $user->addRole($role);
    }
}
