<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 2/14/2018
 * Time: 1:49 PM
 */

namespace App\Service;


use App\Entity\User;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class PasswordManager
{
    private $encoder;
    private $em;

    public function __construct(
        UserPasswordEncoderInterface $passwordEncoder,
        ManagerRegistry $doctrine
    )
    {
        $this->encoder = $passwordEncoder;
        $this->em = $doctrine->getManager();
    }

    /**
     * Encodes user's plain text password.
     *
     * @param User $user
     *
     * @return string Encoded password.
     */
    public function encode(User $user)
    {
        return $this->encoder->encodePassword($user, $user->getPlainPassword());
    }

    /**
     * Encodes given string and sets it as user's password.
     *
     * @param User $user
     * @param string $newPassword New password in plain text.
     *
     * @return void
     */
    public function changePassword(User $user, string $newPassword)
    {
        $user->setPlainPassword($newPassword);
        $encodedPassword = $this->encode($user);
        $user->setPassword($encodedPassword);

        $this->saveChanges();
    }

    /**
     * Saves all changes made to objects to the database.
     *
     * @return void
     */
    public function saveChanges()
    {
        $this->em->flush();
    }
}
