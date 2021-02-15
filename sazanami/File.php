<?php
namespace sazanami;
class File
{
    private $fp = null;

    public function __construct($filePath, $mode)
    {
        $this->fp = fopen( $filePath, $mode );
    }

    public function write($text)
    {
        fwrite( $this->fp, $text );
    }

    public function writeLine($text)
    {
        fputs( $this->fp, $text );
    }

    public function readLine()
    {
        return fgets( $this->fp );
    }

    public function __destruct()
    {        
        if( $this->fp != null )
        {
            fclose( $this->fp );
        }
    }
}
?>