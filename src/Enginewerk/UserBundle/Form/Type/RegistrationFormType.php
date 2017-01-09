<?php
namespace Enginewerk\UserBundle\Form\Type;

use FOS\UserBundle\Form\Type\RegistrationFormType as BaseRegistrationFormType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Description of RegistrationFormType
 *
 * @author Paweł Czyżewski <pawel.czyzewski@enginewerk.com>
 */
class RegistrationFormType extends BaseRegistrationFormType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('invitation', 'enginewerk_invitation_type');
    }

    public function getName()
    {
        return 'enginewerk_user_registration';
    }
}
