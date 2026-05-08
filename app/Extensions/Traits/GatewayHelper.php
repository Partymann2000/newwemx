<?php

namespace App\Extensions\Traits;

trait GatewayHelper
{
    public function supportsRefunds(): bool
    {
        // if method exists called refund in the extension, return it
        if (method_exists($this, 'refund')) {
            return true;
        }

        // default to false if method does not exist
        return false;
    }

    public function supportsPartialRefunds(): bool
    {
        if(!$this->supportsRefunds()) {
            return false;
        }

        // check if the refund() takes 'amount' as a parameter
        $reflection = new \ReflectionMethod($this, 'refund');
        $parameters = $reflection->getParameters();
        foreach ($parameters as $parameter) {
            if ($parameter->getName() === 'amount') {
                return true;
            }
        }

        // default to false if method does not exist or does not take 'amount' as a parameter
        return false;
    }
}
