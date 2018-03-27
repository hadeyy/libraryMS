<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 3/5/2018
 * Time: 1:54 PM
 */

namespace App\Controller;


use App\Entity\Book;
use App\Entity\Comment;
use App\Entity\User;
use App\Service\ActivityManager;
use App\Service\AppManager;
use App\Service\BookReservationManager;
use App\Service\UserManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Security("has_role('ROLE_ADMIN')")
 */
class AdminController extends AbstractController
{
    private $appManager;
    private $userManager;
    private $reservationManager;
    private $activityManager;

    public function __construct(
        AppManager $appManager,
        UserManager $userManager,
        BookReservationManager $reservationManager,
        ActivityManager $activityManager
    )
    {
        $this->appManager = $appManager;
        $this->userManager = $userManager;
        $this->reservationManager = $reservationManager;
        $this->activityManager = $activityManager;
    }

    /**
     * @return Response
     */
    public function showAllUsers()
    {
        return $this->render(
            'librarian/users.html.twig',
            ['users' => $this->userManager->findUsersByRole('ROLE_USER')]
        );
    }

    /**
     * @param User $user
     *
     * @return RedirectResponse
     */
    public function deleteUser(User $user)
    {
        $this->appManager->deleteUser($user);

        return $this->redirectToRoute('show-users');
    }

    /**
     * @param Request $request
     * @param User $user
     *
     * @return RedirectResponse|Response
     */
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
     * @return Response
     */
    public function showUser(User $user)
    {
        return $this->render(
            'user/profile.html.twig',
            [
                'user' => $user,
                'favorites' => $this->userManager->findFavoriteBooks($user),
                'activeReservations' => $this->reservationManager
                    ->findUserReservationsByStatus($user, 'reading'),
                'closedReservations' => $this->reservationManager
                    ->findUserReservationsByStatus($user, 'returned'),
            ]
        );
    }

    /**
     * @return Response
     */
    public function showActivity()
    {
        $activity = $this->activityManager->findAllActivity();

        return $this->render(
            'admin/activity.html.twig',
            ['activities' => $activity]
        );
    }

    /**
     * @param string $filter
     *
     * @return Response
     */
    public function showActivityFilteredByDate(string $filter)
    {
        $activity = $this->activityManager->findActivityByDateLimit($filter);

        return $this->render(
            'admin/activity.html.twig',
            ['activities' => $activity]
        );
    }

    /**
     * @param Comment $comment
     * @param Book $book
     *
     * @ParamConverter("book", class="App\Entity\Book", options={"mapping": {"bookSlug": "slug"}})
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteComment(Comment $comment, Book $book)
    {
        $this->appManager->deleteComment($comment);
        $author = $book->getAuthor();

        return $this->redirectToRoute(
            'show-book',
            [
                'authorSlug' => $author->getSlug(),
                'bookSlug' => $book->getSlug()
            ]
        );
    }
}
