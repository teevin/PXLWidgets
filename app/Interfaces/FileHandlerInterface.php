<?php

namespace App\Interfaces;

interface FileHandlerInterface
{
    /**
     * Process Different file types
     * @return array
     */
   public function process(): array;

}
