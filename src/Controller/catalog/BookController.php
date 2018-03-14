<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 2/26/2018
 * Time: 3:59 PM
 */

namespace App\Controller\catalog;


use App\Entity\Book;
use App\Entity\User;
use App\Form\CommentType;
use App\Service\ActivityManager;
use App\Service\BookReservationManager;
use App\Service\CommentManager;
use App\Service\RatingManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class BookController extends AbstractController
{
    private $commentManager;
    private $ratingManager;
    private $activityManager;
    private $bookReservationManager;
    private $user;

    public function __construct(
        CommentManager $commentManager,
        RatingManager $ratingManager,
        ActivityManager $activityManager,
        BookReservationManager $bookReservationManager,
        TokenStorage $tokenStorage
    ) {
        $this->commentManager = $commentManager;
        $this->ratingManager = $ratingManager;
        $this->activityManager = $activityManager;
        $this->bookReservationManager = $bookReservationManager;
        $this->user = $tokenStorage->getToken()->getUser();
    }

    /**
     * @param Request $request
     * @param Book $book
     *
     * @ParamConverter("book", class="App\Entity\Book")
     *
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function showBook(Request $request, Book $book)
    {
        if ($this->user instanceof User) {
            /** Comment form */
            $commentForm = $this->createForm(CommentType::class);
            $commentForm->handleRequest($request);
            if ($commentForm->isSubmitted() && $commentForm->isValid()) {
                $content = $commentForm->get('content');
                $comment = $this->commentManager->create($this->user, $book, $content);
                $this->commentManager->save($comment);
                $this->activityManager->log($this->user, $book, "Commented on a book's page");
            }

            /** Rating form */
            $defaultData = ['message' => 'Select rating'];
            $ratingForm = $this->createFormBuilder($defaultData)
                ->add('rating', ChoiceType::class, [
                    'placeholder' => '- Choose rating -',
                    'choices' => [
                        '5' => '5',
                        '4' => '4',
                        '3' => '3',
                        '2' => '2',
                        '1' => '1',
                    ],
                ])
                ->getForm();

            $ratingForm->handleRequest($request);
            if ($ratingForm->isSubmitted() && $ratingForm->isValid()) {
                $formData = $ratingForm->getData();
                $value = (int)$formData['rating'];

                $this->ratingManager->rate($book, $this->user, $value);
                $this->activityManager->log($this->user, $book, 'Rated a book');
            }

            $isReserved = $this->bookReservationManager->checkIfIsReserved($book, $this->user);
        }

        return $this->render(
            'catalog/book/show.html.twig',
            [
                'book' => $book,
                'bookRating' => $this->ratingManager->getAverageRating($book),
                'commentForm' => isset($commentForm) ? $commentForm->createView() : null,
                'ratingForm' => isset($ratingForm) ? $ratingForm->createView() : null,
                'isReserved' => isset($isReserved) ? $isReserved : null,
            ]
        );
    }
}
