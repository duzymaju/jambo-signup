<?php

namespace JamboBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/*
 * Form type
 */
class TroopEditType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        unset($options);

        $builder
            ->add('status', ChoiceType::class, $this->mergeOptions('status', [
                'choices' => $this->registrationLists->getStatusLabels(),
                'label' => 'form.status',
            ]))
            ->add('comments', TextType::class, $this->mergeOptions('comments', [
                'label' => 'form.comments',
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
        return 'troop_edit';
    }
}
