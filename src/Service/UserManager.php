<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 2/14/2018
 * Time: 2:47 PM
 */

namespace App\Service;


use App\Entity\Book;
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
        $this->setPhoto($user, $filename);
        $this->setPassword($user, $password);
        $this->addRole($user, $role);
    }

    /**
     * @param User $user
     *
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getFavoriteBooks(User $user)
    {
        $user = $this->repository->findUserJoinedToFavoriteBooks($user);

        return $user->getFavorites();
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
        $this->setPhotoName($this->getPhoto($user));
        $this->setPhotoPath($this->photoDirectory . '/' . $this->getPhotoName());
        $this->setPhoto($user, $this->fileManager->createFileFromPath($this->getPhotoPath()));
    }

    public function updateProfile(User $user)
    {
        $photo = $this->getPhoto($user);
        if ($photo instanceof UploadedFile) {
            $this->fileManager->deleteFile($this->getPhotoPath());
            $filename = $this->fileManager->upload($photo, $this->photoDirectory);
        } else {
            $filename = $this->getPhotoName();
        }
        $encodedPassword = $this->passwordManager->encode($user);

        $this->setPhoto($user, $filename);
        $this->setPassword($user, $encodedPassword);

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
        $this->saveChanges();
    }

    public function saveChanges()
    {
        $this->em->flush();
    }

    public function setPhoto(User $user, $photo)
    {
        $user->setPhoto($photo);
    }

    public function getPhoto(User $user)
    {
        return $user->getPhoto();
    }

    public function setPassword(User $user, $password)
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

    public function getFavorites(User $user)
    {
        return $user->getFavorites();
    }

    public function addFavorite(User $user, Book $book)
    {
        $user->addFavorite($book);
    }

    public function removeFavorite(User $user, Book $book)
    {
        $user->removeFavorite($book);
    }
}
