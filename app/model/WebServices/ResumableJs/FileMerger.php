<?php

namespace App\Model\WebServices\ResumableJs;

use App\Model\Exceptions\Runtime\ResumableJs\FileUploadException;
use App\Model\WebServices\ResumableJs\Entity\ChunkFileParameters;

class FileMerger
{

    /** @var ChunkFileLocator */
    private $chunkFileLocator;


    public function __construct(ChunkFileLocator $chunkFileLocator)
    {
        $this->chunkFileLocator = $chunkFileLocator;
    }


    /**
     * @param string $destinationFilePath
     * @param ChunkFileParameters $parameters
     * @throws FileUploadException
     */
    public function merge($destinationFilePath, ChunkFileParameters $parameters)
    {
        if (!$destinationFile = fopen($destinationFilePath, 'w')) {
            throw new FileUploadException("Opening file '$destinationFilePath' for writing failed.");
        }

        for ($i = 1; $i <= $parameters->totalCount; $i++) {
            $chunkFilePath = $this->chunkFileLocator->getPath($parameters->identifier, $i);

            if (!file_exists($chunkFilePath)) {
                throw new FileUploadException("File '$chunkFilePath' not found.");
            }

            fwrite($destinationFile, file_get_contents($chunkFilePath));
            unlink($chunkFilePath);
        }

        fclose($destinationFile);

        if (filesize($destinationFilePath) != $parameters->totalSize) {
            throw new FileUploadException("Size of '$destinationFile' differs from '$parameters->totalSize'.");
        }
    }
}
