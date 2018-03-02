<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 3/2/2018
 * Time: 1:24 PM
 */

namespace App\Service;


use App\Entity\Activity;
use App\Entity\Comment;
use App\Entity\User;
use Doctrine\Common\Persistence\ManagerRegistry;

class AppManager
{
    private $em;
    private $fileManager;

    public function __construct(
        ManagerRegistry $doctrine,
        FileManager $fileManager
    )
    {
        $this->em = $doctrine->getManager();
        $this->fileManager = $fileManager;
    }

    public function createUser()
    {
        return new User();
    }

    public function changeRole(User $user, string $role)
    {
        $this->resetRoles($user);

        $user->addRole($role);
    }

    private function resetRoles(User $user)
    {
        foreach ($user->getRoles() as $role) {
            unset($role);
        }
    }

    public function deleteUser(User $user)
    {
        /** @todo remove user photo */

        $this->remove($user);
    }

    public function deleteComment(Comment $comment)
    {
        $this->remove($comment);
    }

    public function getAllActivity()
    {
        $repository = $this->em->getRepository(Activity::class);

        return $repository->findRecentActivity();
    }

    public function save(User $user)
    {
        $this->em->persist($user);
        $this->em->flush();
    }

    public function saveChanges()
    {
        $this->em->flush();
    }

    public function remove($entity)
    {
        $this->em->remove($entity);
        $this->em->flush();
    }
}
