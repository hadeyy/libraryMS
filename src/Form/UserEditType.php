<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 3/8/2018
 * Time: 5:17 PM
 */

namespace App\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Length([
                        'min' => 2,
                        'max' => 50,
                        'minMessage' => 'First name must be at least {{ limit }} characters long.',
                        'maxMessage' => 'First name cannot be longer than {{ limit }} characters.',
                    ]),
                ],
            ])
            ->add('lastName', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Length([
                        'min' => 2,
                        'max' => 50,
                        'minMessage' => 'Last name must be at least {{ limit }} characters long.',
                        'maxMessage' => 'Last name cannot be longer than {{ limit }} characters.',
                    ]),
                ],
            ])
            ->add('username', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Length([
                        'min' => 2,
                        'max' => 50,
                        'minMessage' => 'Last name must be at least {{ limit }} characters long.',
                        'maxMessage' => 'Last name cannot be longer than {{ limit }} characters.',
                    ]),
                ],
            ])
            ->add('email', EmailType::class, [
                'constraints' => [
                    new NotBlank()
                ],
            ])
            ->add('photo', FileType::class, [
                'constraints' => [
                    new Image([
                        'minWidth' => 50,
                        'maxWidth' => 5000,
                        'minWidthMessage' => 'Minimum width expected is {{ min_width }}px.',
                        'maxWidthMessage' => 'Allowed maximum width is {{ max_width }}px.',
                        'minHeight' => 50,
                        'maxHeight' => 5000,
                        'minHeightMessage' => 'Minimum height expected is {{ min_height }}px.',
                        'maxHeightMessage' => 'Allowed maximum height is {{ max_height }}px.',
                    ])
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}
