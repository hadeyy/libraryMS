<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 1/9/2018
 * Time: 1:43 PM
 */

namespace App\Controller\user;


use App\Entity\Activity;
use App\Entity\BookReservation;
use App\Entity\User;
use App\Repository\ActivityRepository;
use App\Repository\BookReservationRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/user")
 *
 * @Security("has_role('ROLE_USER')")
 */
class UserController extends Controller
{
    /**
     * @Route("/profile", name="profile")
     */
    public function profileAction(): Response
    {
        /** @var BookReservationRepository $reservationRepo */
        $reservationRepo = $this->getDoctrine()->getRepository(BookReservation::class);
        /** @var User $user */
        $user = $this->getUser();

        $activeReservations = $reservationRepo->findCurrentReservations($user);
        $closedReservations = $reservationRepo->findPastReservations($user);

        return $this->render(
            'user/profile.html.twig',
            [
                'activeReservations' => $activeReservations,
                'closedReservations' => $closedReservations,
            ]
        );
    }

    /**
     * @Route("/activity", name="activity")
     */
    public function activityAction(): Response
    {
        /** @var ActivityRepository $activityRepo */
        $activityRepo = $this->getDoctrine()->getRepository(Activity::class);

        $activities = $activityRepo->findAllUserActivities($this->getUser());

        return $this->render('user/activities.html.twig', ['activities' => $activities]);
    }

    /**
     * @Route("/notifications", name="notifications")
     */
    public function notificationAction(): Response
    {


        return $this->render('user/notifications.html.twig');
    }
}
