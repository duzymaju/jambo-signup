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
class PatrolMemberType extends AbstractType
{
    /** @var int */
    private $regionId;

    /**
     * Constructor
     *
     * @param TranslatorInterface $translator        translator
     * @param RegistrationLists   $registrationLists registration lists
     * @param int                 $regionId          region ID
     */
    public function __construct(TranslatorInterface $translator, RegistrationLists $registrationLists, $regionId)
    {
        parent::__construct($translator, $registrationLists);
        $this->loadValidation('Participant');
        $this->regionId = $regionId;
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
            ]))
            ->add('guardianName', TextType::class, $this->mergeOptions('guardianName', [
                'label' => 'form.guardian_name',
            ]))
            ->add('guardianPhone', TextType::class, $this->mergeOptions('guardianPhone', [
                'label' => 'form.guardian_phone',
            ]))
            ->add('gradeId', ChoiceType::class, $this->mergeOptions('gradeId', [
                'choices' => $this->registrationLists->getGradeLabels(),
                'label' => 'form.grade',
            ]))
            ->add('districtId', ChoiceType::class, $this->mergeOptions('districtId', [
                'choices' => array_flip($this->registrationLists->getDistricts($this->regionId, false)),
                'label' => $this->translator->trans('form.district'),
                'required' => false,
                'translation_domain' => false,
            ]))
            ->add('shirtSize', ChoiceType::class, $this->mergeOptions('shirtSize', [
                'choices' => $this->registrationLists->getShirtSizeLabels(),
                'label' => 'form.shirt_size',
            ]))
            ->add('specialDiet', TextType::class, $this->mergeOptions('specialDiet', [
                'label' => 'form.special_diet',
                'required' => false,
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
        return 'patrol_member';
    }
}
