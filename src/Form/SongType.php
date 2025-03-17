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
            ->add('title', TextType::class, ['label' => 'Titre'])
            ->add('artist', TextType::class, ['label' => 'Artiste'])
            ->add('year', IntegerType::class, ['label' => 'Année'])
            ->add('album', TextType::class, ['required' => false, 'label' => 'Album'])
            ->add('coverImage', FileType::class, [
                'label' => 'Image de couverture',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File(['mimeTypes' => ['image/jpeg', 'image/png']])
                ],
            ])
            ->add('audioFile', FileType::class, [
                'label' => 'Fichier audio (MP3)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File(['mimeTypes' => ['audio/mpeg'], 'mimeTypesMessage' => 'Veuillez uploader un fichier MP3'])
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
