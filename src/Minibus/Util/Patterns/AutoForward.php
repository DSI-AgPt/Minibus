<?php
namespace Minibus\Util\Patterns;

/**
 * AutoForward baseclass for automatic forwarding of
 * method calls and member variables.
 *
 * @author Peter C. Verhage
 * @author Martin Roest
 */
class AutoForward
{

    var $m_object;

    /**
     * Set the forwarded object.
     */
    public function setObject(&$object)
    {
        $this->m_object = $object;
    }

    /**
     * Returns the forwarded object.
     */
    function &__getObject()
    {
        return $this->m_object;
    }

    /**
     * Forward method calls.
     *
     * @param String $method
     *            method name
     * @param Array $args
     *            method arguments
     * @return Unknown method return value
     */
    function __call($method, $args)
    {
        return call_user_func_array(array(
            $this->m_object,
            $method
        ), $args);
    }

    /**
     * Forward property set.
     *
     * @param String $name
     *            property name
     * @param Unknown $value
     *            property value
     */
    function __set($name, $value)
    {
        $this->m_object->$name = $value;
    }

    /**
     * Forward property get.
     *
     * @param String $name,
     *            property name
     * @return Unknown
     */
    function __get($name)
    {
        return $this->m_object->$name;
    }
}