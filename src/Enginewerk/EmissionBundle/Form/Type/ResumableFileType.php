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
        $builder->add('resumableFilename', 'text', [
                'property_path' => 'name',
            ]);
        $builder->add('resumableTotalSize', 'text', [
                'property_path' => 'size',
            ]);
        $builder->add('resumableIdentifier', 'text', [
                'property_path' => 'checksum',
            ]);

        $builder->add('uploadedFile', 'file', ['mapped' => false]);
        $builder->add('resumableChunkNumber', 'text', ['mapped' => false]);
        $builder->add('resumableChunkSize', 'text', ['mapped' => false]);
        $builder->add('resumableCurrentChunkSize', 'text', ['mapped' => false]);
        $builder->add('resumableCurrentStartByte', 'text', ['mapped' => false]);
        $builder->add('resumableCurrentEndByte', 'text', ['mapped' => false]);
        $builder->add('resumableType', 'text', ['mapped' => false]);
        $builder->add('resumableRelativePath', 'text', ['mapped' => false]);
        $builder->add('resumableTotalChunks', 'text', ['mapped' => false]);
        $builder->add('_tokenFileBlock', 'text', ['mapped' => false]);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Enginewerk\EmissionBundle\Entity\File',
            'csrf_field_name' => '_tokenFile',
        ]);
    }

    public function getName()
    {
        // name dictated by resumable.js
        return 'form';
    }
}
