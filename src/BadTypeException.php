<?php

namespace Ditto;

use UnexpectedValueException;

class BadTypeException extends UnexpectedValueException
{
    /**
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
    protected $expectedType;

    /**
     * @var string
     */
    protected $value;

    /**
     * Constructor.
     *
     * @param string $path         the path of the variable with the bad type
     * @param string $expectedType the expected type of the variable with the bad type
     * @param string $value        the value that didn't pass the test
     */
    public function __construct($path, $expectedType, $value)
    {
        $this->path = $path;
        $this->expectedType = $expectedType;
        $this->value = $value;

        $message = sprintf(
            'Value with the path "%s" had invalid type. Excpected %s, but got %s',
            $path,
            $expectedType,
            gettype($value)
        );

        parent::__construct($message);
    }

    /**
     * Get the expected type of the variable with the bad type.
     *
     * @return string
     */
    public function getExpectedType()
    {
        return $this->expectedType;
    }

    /**
     * Get the actual type of the variable with the bad type.
     *
     * @return string
     */
    public function getActualType()
    {
        return gettype($this->value);
    }

    /**
     * Get the path of the variable with the bad type.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Get the offending value.
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
}
