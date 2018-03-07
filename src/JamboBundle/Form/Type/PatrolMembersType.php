<?php

namespace JamboBundle\Form\Type;

use JamboBundle\Entity\Patrol;
use JamboBundle\Form\RegistrationLists;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

/*
 * Form type
 */
class PatrolMembersType extends AbstractType
{
    /**
     * Constructor
     *
     * @param TranslatorInterface $translator        translator
     * @param RegistrationLists   $registrationLists registration lists
     */
    public function __construct(TranslatorInterface $translator, RegistrationLists $registrationLists)
    {
        parent::__construct($translator, $registrationLists);
        $this->loadValidation('Patrol');
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        unset($options);

        $builder
            ->add('members', CollectionType::class, $this->mergeOptions('members', [
                'allow_add' => true,
                'allow_delete' => false,
                'by_reference' => false,
                'entry_options' => [
                    'districtRequired' => true,
                ],
                'entry_type' => PatrolMemberType::class,
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
            'data_class' => Patrol::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'patrol_members';
    }
}
