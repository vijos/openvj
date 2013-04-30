<?php

//
// This file contains the View class which extends Blitz
//

class View extends Blitz
{
    function __construct($path)
    {
        parent::__construct($path);
        parent::setGlobals(array('footer_revision' => SYS_REVISION));
    }

    function execution_time()
    {
        return round((microtime(true) - ENV_REQUEST_TIME) * 1000, 5);
    }
}
