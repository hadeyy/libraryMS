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
    private $photoDirectory;

    public function __construct(
        ManagerRegistry $doctrine,
        FileManager $fileManager,
        $userPhotoDirectory
    ) {
        $this->em = $doctrine->getManager();
        $this->repository = $doctrine->getRepository(User::class);
        $this->fileManager = $fileManager;
        $this->photoDirectory = $userPhotoDirectory;
    }

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

    public function getPhotoDirectory()
    {
        return $this->photoDirectory;
    }
}
