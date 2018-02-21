<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 1/9/2018
 * Time: 1:43 PM
 */

namespace App\Controller\user;


use App\Form\UserType;
use App\Service\FileManager;
use App\Service\PasswordManager;
use App\Service\UserManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Security("has_role('ROLE_USER')")
 */
class UserController extends AbstractController
{
    private $user;
    private $userManager;
    protected $container;

    public function __construct(UserManager $userManager, ContainerInterface $container)
    {
        $this->userManager = $userManager;
        $this->container = $container;
        $this->user = $container->get('security.token_storage')->getToken()->getUser();
    }

    /**
     * @return Response
     */
    public function profile()
    {
        return $this->render(
            'user/profile.html.twig',
            [
                'activeReservations' => $this->userManager->getActiveReservations($this->user),
                'closedReservations' => $this->userManager->getClosedReservations($this->user),
            ]
        );
    }

    /**
     * @param Request $request
     * @param FileManager $fileManager
     * @param PasswordManager $passwordManager
     *
     * @return RedirectResponse|Response
     */
    public function editProfile(
        Request $request,
        PasswordManager $passwordManager
    ) {
        $this->userManager->changePhotoFromPathToFile($this->user);

        $form = $this->createForm(UserType::class, $this->user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->userManager->updateProfile($this->user, $passwordManager);

            return $this->redirectToRoute(
                'profile',
                [
                    'activeReservations' => $this->userManager->getActiveReservations($this->user),
                    'closedReservations' => $this->userManager->getClosedReservations($this->user),
                ]
            );
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
     * @return Response
     */
    public function notifications()
    {


        return $this->render('user/notifications.html.twig');
    }
}
