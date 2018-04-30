<?php

namespace Shopsys\FrameworkBundle\Component\DataFixture\Exception;

use Exception;

class NonExistingClassException extends Exception implements DataFixtureException
{
    /**
     * @param string $className
     * @param \Exception|null $previous
     */
    public function __construct($className, Exception $previous = null)
    {
        $message = 'Trying to load fixtures from non-existing class ' . $className;

        parent::__construct($message, 0, $previous);
    }
}
