<?php

namespace JamboBundle\Form\Type;

use JamboBundle\Entity\Patrol;
use JamboBundle\Form\RegistrationLists;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

/*
 * Form type
 */
class PatrolType extends AbstractType
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
        $this->loadValidation('Patrol');
        $this->regionId = $regionId;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        unset($options);

        $builder
            ->add('name', TextType::class, $this->mergeOptions('name', [
                'label' => 'form.patrol_name',
            ]))
            ->add('districtId', ChoiceType::class, $this->mergeOptions('districtId', [
                'choices' => array_flip($this->registrationLists->getDistricts($this->regionId)),
                'label' => $this->translator->trans('form.district'),
                'translation_domain' => false,
            ]))
            ->add('methodologyGroupId', ChoiceType::class, $this->mergeOptions('methodologyGroupId', [
                'choices' => array_flip($this->registrationLists->getMethodologyGroups()),
                'label' => $this->translator->trans('form.methodology_group'),
                'translation_domain' => false,
            ]))
            ->add('members', CollectionType::class, $this->mergeOptions('members', [
                'allow_add' => true,
                'allow_delete' => false,
                'by_reference' => false,
                'entry_type' => PatrolMemberType::class,
                'validation_groups' => [
                    'troopMember',
                ],
            ]))
            ->add('comments', TextType::class, $this->mergeOptions('comments', [
                'label' => 'form.comments',
                'required' => false,
            ]))
            ->add('personalData', CheckboxType::class, $this->mergeOptions('personalData', [
                'constraints' => [
                    new NotBlank(),
                ],
                'label' => 'form.personal_data',
                'mapped' => false,
            ]))
            ->add('save', SubmitType::class, [
                'label' => 'form.save',
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'cascade_validation' => true,
            'data_class' => Patrol::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'patrol';
    }
}
