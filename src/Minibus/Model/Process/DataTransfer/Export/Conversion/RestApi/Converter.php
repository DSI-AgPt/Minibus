<?php
namespace Minibus\Model\Process\DataTransfer\Export\Conversion\RestApi;

use Minibus\Model\Process\Conversion\IConverter;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Minibus\Controller\Process\Exception\ProcessException;

abstract class Converter implements IConverter, ServiceLocatorAwareInterface
{
    
    use\Minibus\Util\Traits\ServiceLocatorAwareTrait;

    /**
     * (non-PHPdoc)
     *
     * @see \Minibus\Model\Process\Conversion\IConverter::convert()
     */
    public function convert($arrayOrObject)
    {
        $converted = array();
        $fields = $this->getFields();
        if (! is_array($fields))
            throw new \Exception("La méthode getFields est obligatoire et doit renvoyer l'index des champs à renseigner ");
        foreach ($fields as $field => $params) {
            $conversionMethodName = 'convert' . ucfirst($field);
            $getterName = 'get' . ucfirst($field);
            if (method_exists($this, $conversionMethodName)) {
                $value = call_user_func(array(
                    $this,
                    $conversionMethodName
                ), $arrayOrObject);
                if (! is_null($value))
                    $converted[$field] = $value;
            } else 
                if (method_exists($arrayOrObject, $getterName)) {
                    {
                        $value = call_user_func(array(
                            $arrayOrObject,
                            $getterName
                        ));
                        if (! is_null($value))
                            $converted[$field] = $value;
                    }
                } else 
                    if (is_array($params) && array_key_exists('mandatory', $params) && true === $params['mandatory'])
                        throw new ProcessException("Aucune méthode n'est fournie pour convertir le paramètre obligatoire " . $field);
            try {
                // TODO réécriture récursive
                if (array_key_exists($field, $converted) && ! is_array($converted[$field]))
                    $converted[$field] = strval($converted[$field]);
            } catch (\Exception $e) {
                throw new ProcessException("Impossible de convertir le paramètre " . $field . " de type " . get_class($converted[$field]) . " en chaîne : " . $e->getMessage());
            }
        }
        return $converted;
    }
}