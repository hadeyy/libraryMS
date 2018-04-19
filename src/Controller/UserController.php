<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 1/9/2018
 * Time: 1:43 PM
 */

namespace App\Controller;


use App\Form\PasswordEditType;
use App\Form\UserEditType;
use App\Service\ActivityManager;
use App\Service\BookReservationManager;
use App\Service\PasswordManager;
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
    private $bookReservationManager;
    private $passwordManager;

    public function __construct(
        UserManager $userManager,
        TokenStorage $tokenStorage,
        ActivityManager $activityManager,
        BookReservationManager $bookReservationManager,
        PasswordManager $passwordManager
    )
    {
        $this->userManager = $userManager;
        $this->activityManager = $activityManager;
        $this->user = $tokenStorage->getToken()->getUser();
        $this->bookReservationManager = $bookReservationManager;
        $this->passwordManager = $passwordManager;
    }

    /**
     * @return Response
     */
    public function showProfile()
    {
        return $this->render(
            'user/profile.html.twig',
            [
                'user' => $this->user,
                'favorites' => $this->userManager->findFavoriteBooks($this->user),
                'activeReservations' => $this->bookReservationManager->findUserReservationsByStatus($this->user,
                    'reading'),
                'closedReservations' => $this->bookReservationManager->findUserReservationsByStatus($this->user,
                    'returned'),
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
        $data = $this->userManager->createArrayFromUser($this->user);

        $form = $this->createForm(UserEditType::class, $data);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->userManager->updateProfile($this->user, $form->getData());

            $this->addFlash('success', 'Profile info updated.');

            return $this->redirectToRoute('show-profile');
        }

        return $this->render('user/edit.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse|Response
     */
    public function changePassword(Request $request)
    {
        $form = $this->createForm(PasswordEditType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $newPassword = $data['newPassword'];
            $this->passwordManager->changePassword($this->user, $newPassword);

            $this->addFlash('success', 'Password changed.');

            return $this->redirectToRoute('show-profile');
        }

        return $this->render(
            'user/edit_password.html.twig',
            ['form' => $form->createView()]
        );
    }

    /**
     * @return Response
     */
    public function showActivity()
    {
        $activities = $this->activityManager->findUserActivity($this->user);

        return $this->render(
            'user/activities.html.twig',
            ['activities' => $activities]
        );
    }

    /**
     * @param string $filter
     *
     * @return Response
     */
    public function showActivityFilteredByDate(string $filter)
    {
        $activity = $this->activityManager->findUserActivityByDateLimit($this->user, $filter);

        return $this->render(
            'user/activities.html.twig',
            ['activities' => $activity]
        );
    }

    /**
     * @return Response
     */
    public function showReservations()
    {
        return $this->render(
            'user/reservations.html.twig',
            [
                'reserved' => $this->bookReservationManager->findUserReservationsByStatus($this->user, 'reserved'),
                'reading' => $this->bookReservationManager->findUserReservationsByStatus($this->user, 'reading'),
                'returned' => $this->bookReservationManager->findUserReservationsByStatus($this->user, 'returned'),
                'canceled' => $this->bookReservationManager->findUserReservationsByStatus($this->user, 'canceled'),
            ]
        );
    }

    /**
     * @return RedirectResponse
     */
    public function checkActiveReservations()
    {
        $approachingEndDate = $this->bookReservationManager->checkReservationsForApproachingReturnDate($this->user);
        $missedEndDate = $this->bookReservationManager->checkReservationsForMissedReturnDate($this->user);

        foreach ($approachingEndDate as $reservation) {
            $book = $reservation->getBook();
            $date = $reservation->getDateTo();
            $this->addFlash(
                'warning',
                '"' . $book . '"' . ' by ' . $book->getAuthor() . ' has to be returned until ' .
                date_format($date, 'd-m-Y') . ' !'
            );
        }

        foreach ($missedEndDate as $reservation) {
            $book = $reservation->getBook();
            $this->addFlash(
                'danger',
                'Missed return date for "' . $book . '" by ' . $book->getAuthor() . ' !'
            );
        }

        return $this->redirectToRoute('index');
    }
}
