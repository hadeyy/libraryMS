<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 1/9/2018
 * Time: 1:43 PM
 */

namespace App\Controller\user;


use App\Entity\Book;
use App\Entity\User;
use App\Form\UserType;
use App\Service\ActivityManager;
use App\Service\UserManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

/**
 * @Security("has_role('ROLE_USER')")
 */
class UserController extends AbstractController
{
    private $user;
    private $userManager;
    private $activityManager;

    public function __construct(
        UserManager $userManager,
        TokenStorage $tokenStorage,
        ActivityManager $activityManager
    ) {
        $this->userManager = $userManager;
        $this->activityManager = $activityManager;
        $this->user = $tokenStorage->getToken()->getUser();
    }

    /**
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function profile()
    {
        return $this->render(
            'user/profile.html.twig',
            [
                'user' => $this->user,
                'favorites' => $this->userManager->getFavoriteBooks($this->user),
                'activeReservations' => $this->userManager->getReservationsByStatus($this->user, 'reading'),
                'closedReservations' => $this->userManager->getReservationsByStatus($this->user, 'returned'),
            ]
        );
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse|Response
     */
    public function editProfile(Request $request)
    {
        $this->userManager->changePhotoFromPathToFile($this->user);

        $form = $this->createForm(UserType::class, $this->user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->userManager->updateProfile($this->user);

            return $this->redirectToRoute('profile');
        }

        return $this->render('user/edit.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @return Response
     */
    public function activity()
    {
        $activities = $this->userManager->getActivity($this->user);

        return $this->render('user/activities.html.twig', ['activities' => $activities]);
    }

    /**
     * @TODO FIXME
     * @return Response
     */
    public function notifications()
    {


        return $this->render('user/notifications.html.twig');
    }

    /**
     * @TODO FIXME
     *
     * @param User $user
     * @param Book $book
     */
    public function toggleFavorite(User $user, Book $book)
    {
        $userFavorites = $this->userManager->getFavorites($user);

        $isAFavorite = $userFavorites->contains($book);

        if ($isAFavorite) {
            $this->userManager->removeFavorite($user, $book);
            $this->activityManager->log($user, $book, 'Removed a book from favorites');
        } else {
            $this->userManager->addFavorite($user, $book);
            $this->activityManager->log($user, $book, 'Added a book to favorites');
        }
    }
}
