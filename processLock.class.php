<?php

class ProcessLock
{
    const LOCK_MARKER = "/#LOCK#";

    private $lockPid;

    private $lockName;

    private $lockPath;

    private $isLockOwner;

    private $lockFilename;

    private $lockFilePointer;


    public function getLockPid()
    {
        return $this->lockPid;

    } // getLockPid
    

    public function setLockPid( $pid )
    {
        $this->lockPid = $pid;

        return $this;

    } // setLockPid
    

    public function getLockName()
    {
        return $this->lockName;

    } // getLockName
    

    public function setLockName( $name )
    {
        $this->lockName = $name;

        return $this;

    } // setLockName
    

    public function getLockPath()
    {
        return $this->lockPath;

    } // getLockPath


    public function setLockPath( $path )
    {
        $this->lockPath = $path;

        return $this;

    } // setLockPath
    

    public function getLockFile()
    {
        return $this->lockFilename;

    } // getLockFile


    public function setLockFile( $filename )
    {
        $this->lockFilename = $filename;

        return $this;

    } // setLockFile
    
    
    public function isLockOwner()
    {
        return $this->isLockOwner;
    
    } // isLockOwner
    
    
    private function setIsLockOwner( $bool )
    {
        $this->isLockOwner = $bool;
    
    } // setIsLockOwner


    public function createFilename()
    {
        $this->setLockFile( self::LOCK_MARKER . "{$this->getLockName()}.lock" );

        return $this;

    } // createFilename


    public function lock()
    {
        if( $this->isFileLocked() )
        {
            throw new ProcessLockException( "Process lock already exists for {$this->getLockName()}" );
        }
        else
        {
            $this->setIsLockOwner( TRUE );
        }

        $this->setLockFilePointer( fopen( $this->getLockPath() . $this->getLockFile(), "x+" ) );

        if( flock( $this->getLockFilePointer(), LOCK_EX ) )
        {
            $epochTime = time();
            $lockFileContents = "{$this->getLockPid()}:{$epochTime}\n";
            chmod( $this->getLockPath() . $this->getLockFile(), 0600 );
            ftruncate( $this->getLockFilePointer(), 0 );
            fwrite( $this->getLockFilePointer(), $lockFileContents );
            fflush( $this->getLockFilePointer() );
        }
        else
        {
            throw new ProcessLockException( "Unable to obtain lock for {$this->getLockFile()}" );
        }

    } // lock


    public function unlock()
    {
        if( $this->isLockOwner() )
        {
            flock( $this->getLockFilePointer(), LOCK_UN );
            fclose( $this->getLockFilePointer() );
            unlink( $this->getLockPath() . $this->getLockFile() );
            $this->setIsLockOwner( FALSE );
        }

    } // unlock


    private function isFileLocked()
    {
        $fileToCheck = $this->getLockPath() . $this->getLockFile();

        if( ! file_exists( $fileToCheck ) )
        {
            return FALSE;
        }
        else if( is_readable( $fileToCheck ) )
        {
            return ( $this->isLockOwner() ) ? FALSE : TRUE;
        }
        else
        {
            return TRUE;
        }

    } // isFileLocked
    
    
    private function getLockFilePointer()
    {
        return $this->lockFilePointer;
    
    } // getLockFilePointer
    
    
    private function setLockFilePointer( $filePointer )
    {
        $this->lockFilePointer = $filePointer;
    
        return $this;
    
    } // setLockFilePointer


} // ProcessLock


class ProcessLockException extends Exception
{

    public function __construct( $message, $code = 0, Exception $previous = NULL )
    {
        parent::__construct( $message, $code, $previous );

    } // __construct


    public function __toString()
    {
        $time = date( 'Y-m-d H:i:s', time() );
        return __CLASS__ . ": [{$time}]: [{$this->code}]: {$this->message}\n";

    } // __toString


} // ProcessLockException
