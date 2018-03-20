<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 3/2/2018
 * Time: 1:24 PM
 */

namespace App\Service;


use App\Entity\Comment;
use App\Entity\User;
use Doctrine\Common\Persistence\ManagerRegistry;

class AppManager
{
    private $em;
    private $fileManager;
    private $userManager;

    public function __construct(
        ManagerRegistry $doctrine,
        FileManager $fileManager,
        UserManager $userManager
    ) {
        $this->em = $doctrine->getManager();
        $this->fileManager = $fileManager;
        $this->userManager = $userManager;
    }

    public function changeRole(User $user, string $role)
    {
        $user->resetRoles();
        $user->addRole($role);

        $this->saveChanges();
    }

    public function deleteUser(User $user)
    {
        $photoDirectory = $this->userManager->getPhotoDirectory();
        $photo = $user->getPhoto();
        $this->fileManager->deleteFile($photoDirectory . '/' . $photo);

        $this->remove($user);
    }

    public function deleteComment(Comment $comment)
    {
        $this->remove($comment);
    }

    public function remove($entity)
    {
        $this->em->remove($entity);
        $this->saveChanges();
    }

    public function saveChanges()
    {
        $this->em->flush();
    }
}
