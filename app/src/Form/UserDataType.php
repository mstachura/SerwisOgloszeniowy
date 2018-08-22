<?php
/**
 * User data type.
 */
namespace Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * Class UserDataType.
 *
 * @package Form
 */
class UserDataType extends AbstractType
{
    /**
     * Build Form
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'firstname',
            TextType::class,
            [
                'label' => 'label.firstname',
                'required'   => true,
                'attr' => [
                    'max_legth' => 32,
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new Assert\NotBlank(
                        ['groups' => ['ads-default']]
                    ),
                ],
            ]
        );

        $builder->add(
            'lastname',
            TextType::class,
            [
                'label' => 'label.lastname',
                'required'   => true,
                'attr' => [
                    'max_legth' => 32,
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new Assert\NotBlank(
                        ['groups' => ['ads-default']]
                    ),
                ],
            ]
        );

        $builder->add(
            'phone_number',
            NumberType::class,
            [
                'label' => 'label.phone_number',
                'required'   => true,
                'attr' => [
                    'max_legth' => 10,
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new Assert\NotBlank(
                        ['groups' => ['ads-default']]
                    ),
                ],
            ]
        );
    }


    /**
     * Configure Options
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'validation_groups' => ['user-default']
            ]
        );
    }
}
