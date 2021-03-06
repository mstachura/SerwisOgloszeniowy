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
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

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
                    'max_length' => 45,
                    'readonly' => (isset($options['data']) && isset($options['data']['id'])),
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new Assert\NotBlank(
                        ['groups' => ['user-default']]
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
                            'groups' => ['user-default'],
                            'min' => 8,
                            'max' => 255,
                        ]
                    ),
                ],
            ]
        );


        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) {
                $form = $event->getForm();
                $data = $event->getData();

                $normData = $form->getNormData();

                if (isset($normData['id'])) {
                    $data['login'] = isset($normData['login']) ? $normData['login'] : '';
                    $event->setData($data);
                }
            }
        );

//        $builder->get('location')->addModelTransformer(
//            new LocationDataTransformer($options['location_repository'])
//        );
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
                'user_repository' => null
            ]
        );
    }

}