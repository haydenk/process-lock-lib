<?php

try {

    require_once "processLock.class.php";

    $process = new ProcessLock();

    $process->setLockPath( dirname( __FILE__ ) )
            ->setLockPid( getmypid() )
            ->setLockName( 'test' )
            ->createFilename();

    $process->lock();

    // Your processing scripts go here

    $process->unlock();

}
catch( ProcessLockException $e )
{
    echo $e->__toString();
    die;
}

