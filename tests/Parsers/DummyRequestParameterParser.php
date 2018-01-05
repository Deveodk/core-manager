<?php

namespace DeveoDK\Tests\Parsers;

use DeveoDK\Core\Manager\Parsers\RequestParameterParser;

class DummyRequestParameterParser
{
    /** @var array */
    protected $fieldAliases = ['dummy' => 'super'];

    /** @var array */
    protected $includesAlias = ['dummy' => 'super'];

    use RequestParameterParser;
}
