<?php

namespace Enginewerk\EmissionBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Description of ResumableFileType.
 *
 * @author Paweł Czyżewski <pawel.czyzewski@enginewerk.com>
 */
class ResumableFileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('resumableFilename', 'text', array(
                'property_path' => 'name'
            ));
        $builder->add('resumableTotalSize', 'text', array(
                'property_path' => 'size'
            ));
        $builder->add('resumableIdentifier', 'text', array(
                'property_path' => 'checksum'
            ));

        $builder->add('uploadedFile', 'file', array('mapped' => false));
        $builder->add('resumableChunkNumber', 'text', array('mapped' => false));
        $builder->add('resumableChunkSize', 'text', array('mapped' => false));
        $builder->add('resumableCurrentChunkSize', 'text', array('mapped' => false));
        $builder->add('resumableCurrentStartByte', 'text', array('mapped' => false));
        $builder->add('resumableCurrentEndByte', 'text', array('mapped' => false));
        $builder->add('resumableType', 'text', array('mapped' => false));
        $builder->add('resumableRelativePath', 'text', array('mapped' => false));
        $builder->add('resumableTotalChunks', 'text', array('mapped' => false));
        $builder->add('_tokenFileBlock', 'text', array('mapped' => false));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Enginewerk\EmissionBundle\Entity\File',
            'csrf_field_name' => '_tokenFile'
        ));
    }

    public function getName()
    {
        // name dictated by resumable.js
        return 'form';
    }
}
