<?php
namespace Minibus\Controller\Exceptions;

/**
 *
 * @author Joachim Dornbusch
 * @copyright Joachim Dornbusch -AgroParisTech 2014-2015
 *            @30 juin 2015
 */
class RestApiException extends \Exception
{

    /**
     *
     * @var array
     */
    private $errorData;

    /**
     *
     * @var \Exception
     */
    private $original;

    /**
     *
     * @param array $data            
     * @param \Exception $original            
     */
    public function __construct(array $data, \Exception $original = null)
    {
        $this->errorData = $data;
        $this->original = $original;
    }

    /**
     *
     * @return array:
     */
    public function getErrorData()
    {
        return $this->errorData;
    }

    /**
     *
     * @return Exception
     */
    public function getOriginal()
    {
        return $this->original;
    }
}