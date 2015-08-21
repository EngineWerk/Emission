<?php

namespace Enginewerk\EmissionBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Description of ResumableFileBlockType.
 *
 * @author Paweł Czyżewski <pawel.czyzewski@enginewerk.com>
 */
class ResumableFileBlockType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('resumableCurrentStartByte', 'text', array(
                'property_path' => 'rangeStart'
            ));
        $builder->add('resumableCurrentEndByte', 'text', array(
                'property_path' => 'rangeEnd'
            ));
        $builder->add('resumableCurrentChunkSize', 'text', array(
                'property_path' => 'size'
            ));

        $builder->add('resumableFilename', 'text', array('mapped' => false));
        $builder->add('resumableTotalSize', 'text', array('mapped' => false));
        $builder->add('resumableIdentifier', 'text', array('mapped' => false));
        $builder->add('uploadedFile', 'file', array('mapped' => false));
        $builder->add('resumableChunkNumber', 'text', array('mapped' => false));
        $builder->add('resumableChunkSize', 'text', array('mapped' => false));
        $builder->add('resumableType', 'text', array('mapped' => false));
        $builder->add('resumableRelativePath', 'text', array('mapped' => false));
        $builder->add('resumableTotalChunks', 'text', array('mapped' => false));
        $builder->add('_tokenFile', 'text', array('mapped' => false));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Enginewerk\EmissionBundle\Entity\FileBlock',
            'csrf_field_name' => '_tokenFileBlock'
        ));
    }

    public function getName()
    {
        // name dictated by resumable.js
        return 'form';
    }
}
