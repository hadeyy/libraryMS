<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 3/5/2018
 * Time: 1:54 PM
 */

namespace App\Controller\admin;


use App\Entity\Book;
use App\Entity\Comment;
use App\Entity\User;
use App\Service\AppManager;
use App\Service\UserManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Security("has_role('ROLE_ADMIN')")
 */
class AdminController extends AbstractController
{
    private $appManager;
    private $userManager;

    public function __construct(
        AppManager $appManager,
        UserManager $userManager
    ) {
        $this->appManager = $appManager;
        $this->userManager = $userManager;
    }

    public function showAllUsers()
    {
        return $this->render(
            'librarian/users.html.twig',
            ['users' => $this->userManager->findUsersByRole('ROLE_USER')]
        );
    }

    public function deleteUser(User $user)
    {
        $this->appManager->deleteUser($user);

        return $this->redirectToRoute('show-users');
    }

    public function editUser(Request $request, User $user)
    {
        $defaultData = ['message' => 'Select role'];
        $roleForm = $this->createFormBuilder($defaultData)
            ->add('role', ChoiceType::class, [
                'placeholder' => '- Choose new role -',
                'choices' => [
                    'ROLE_READER' => 'ROLE_READER',
                    'ROLE_LIBRARIAN' => 'ROLE_LIBRARIAN',
                    'ROLE_ADMIN' => 'ROLE_ADMIN',
                ],
            ])
            ->getForm();

        $roleForm->handleRequest($request);
        if ($roleForm->isSubmitted() && $roleForm->isValid()) {
            $formData = $roleForm->getData();
            $role = $formData['role'];
            $this->appManager->changeRole($user, $role);

            return $this->redirectToRoute('show-users');
        }

        return $this->render(
            'admin/edit_user.html.twig',
            [
                'user' => $user,
                'form' => $roleForm->createView(),
            ]
        );
    }

    /**
     * @param User $user
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function showUser(User $user)
    {
        return $this->render(
            'user/profile.html.twig',
            [
                'user' => $user,
                'favorites' => $this->userManager->getFavoriteBooks($user),
                'activeReservations' => $this->userManager
                    ->findReservationsByStatus($user, 'reading'),
                'closedReservations' => $this->userManager
                    ->findReservationsByStatus($user, 'returned'),
            ]
        );
    }

    public function showActivity()
    {
        $activity = $this->appManager->getAllActivity();

        return $this->render(
            'admin/activity.html.twig',
            ['activities' => $activity]
        );
    }

    public function deleteComment(Comment $comment, Book $book)
    {
        $this->appManager->deleteComment($comment);

        return $this->redirectToRoute('show-book', ['book' => $book]);
    }
}
