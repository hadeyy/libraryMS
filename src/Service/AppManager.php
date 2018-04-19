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
    )
    {
        $this->em = $doctrine->getManager();
        $this->fileManager = $fileManager;
        $this->userManager = $userManager;
    }

    /**
     * Resets roles previously assigned to the user and assigns the new one.
     *
     * @param User $user
     * @param string $role New role that will replace existing one.
     *
     * @return void
     */
    public function changeRole(User $user, string $role)
    {
        $user->resetRoles();
        $user->addRole($role);

        $this->saveChanges();
    }

    /**
     * Removes user's photo from user photo directory and
     * removes the user instance from database.
     *
     * @param User $user
     *
     * @return void
     */
    public function deleteUser(User $user)
    {
        $photoDirectory = $this->userManager->getPhotoDirectory();
        $photo = $user->getPhoto();
        $this->fileManager->deleteFile($photoDirectory . '/' . $photo);

        $this->remove($user);
    }

    /**
     * Removes the comment instance from the database.
     *
     * @param Comment $comment
     *
     * @return void
     */
    public function deleteComment(Comment $comment)
    {
        $this->remove($comment);
    }

    /**
     * Removes an entity instance from the database.
     *
     * @param User|Comment $entity
     *
     * @return void
     */
    public function remove($entity)
    {
        $this->em->remove($entity);
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
