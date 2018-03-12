<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 1/24/2018
 * Time: 10:00 AM
 */

namespace App\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class CommentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('content', TextareaType::class, [
            'data' => '',
            'constraints' => [
                new NotBlank(),
                new Length([
                    'min' => 2,
                    'minMessage' => 'Comment must be at least {{ limit }} characters long.',
                    'max' => 350,
                    'maxMessage' => 'Comment cannot be longer than {{ limit }} characters.',
                ]),
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}
