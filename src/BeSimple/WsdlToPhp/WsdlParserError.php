<?php

/*
 * This file is part of BeSimpleWsdlToPhp.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 * (c) Francis Besset <francis.besset@gmail.com>
 * (c) Andreas Schamberger <mail@andreass.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BeSimple\WsdlToPhp;

/**
 * WSDL parser error struct.
 *
 * @author Andreas Schamberger <mail@andreass.net>
 */
class WsdlParserError
{
    /**
     * Constructor.
     *
     * @param string $message Error message
     * @param int    $file    WSDL file name
     * @param int    $line    XML file line number
     */
    public function __construct($message, $file, $line)
    {
        $this->message = $message;
        $this->file    = $file;
        $this->line    = $line;
    }

    /**
     * Render WSDL parser error to string.
     *
     * @return string
     */
    public function toString()
    {
        return "Error: {$this->message}, File: {$this->file}" . ($this->line?' Line: '.$this->line:'');
    }
}
