<?php
namespace Enginewerk\OAuthBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class OAuthBundle extends Bundle
{
    public function getParent()
    {
        return 'HWIOAuthBundle';
    }
}
