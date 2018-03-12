<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 2/5/2018
 * Time: 3:02 PM
 */

namespace App\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class AuthorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName', TextType::class,[
                'constraints' => [
                    new NotBlank(),
                    new Length([
                        'min' => 2,
                        'minMessage' => 'First name must be at least {{ limit }} characters long',
                        'max' => 50,
                        'maxMessage' => 'First name cannot be longer than {{ limit }} characters',
                    ]),
                ],
            ])
            ->add('lastName', TextType::class, [
                'required' => false,
            ])
            ->add('portrait', FileType::class, [
                'required' => false,
            ])
            ->add('country', TextType::class,[
                'constraints' => [
                    new NotBlank(),
                    new Length([
                        'min' => 2,
                        'minMessage' => 'Country name must be at least {{ limit }} characters long',
                        'max' => 50,
                        'maxMessage' => 'Country name cannot be longer than {{ limit }} characters',
                    ]),
                ],
            ])
            ->add('save', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}
