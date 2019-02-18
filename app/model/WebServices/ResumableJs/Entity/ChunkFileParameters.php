<?php

namespace App\Model\WebServices\ResumableJs\Entity;

class ChunkFileParameters
{

    /** @var string */
    public $identifier;

    /** @var string */
    public $filename;

    /** @var int */
    public $number;

    /** @var int */
    public $totalCount;

    /** @var int */
    public $totalSize;


    public function __construct(array $params)
    {
        $this->identifier = isset($params['resumableIdentifier']) ? $params['resumableIdentifier'] : '';
        $this->filename = isset($params['resumableFilename']) ? $params['resumableFilename'] : '';
        $this->number = isset($params['resumableChunkNumber']) ? (int) $params['resumableChunkNumber'] : 0;
        $this->totalCount = isset($params['resumableTotalChunks']) ? (int) $params['resumableTotalChunks'] : 0;
        $this->totalSize = isset($params['resumableTotalSize']) ? (int) $params['resumableTotalSize'] : 0;
    }
}
