<?php

namespace Enginewerk\EmissionBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Description of ResumableFileBlockType
 *
 * @author Paweł Czyżewski <pawel.czyzewski@enginewerk.com>
 */
class ResumableFileBlockType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('rangeStart', 'text');
        $builder->add('rangeEnd', 'text');
        $builder->add('size', 'text');
        $builder->add('uploadedFile', 'file', array('mapped' => false));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Enginewerk\EmissionBundle\Entity\FileBlock'
        ));
    }

    public function getName()
    {
        return 'fileBlock';
    }
}
