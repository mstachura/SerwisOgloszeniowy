<?php
/**
 * User type.
 */
namespace Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Validator\Constraints as CustomAssert;

/**
 * Class UserType.
 *
 * @package Form
 */
class UserType extends AbstractType
{
    /**
     * Build Form
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add(
            'login',
            TextType::class,
            [
                'label' => 'label.login',
                'required'   => true,
                'attr' => [
                    'readonly' => (isset($options['data']) && isset($options['data']['id'])),
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new Assert\NotBlank(
                        ['groups' => ['user-default']]
                    ),

                    new Assert\Length(
                        [
                            'groups' => ['user-default'],
                            'min' => 8,
                            'max' => 32,
                        ]
                    ),

                    new CustomAssert\UniqueLogin(
                        ['groups' => ['user-default'],
                            'repository' => isset($options['user_repository']) ? $options['user_repository'] : null,
                            'elementId' => isset($options['data']['id']) ? $options['data']['id'] : null,
                        ]
                    ),
                ],
            ]
        );

        $builder->add(
            'password',
            RepeatedType::class,
            [
                'type' => PasswordType::class,
                'invalid_message' => 'message.password_not_repeated',
                'options' => array('attr' => array('class' => 'password-field')),
                'required' => true,
                'first_options' => array('label' => 'label.password'),
                'second_options' => array('label' => 'label.repeat.password'),
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(
                        [
                            ['groups' => ['user-default']],
                            'min' => 8,
                            'max' => 255,
                        ]
                    ),
                ],
            ]
        );

        $builder->add(
            'email',
            EmailType::class,
            [
                'label' => 'label.email',
                'required'   => true,
                'attr' => [
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new Assert\NotBlank(
                        ['groups' => ['user-default']]
                    ),

                    new Assert\Length(
                        [
                            'groups' => ['user-default'],
                            'min' => 10,
                            'max' => 125,
                        ]
                    ),
                ],
            ]
        );

        $builder->add(
            'firstname',
            TextType::class,
            [
                'label' => 'label.firstname',
                'required'   => true,
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(
                        [
                            ['groups' => ['user-default']],
                            'min' => 3,
                            'max' => 32,
                            'class' => 'form-control',
                        ]
                    ),
                ]
            ]
        );

        $builder->add(
            'lastname',
            TextType::class,
            [
                'label' => 'label.lastname',
                'required'   => true,
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(
                        [
                            ['groups' => ['user-default']],
                            'min' => 3,
                            'max' => 32,
                            'class' => 'form-control',
                        ]
                    ),
                ]
            ]
        );

        $builder->add(
            'location_name',
            TextType::class,
            [
                'required' => true,
                'label' => 'label.location_name',
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(
                        [
                            ['groups' => ['user-default']],
                            'min' => 3,
                            'max' => 128,
                        ]
                    ),
                ]
            ]
        );

        $builder->add(
            'phone_number',
            NumberType::class,
            [
                'label' => 'label.phone_number',
                'required'   => true,
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(
                        [
                            ['groups' => ['user-default']],
                            'min' => 7,
                            'max' => 10,
                        ]
                    ),
                ]
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
                'validation_groups' => ['user-default'],
                'user_repository' => null,
            ]
        );
    }
}
