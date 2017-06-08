<?php

namespace Moccalotto\Ditto;

/**
 * DTO for function call results.
 */
class Result extends Ditto
{
    /**
     * @var bool
     */
    protected $success;

    /**
     * Constructor.
     *
     * @param bool   $success              Was the call successful or not?
     * @param array  $content              The data to transfer
     */
    public function __construct($success, $content)
    {
        $this->success = (bool) $success;
        parent::__construct($content);
    }

    /**
     * Did the call succeed?
     *
     * @return bool
     */
    public function isSuccess()
    {
        return $this->success;
    }

    /**
     * Did the call fail?
     *
     * @return bool
     */
    public function isFailure()
    {
        return !$this->success;
    }
}
