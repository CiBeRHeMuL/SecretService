<?php

namespace App\Presentation\Web\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use function Symfony\Component\Translation\t;

class CreateMessageForm extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('attr', ['class' => 'w-100']);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'text',
                TextareaType::class,
                [
                    'label' => t('secret_message'),
                    'required' => false,
                    'attr' => [
                        'placeholder' => t('secret_message'),
                        'rows' => 8,
                        'style' => 'height: auto !important;',
                    ],
                    'row_attr' => [
                        'class' => 'form-floating mb-3',
                    ],
                    'empty_data' => '',
                ],
            )
            ->add(
                'files',
                FileType::class,
                [
                    'label' => false,
                    'multiple' => true,
                    'required' => false,
                ],
            )
            ->add(
                'submit',
                SubmitType::class,
                [
                    'attr' => [
                        'class' => 'w-100 btn btn-outline-success',
                    ],
                ],
            );
    }
}
