<?php

namespace App\Form;

use App\Entity\Course;
use App\Entity\CourseClass;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CourseClassType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('description')
            ->add('video', FileType::class, [
                'data_class' => null,
                'label' => 'Video Clase del curso',
            ])
            ->add('previus_class')
            ->add('next_class')
            ->add('course', EntityType::class, [
                'class' => Course::class,
                'choice_label' => 'title'
            ])
            ->add('Guardar', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CourseClass::class,
        ]);
    }
}
