<?php

namespace app;


class BaseObject
{
    public function __toString()
    {
        return get_class($this);
    }
}