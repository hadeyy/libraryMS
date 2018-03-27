<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 2/14/2018
 * Time: 2:47 PM
 */

namespace App\Service;


use App\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UserManager
{
    private $em;
    private $repository;
    private $fileManager;
    private $photoDirectory;

    public function __construct(
        ManagerRegistry $doctrine,
        FileManager $fileManager,
        ActivityManager $activityManager,
        $userPhotoDirectory
    )
    {
        $this->em = $doctrine->getManager();
        $this->repository = $doctrine->getRepository(User::class);
        $this->fileManager = $fileManager;
        $this->photoDirectory = $userPhotoDirectory;
    }

    /**
     * Converts an associative array of data to an instance of User.
     *
     * @param array $data Data about the user.
     *
     * @return User
     */
    public function createUserFromArray(array $data): User
    {
        return new User(
            $data['firstName'],
            $data['lastName'],
            $data['username'],
            $data['email'],
            $data['photo'],
            $data['plainPassword']
        );
    }

    /**
     * Converts an instance of User to an associative array.
     *
     * @param User $user
     *
     * @return array
     */
    public function createArrayFromUser(User $user): array
    {
        $photoPath = $this->photoDirectory . '/' . $user->getPhoto();

        return [
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'username' => $user->getUsername(),
            'email' => $user->getEmail(),
            'photo' => $this->fileManager->createFileFromPath($photoPath),
        ];
    }

    /**
     * Sets the user's photo and password, assigns the role
     * and saves the user to the database.
     *
     * @param User $user .
     * @param string $filename Photo filename including the file extension.
     * @param string $password Encoded password.
     * @param string $role Role to assign.
     *
     * @return void
     */
    public function register(
        User $user,
        string $filename,
        string $password,
        string $role
    )
    {
        $user->setPhoto($filename);
        $user->setPassword($password);
        $user->addRole($role);

        $this->save($user);
    }

    /**
     * Returns an array of the user's favorite books.
     *
     * @param User $user
     *
     * @return ArrayCollection
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findFavoriteBooks(User $user)
    {
        $user = $this->repository->findUserJoinedToFavoriteBooks($user);

        return $user->getFavorites();
    }

    /**
     * Returns an array of the user's book reservations that match the status.
     *
     * @param User $user
     * @param string $status
     *
     * @return ArrayCollection
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findReservationsByStatus(User $user, string $status)
    {
        $user = $this->repository->findUserJoinedToReservationsByStatus($user, $status);

        return $user->getBookReservations();
    }

    /**
     * Changes an instance of User using data from an associative array.
     * Removes the previous photo if there was one when a new one has been provided.
     *
     * @param User $user
     * @param array $data New data that will replace existing.
     *
     * @return void
     */
    public function updateProfile(User $user, array $data)
    {
        $photo = $data['photo'];
        if ($photo instanceof UploadedFile) {
            $photoPath = $this->photoDirectory . '/' . $user->getPhoto();
            $this->fileManager->deleteFile($photoPath);

            $filename = $this->fileManager->upload($photo, $this->photoDirectory);
            $user->setPhoto($filename);
        }

        $user->setFirstName($data['firstName']);
        $user->setLastName($data['lastName']);
        $user->setUsername($data['username']);
        $user->setEmail($data['email']);

        $this->saveChanges();
    }

    /**
     * Looks for all users that match the role.
     *
     * @param string $role
     *
     * @return User[]
     */
    public function findUsersByRole(string $role)
    {
        return $this->repository->findUsersByRole($role);
    }

    /**
     * Calls entity manager to make the instance managed and persistent and
     * to save all changes made to objects to the database.
     *
     * @param User $user
     *
     * @return void
     */
    public function save(User $user)
    {
        $this->em->persist($user);
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


    /**
     * Returns the path to the user photo directory.
     *
     * @return string
     */
    public function getPhotoDirectory()
    {
        return $this->photoDirectory;
    }
}
