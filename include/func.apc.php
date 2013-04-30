<?php

//
// This file contains the helper functions of the APC extension
//

namespace apc;

// Returns TRUE if the file is unmodified since last test
//@deprecated
function unmodified($path)
{
    if (!($path = realpath($path)))
        trigger_error("File not found", E_USER_ERROR);

    $mtime = filemtime($path);
    if (apc_fetch('timestamp.'.$path) === $mtime)
        return true;

    apc_store('timestamp.'.$path, $mtime);

    return false;
}
