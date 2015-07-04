<?php
namespace Minibus\Model\Process\Service\Hash;

use Zend\ServiceManager\ServiceLocatorAwareInterface;

class HashCalculator implements ServiceLocatorAwareInterface
{
    
    use\Minibus\Util\Traits\ServiceLocatorAwareTrait;

    public function getHash(array $data)
    {
        return md5(serialize($data));
    }
}