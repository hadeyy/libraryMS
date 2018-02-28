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
        $user->setPhoto($filename);
        $user->setPassword($password);
        $user->addRole($role);
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
        $user->setPhoto($this->fileManager->createFileFromPath($this->getPhotoPath()));
    }

    public function updateProfile(User $user)
    {
        $photo = $user->getPhoto();
        if ($photo instanceof UploadedFile) {
            $this->fileManager->deleteFile($this->getPhotoPath());
            $filename = $this->fileManager->upload($photo, $this->photoDirectory);
        } else {
            $filename = $this->getPhotoName();
        }
        $encodedPassword = $this->passwordManager->encode($user);

        $user->setPhoto($filename);
        $user->setPassword($encodedPassword);

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
