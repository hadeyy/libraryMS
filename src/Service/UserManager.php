<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 2/14/2018
 * Time: 2:47 PM
 */

namespace App\Service;


use App\Entity\Activity;
use App\Entity\BookReservation;
use App\Entity\User;
use App\Repository\ActivityRepository;
use App\Repository\BookReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UserManager extends EntityManager
{
    private $photoName;
    private $photoPath;
    private $photoDirectory;

    public function __construct(EntityManagerInterface $manager, ContainerInterface $container)
    {
        parent::__construct($manager, $container);
        $this->photoDirectory = $container->getParameter('user_photo_directory');
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

    public function getActiveReservations(User $user)
    {
        /** @var BookReservationRepository $repository */
        $repository = $this->getRepository(BookReservation::class);

        return $repository->findCurrentReservations($user);
    }

    public function getClosedReservations(User $user)
    {
        /** @var BookReservationRepository $repository */
        $repository = $this->getRepository(BookReservation::class);

        return $repository->findPastReservations($user);
    }

    public function changePhotoFromPathToFile(User $user, FileManager $fileManager)
    {
        $this->photoName = $user->getPhoto();
        $this->photoPath = $this->photoDirectory . '/' . $this->photoName;
        $user->setPhoto($fileManager->createFileFromPath($this->photoPath));
    }

    public function updateProfile(User $user, FileManager $fileManager, PasswordManager $passwordManager)
    {
        $photo = $user->getPhoto();
        if ($photo instanceof UploadedFile) {
            unlink($this->photoPath);
            $filename = $fileManager->upload($photo, $this->photoDirectory);
        } else {
            $filename = $this->photoName;
        }
        $encodedPassword = $passwordManager->encode($user);

        $user->setPhoto($filename);
        $user->setPassword($encodedPassword);

        $this->em->flush();
    }

    public function getActivity(User $user)
    {
        /** @var ActivityRepository $repository */
        $repository = $this->getRepository(Activity::class);

        return $repository->findAllUserActivities($user);
    }
}
