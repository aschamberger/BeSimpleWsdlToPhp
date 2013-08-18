<?php

namespace BeSimple\WsdlToPhp;

class WsdlException extends Exception
{
    /**
     * @param string $message
     * @param int $wsdlFile
     * @param int $line
     */
    public function __construct($parent, $message, $wsdlFile, $line)
    {
        $this->previous = $parent;
        $this->message = $message;
        $this->file = $wsdlFile;
        $this->line = $line;
    }

    public function toString()
    {
        return "Error: {$this->message}, File: {$this->file}" . ($this->line?' Line: '.$this->line:'');
    }
}
