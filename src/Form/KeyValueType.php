<?php

namespace App\Form;

use App\Entity\KeyValue;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class KeyValueType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('key', TextType::class, ['attr'=>['autocomplete' => 'off']])
            ->add('value', TextType::class, ['attr'=>['autocomplete' => 'off']])
            ->add('save', SubmitType::class, ['label' => $options['save_button_label']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => KeyValue::class,
            'save_button_label' => 'Save',
        ]);
    }
}
