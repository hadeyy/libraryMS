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

    public function createUser(array $data): User
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

    public function findAllActivity()
    {
        $repository = $this->em->getRepository(Activity::class);

        return $repository->findRecentActivity();
    }

    public function findActivityByDateLimit(string $filter)
    {
        $repository = $this->em->getRepository(Activity::class);

        $dates = [
            'today' => 'today',
            'this-week' => 'monday this week',
            'this-month' => 'first day of this month',
            'this-year' => 'first day of January this year',
        ];

        return $repository->findActivityByDateLimit($dates[$filter]);
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

    public function remove($entity)
    {
        $this->em->remove($entity);
        $this->saveChanges();
    }
}
