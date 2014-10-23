<?php

namespace Enginewerk\EmissionBundle\Twig;

class EmissionExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('md5', array($this, 'md5')),
        );
    }

    public function md5($number)
    {
        return md5($number);
    }

    public function getName()
    {
        return 'emission_extension';
    }
}
