<?php

namespace Enginewerk\EmissionBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Description of ResumableFileType
 *
 * @author Paweł Czyżewski <pawel.czyzewski@enginewerk.com>
 */
class ResumableFileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text');
        $builder->add('size', 'text');
        $builder->add('checksum', 'text');
        $builder->add('uploadedFile', 'file', array('mapped' => false)); 
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Enginewerk\EmissionBundle\Entity\File'
        ));
    }

    public function getName()
    {
        return 'file';
    }
}
