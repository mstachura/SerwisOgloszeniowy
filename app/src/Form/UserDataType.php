<?php
/**
 * User Data type.
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
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

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
            'email',
            EmailType::class,
            [
                'label' => 'label.email',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new Assert\NotBlank(
                        [
                            'groups' => ['user_data-default']
                        ]
                    ),
                    new Assert\Length(
                        [
                            'groups' => ['user_data-default'],
                            'min' => 3,
                            'max' => 45,
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
                'required' => true,
                'attr' => [
                    'max_length' => 32,
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new Assert\NotBlank(
                        [
                            'groups' => ['user_data-default']
                        ]
                    ),
                    new Assert\Length(
                        [
                            'groups' => ['user_data-default'],
                            'min' => 3,
                            'max' => 32,
                        ]
                    ),
                ],
            ]
        );

        $builder->add(
            'lastname',
            TextType::class,
            [
                'label' => 'label.lastname',
                'required' => true,
                'attr' => [
                    'max_length' => 32,
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new Assert\NotBlank(
                        ['groups' => ['user_data-default']]
                    ),
                    new Assert\Length(
                        [
                            'groups' => ['user_data-default'],
                            'min' => 3,
                            'max' => 32,
                        ]
                    ),
                ],
            ]
        );

        $builder->add(
            'location_name',
            TextType::class,
            [
                'required' => true,
                'label' => 'label.location',
                'attr' => [
                    'min_length' => 3,
                    'max_length' => 128,
                ],

                'constraints' => [
                    new Assert\NotBlank(
                        ['groups' => ['user_data-default']]
                    ),
                    new Assert\Length(
                        [
                            'groups' => ['user_data-default'],
                            'min' => 3,
                            'max' => 128,
                        ]
                    ),
                ],
            ]
        );

        $builder->add(
            'phone_number',
            NumberType::class,
            [
                'label' => 'label.phone_number',
                'required' => true,
                'attr' => [
//                    'max_length' => 10,
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new Assert\NotBlank(
                        ['groups' => ['user_data-default']]
                    ),
                    new Assert\Length(
                        [
                            'groups' => ['user_data-default'],
                            'min' => 7,
                            'max' => 10,
                        ]
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
                'validation_groups' => ['user_data-default'],
                'location_repository' => null
            ]
        );
    }
    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'user_data_type';
    }
//    /**
//     * Prepare Locations For Choices
//     * @param $locationRepository
//     * @return array
//     */
//    protected function prepareLocationsForChoices($locationRepository)
//    {
//        $locations = $locationRepository->findAll();
//        $choices = [];
//        foreach ($locations as $location) {
//            $choices[$location['name']] = $location['id'];
//        }
//        return $choices;
//    }


}