<?php

namespace App\Model\WebServices\ResumableJs;

class ChunkFileLocator
{

    /** @var string */
    private $tempDir;


    /**
     * @param string $tempDir
     */
    public function __construct($tempDir)
    {
        $this->tempDir = $tempDir;
    }


    /**
     * @param string $identifier
     * @param int $chunkNumber
     * @return string
     */
    public function getPath($identifier, $chunkNumber)
    {
        return "$this->tempDir/$identifier.part$chunkNumber";
    }
}
