<?php

namespace JamboBundle\Form\Type;

use JamboBundle\Entity\Participant;
use JamboBundle\Form\RegistrationLists;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

/*
 * Form type
 */
class TroopMemberType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function __construct(TranslatorInterface $translator, RegistrationLists $registrationLists)
    {
        parent::__construct($translator, $registrationLists);
        $this->loadValidation('Participant');
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        unset($options);

        $builder
            ->add('firstName', TextType::class, $this->mergeOptions('firstName', [
                'label' => 'form.first_name',
            ]))
            ->add('lastName', TextType::class, $this->mergeOptions('lastName', [
                'label' => 'form.last_name',
            ]))
            ->add('address', TextType::class, $this->mergeOptions('address', [
                'label' => 'form.address',
            ]))
            ->add('phone', TextType::class, $this->mergeOptions('phone', [
                'label' => 'form.phone',
            ]))
            ->add('email', EmailType::class, $this->mergeOptions('email', [
                'label' => 'form.email',
            ]))
            ->add('pesel', TextType::class, $this->mergeOptions('pesel', [
                'label' => 'form.pesel',
                'required' => false,
            ]))
            ->add('fatherName', TextType::class, $this->mergeOptions('fatherName', [
                'label' => 'form.father_name',
            ]))
            ->add('emergencyPhone', TextType::class, $this->mergeOptions('emergencyPhone', [
                'label' => 'form.emergency_phone',
            ]))
            ->add('gradeId', ChoiceType::class, $this->mergeOptions('gradeId', [
                'choices' => $this->registrationLists->getGradeLabels(),
                'label' => 'form.grade',
            ]))
            ->add('shirtSize', ChoiceType::class, $this->mergeOptions('shirtSize', [
                'choices' => $this->registrationLists->getShirtSizeLabels(),
                'label' => 'form.shirt_size',
            ]))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'troop_member';
    }
}
