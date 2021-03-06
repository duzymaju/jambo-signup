<?php

namespace JamboBundle\Form\Type;

use JamboBundle\Entity\Troop;
use JamboBundle\Form\RegistrationLists;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

/*
 * Form type
 */
class TroopType extends AbstractType
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
        $this->loadValidation('Troop');
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        unset($options);

        $builder
            ->add('name', TextType::class, $this->mergeOptions('name', [
                'label' => 'form.troop_name',
            ]))
            ->add('comments', TextType::class, $this->mergeOptions('comments', [
                'label' => 'form.comments',
                'required' => false,
            ]))
            ->add('rules', CheckboxType::class, $this->mergeOptions('rules', [
                'constraints' => [
                    new NotBlank(),
                ],
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
            'data_class' => Troop::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'troop';
    }
}
