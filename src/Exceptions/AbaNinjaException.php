<?php

namespace Stui\AbaNinja\Exceptions;

class AbaNinjaException extends \Exception
{
    private $data = null;

    public function getData()
    {
        return $this->data;
    }

    public function setData($data)
    {
        $this->data = $data;
    }
}