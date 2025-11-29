<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class NewGalleryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->
            add('name', TextType::class, [
                'label' => 'Gallery Name',
                'constraints' => [
                    new NotBlank(),
                    new Length(min: 3, max: 20),
                ],
                'attr' => [
                    'placeholder' => 'Some photo'
                ]
                ]);
    }
}