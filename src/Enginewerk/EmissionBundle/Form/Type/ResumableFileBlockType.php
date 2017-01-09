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
        $builder->add('resumableCurrentStartByte', 'text', [
                'property_path' => 'rangeStart',
            ]);
        $builder->add('resumableCurrentEndByte', 'text', [
                'property_path' => 'rangeEnd',
            ]);
        $builder->add('resumableCurrentChunkSize', 'text', [
                'property_path' => 'size',
            ]);

        $builder->add('resumableFilename', 'text', ['mapped' => false]);
        $builder->add('resumableTotalSize', 'text', ['mapped' => false]);
        $builder->add('resumableIdentifier', 'text', ['mapped' => false]);
        $builder->add('uploadedFile', 'file', ['mapped' => false]);
        $builder->add('resumableChunkNumber', 'text', ['mapped' => false]);
        $builder->add('resumableChunkSize', 'text', ['mapped' => false]);
        $builder->add('resumableType', 'text', ['mapped' => false]);
        $builder->add('resumableRelativePath', 'text', ['mapped' => false]);
        $builder->add('resumableTotalChunks', 'text', ['mapped' => false]);
        $builder->add('_tokenFile', 'text', ['mapped' => false]);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Enginewerk\EmissionBundle\Entity\FileBlock',
            'csrf_field_name' => '_tokenFileBlock',
        ]);
    }

    public function getName()
    {
        // name dictated by resumable.js
        return 'form';
    }
}
