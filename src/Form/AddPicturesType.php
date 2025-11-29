<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\Image;

class AddPicturesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('pictures', FileType::class, [
                'label' => 'Select Pictures (Max 20)',
                'multiple' => true,
                'mapped' => false,
                'constraints' => [
                    new Count(
                        max: 20,
                        maxMessage: 'You can upload a maximum of 20 pictures at once'
                    ),
                    new All(
                        new Image(
                            maxSize: '5M',
                            maxSizeMessage: 'Please upload a valid image file (JPG, PNG, GIF, BMP).',
                        ),
                    )
                ]
            ]);
    }
}