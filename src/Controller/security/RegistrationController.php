<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 1/8/2018
 * Time: 2:01 PM
 */

namespace App\Controller\security;


use App\Entity\User;
use App\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class RegistrationController extends Controller
{
    /**
     * @Route("/register", name="registration")
     *
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return Response
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $file */
            $file = $user->getPhoto();

            $extension = $file->guessExtension();
            $filename = md5(uniqid()) . '_' . (string)date('dmYHms') . '.' . $extension;
            $path = $this->getParameter('user_photo_directory');

            $file->move($path, $filename);

            $user->setPhoto($filename);

            $password = $passwordEncoder->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);
            $user->addRole('ROLE_READER');
            $user->setRegisteredAt(new \DateTime(date('d-m-Y')));

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('registered');
        }

        return $this->render('registration/registration.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/registration/success", name="registered")
     */
    public function registrationSuccess()
    {
        return $this->render('registration/registered.html.twig');
    }
}
