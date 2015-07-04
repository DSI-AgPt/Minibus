<?php
namespace Minibus\Model\Process\Conversion;

/**
 *
 * @author Joachim Dornbusch 1 juil. 2015
 * @copyright Joachim Dornbusch - AgroParisTech - 2014,2015
 *           
 */
interface IConverter
{

    /**
     *
     * @param \stdClass|array $arrayOrObject            
     * @return \stdClass|array $arrayOrObject
     */
    public function convert($arrayOrObject);

    /**
     *
     * @return array
     */
    public function getFields();
}
