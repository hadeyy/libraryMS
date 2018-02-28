<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 2/14/2018
 * Time: 2:47 PM
 */

namespace App\Service;


use App\Entity\User;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UserManager
{
    private $em;
    private $repository;
    private $fileManager;
    private $passwordManager;
    private $photoDirectory;
    private $photoName;
    private $photoPath;

    public function __construct(
        ManagerRegistry $doctrine,
        FileManager $fileManager,
        PasswordManager $passwordManager,
        $userPhotoDirectory
    ) {
        $this->em = $doctrine->getManager();
        $this->repository = $doctrine->getRepository(User::class);
        $this->fileManager = $fileManager;
        $this->passwordManager = $passwordManager;
        $this->photoDirectory = $userPhotoDirectory;
    }

    /**
     * @param User $user Created user object.
     * @param string $filename Uploaded photo file name and extension.
     * @param string $password User's encoded password.
     * @param string $role User's role.
     */
    public function register(
        User $user,
        string $filename,
        string $password,
        string $role
    ) {
        $this->setUserPhoto($user, $filename);
        $this->setUserPassword($user, $password);
        $this->addRole($user, $role);
    }

    public function getFavoriteBooks(User $user)
    {
        return $this->repository->getFavoriteBooks($user);
    }

    /**
     * @param User $user
     * @param string $status
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getReservationsByStatus(User $user, string $status)
    {
        $user = $this->repository->findUserJoinedToReservations($user, $status);

        return $user->getBookReservations();
    }

    public function changePhotoFromPathToFile(User $user)
    {
        $this->setPhotoName($user->getPhoto());
        $this->setPhotoPath($this->photoDirectory . '/' . $this->photoName);
        $this->setUserPhoto($user, $this->fileManager->createFileFromPath($this->getPhotoPath()));
    }

    public function updateProfile(User $user)
    {
        $photo = $this->getUserPhoto($user);
        if ($photo instanceof UploadedFile) {
            $this->fileManager->deleteFile($this->getPhotoPath());
            $filename = $this->fileManager->upload($photo, $this->photoDirectory);
        } else {
            $filename = $this->getPhotoName();
        }
        $encodedPassword = $this->passwordManager->encode($user);

        $this->setUserPhoto($user, $filename);
        $this->setUserPassword($user, $encodedPassword);

        $this->saveChanges();
    }

    public function getActivity(User $user)
    {
        return $user->getActivities();
    }

    public function findUsersByRole(string $role)
    {
        return $this->repository->findUsersByRole($role);
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

    public function setUserPhoto(User $user, $photo)
    {
        $user->setPhoto($photo);
    }

    public function getUserPhoto(User $user)
    {
        return $user->getPhoto();
    }

    public function setUserPassword(User $user, $password)
    {
        $user->setPassword($password);
    }

    public function addRole(User $user, string $role)
    {
        $user->addRole($role);
    }

    public function setPhotoPath(string $photoPath)
    {
        $this->photoPath = $photoPath;
    }

    public function getPhotoPath()
    {
        return $this->photoPath;
    }

    public function setPhotoName(string $photoName)
    {
        $this->photoName = $photoName;
    }

    public function getPhotoName()
    {
        return $this->photoName;
    }
}
