<?php
/**
 * Registration Type.
 */

namespace App\Form\Type;

use App\Entity\User;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class RegistrationType.
 */
class RegistrationType extends AbstractType
{
    /**
     * Build form.
     *
     * @param FormBuilderInterface $builder Form builder interface
     * @param array                $options Form options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'email',
            EmailType::class,
            [
                'label' => 'label.email',
                'required' => true,
                'attr' => ['max_length' => 64],
                'constraints' => [
                    new NotBlank(),
                    new Email(),
                ],
            ]
        );

        $builder->add(
            'password',
            PasswordType::class,
            [
                'label' => 'label.password',
                'required' => true,
                'attr' => [
                    'max_length' => 255,
                    'min_length' => 8,
                ],
                'constraints' => [
                    new NotBlank(),
                ],
            ]
        );

        $builder->add(
            'firstName',
            TextType::class,
            [
                'label' => 'label.firstName',
                'required' => true,
                'attr' => ['max_length' => 64],
                'constraints' => [
                    new NotBlank(),
                ],
            ]
        );

        $builder->add(
            'lastName',
            TextType::class,
            [
                'label' => 'label.lastName',
                'required' => true,
                'attr' => ['max_length' => 64],
                'constraints' => [
                    new NotBlank(),
                ],
            ]
        );
    }

    /**
     * @param OptionsResolver $resolver resolver
     *
     * @return void return void
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => User::class,
                'constraints' => [
                    new UniqueEntity(
                        [
                            'entityClass' => User::class,
                            'fields' => ['email'],
                        ]
                    ),
                ],
            ]
        );
    }
}
