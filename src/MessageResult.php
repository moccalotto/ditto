<?php

namespace Moccalotto\Ditto;

/**
 * DTO for function call results with status codes and status messages.
 */
class MessageResult extends Result
{
    /**
     * @var int
     */
    protected $code;

    /**
     * @var string
     */
    protected $message;

    /**
     * Constructor.
     *
     * @param bool   $success              Was the call successful or not?
     * @param int    $code                 the "status code" of the call
     * @param string $message              the status message of the call
     * @param array  $content              The data to transfer
     */
    public function __construct($success, $code, $message, $content)
    {
        $this->code = (int) $code;
        $this->message = (string) $message;
        parent::__construct($success, $content);
    }

    /**
     * Get the status message.
     *
     * @return string
     */
    public function message()
    {
        return $this->message;
    }

    /**
     * Get the status code.
     *
     * @return int
     */
    public function code()
    {
        return $this->code;
    }
}
