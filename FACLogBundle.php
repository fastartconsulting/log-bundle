<?php

namespace FAC\LogBundle;

use FAC\LogBundle\DependencyInjection\FACLogExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class FACLogBundle extends Bundle {

    public function getContainerExtension()
    {
        if ($this->extension === null) {
            $this->extension = new FACLogExtension();
        }

        return $this->extension;
    }
}
