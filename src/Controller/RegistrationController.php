<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 1/8/2018
 * Time: 2:01 PM
 */

namespace App\Controller;


use App\Entity\User;
use App\Form\UserType;
use App\Service\FileManager;
use App\Service\PasswordManager;
use App\Service\UserManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RegistrationController extends AbstractController
{
    private $userManager;
    private $passwordManager;
    private $fileManager;
    private $userPhotoDirectory;
    private $defaultUserRole;

    public function __construct(
        UserManager $userManager,
        PasswordManager $passwordManager,
        FileManager $fileManager,
        string $userPhotoDirectory,
        string $defaultUserRole
    ) {
        $this->userManager = $userManager;
        $this->passwordManager = $passwordManager;
        $this->fileManager = $fileManager;
        $this->userPhotoDirectory = $userPhotoDirectory;
        $this->defaultUserRole = $defaultUserRole;
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function register(Request $request)
    {
        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('index');
        }

        $user = new User();

        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $filename = $this->fileManager->upload($user->getPhoto(), $this->userPhotoDirectory);
            $encodedPassword = $this->passwordManager->encode($user);

            $this->userManager->register($user, $filename, $encodedPassword, $this->defaultUserRole);
            $this->userManager->save($user);

            $this->addFlash('success', 'Registration successful!');

            return $this->redirectToRoute('login');
        }

        return $this->render(
            'registration/registration.html.twig',
            ['form' => $form->createView()]
        );
    }
}
