<?php

namespace DeveoDK\Core\Manager\Exceptions;

use Exception;

class FormatterDoNotImplementInterface extends Exception
{
    /** @var string  */
    protected $message = 'The formatter should implement FormatterInterface';

    /**
     * FormatterDoNotImplementInterface constructor.
     */
    public function __construct()
    {
        parent::__construct($this->message);
    }
}
