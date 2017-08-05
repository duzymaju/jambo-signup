<?php

namespace JamboBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/*
 * Form type
 */
class ParticipantEditType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        unset($options);

        $builder
            ->add('comments', TextType::class, $this->mergeOptions('comments', [
                'label' => 'form.comments',
                'required' => false,
            ]))
            ->add('emergencyInfo', TextType::class, $this->mergeOptions('emergencyInfo', [
                'label' => 'form.emergency_info',
                'required' => false,
            ]))
            ->add('emergencyPhone', TextType::class, $this->mergeOptions('emergencyPhone', [
                'label' => 'form.emergency_phone',
                'required' => false,
            ]))
            ->add('save', SubmitType::class, [
                'label' => 'form.save',
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'participant_edit';
    }
}
