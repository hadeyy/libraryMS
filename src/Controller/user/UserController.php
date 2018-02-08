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
use App\Form\UserType;
use App\Repository\ActivityRepository;
use App\Repository\BookReservationRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

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
     * @Route("/profile/edit", name="edit-profile")
     *
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     *
     * @return RedirectResponse|Response
     */
    public function editProfile(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        /** @var BookReservationRepository $reservationRepo */
        $reservationRepo = $this->getDoctrine()->getRepository(BookReservation::class);
        /** @var User $user */
        $user = $this->getUser();

        $userPhoto = $user->getPhoto();
        $photoPath = $this->getParameter('user_photo_directory') . '/' . $userPhoto;
        $user->setPhoto(new File($photoPath));

        $activeReservations = $reservationRepo->findCurrentReservations($user);
        $closedReservations = $reservationRepo->findPastReservations($user);

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $file = $user->getPhoto();
            if ($file instanceof UploadedFile) {
                unlink($photoPath);
                $this->uploadPhoto($file, $user);
            } else {
                $user->setPhoto($userPhoto);
            }

            $password = $passwordEncoder->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute(
                'profile',
                [
                    'activeReservations' => $activeReservations,
                    'closedReservations' => $closedReservations,
                ]
            );
        }

        return $this->render('user/edit.html.twig', ['form' => $form->createView()]);
    }

    private function uploadPhoto(UploadedFile $file, User $user)
    {
        $fileName = md5(uniqid()) . '_' . (string)date('dmYHms') . '.' . $file->guessExtension();
        $file->move(
            $this->getParameter('user_photo_directory'),
            $fileName
        );

        $user->setPhoto($fileName);
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
