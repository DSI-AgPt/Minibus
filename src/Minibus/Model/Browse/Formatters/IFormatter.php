<?php
namespace Minibus\Model\Browse\Formatters;

/**
 *
 * @author Joachim Dornbusch
 * @copyright Joachim Dornbusch -AgroParisTech 2014-2015
 *            1 juil. 2015
 */
interface IFormatter
{

    /**
     *
     * @param array $columns            
     * @param int $start            
     * @param int $length            
     * @param string $order            
     * @param string $search        
     * @param int $year            
     */
    public function getData(array $columns, $start, $length, array $order, array $search, $year);
}
