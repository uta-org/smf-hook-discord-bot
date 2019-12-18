<?php

function tryLock()
{
    # If lock file exists, check if stale.  If exists and is not stale, return TRUE
    # Else, create lock file and return FALSE.

    if (@symlink("/proc/" . getmypid(), LOCK_FILE) !== FALSE) # the @ in front of 'symlink' is to suppress the NOTICE you get if the LOCK_FILE exists
        return true;

    # link already exists
    # check if it's stale
    if (is_link(LOCK_FILE) && !is_dir(LOCK_FILE))
    {
        unlink(LOCK_FILE);
        # try to lock again
        return tryLock();
    }

    return false;
}