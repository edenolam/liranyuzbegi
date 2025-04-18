<?php

namespace App\Form;

use App\Entity\Song;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class SongType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, ['label' => 'song.title'])
            ->add('artist', TextType::class, ['label' => 'song.artist'])
            ->add('year', IntegerType::class, ['label' => 'song.year'])
            ->add('album', TextType::class, [
                'required' => false,
                'label' => 'song.album'
            ])
            ->add('coverImage', FileType::class, [
                'label' => 'song.cover_image',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'mimeTypes' => ['image/jpeg', 'image/png'],
                    ])
                ],
            ])
            ->add('audioFile', FileType::class, [
                'label' => 'song.audio_file',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'mimeTypes' => ['audio/mpeg'],
                        'mimeTypesMessage' => 'song.audio_file_invalid',
                    ])
                ],
            ]);

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Song::class,
        ]);
    }
}
