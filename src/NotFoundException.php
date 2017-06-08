<?php

namespace Moccalotto\Ditto;

use RuntimeException;

class NotFoundException extends RuntimeException
{
    /**
     * @var string
     */
    protected $path;

    /**
     * Constructor
     *
     * @param string $path
     */
    public function __construct($path)
    {
        $this->path  = $path;

        $message = sprintf(
            'Value with the path "%s" not found.',
            $path
        );

        parent::__construct($message);
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
}
