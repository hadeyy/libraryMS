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
    public function getActiveReservations(User $user, string $status = 'reading')
    {
        /** @var User $user */
        $user = $this->repository->findUserJoinedToReservations($user, $status);

        return $user->getBookReservations();
    }

    /**
     * @param User $user
     * @param string $status
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getReturnedReservations(User $user, string $status = 'returned')
    {
        /** @var User $user */
        $user = $this->repository->findUserJoinedToReservations($user, $status);

        return $user->getBookReservations();
    }

    public function changePhotoFromPathToFile(User $user)
    {
        $this->photoName = $user->getPhoto();
        $this->photoPath = $this->photoDirectory . '/' . $this->photoName;
        $user->setPhoto($this->fileManager->createFileFromPath($this->photoPath));
    }

    public function updateProfile(User $user)
    {
        $photo = $user->getPhoto();
        if ($photo instanceof UploadedFile) {
            $this->fileManager->deleteFile($this->photoPath);
            $filename = $this->fileManager->upload($photo, $this->photoDirectory);
        } else {
            $filename = $this->photoName;
        }
        $encodedPassword = $this->passwordManager->encode($user);

        $user->setPhoto($filename);
        $user->setPassword($encodedPassword);

        $this->em->flush();
    }

    public function getActivity(User $user)
    {
        return $user->getActivities();
    }

    public function findUsersByRole(string $role)
    {
        return $this->repository->findUsersByRole($role);
    }
}
