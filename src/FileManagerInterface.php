<?php namespace  Digitaldream\DataConverter;

/**
 *
 * @author Tuhin
 */
interface FileManagerInterface
{

    /**
     * Read data from given file
     */
    public function read();

    /**
     * Write data to specified file
     */
    public function write();

    /**
     * Append data to existing file
     */
    public function append();
}
