<?php
namespace Enginewerk\EmissionBundle\Twig;

class EmissionExtension extends \Twig_Extension
{
    /**
     * @return array
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('md5', [$this, 'md5']),
        ];
    }

    /**
     * @param int $number
     *
     * @return string
     */
    public function md5($number)
    {
        return md5($number);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'emission_extension';
    }
}
