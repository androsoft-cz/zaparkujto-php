<?php

namespace App\Model\WebServices\ResumableJs;

use App\Model\Exceptions\Runtime\ResumableJs\FileUploadException;
use App\Model\WebServices\ResumableJs\Entity\ChunkFileParameters;
use Nette\Application\Request;
use Nette\Http\FileUpload;
use Nette\Http\Response;

class UploadHandler
{

    /** @var ChunkFileLocator */
    private $chunkFileLocator;

    /** @var FileMerger */
    private $fileMerger;


    public function __construct(ChunkFileLocator $chunkFileLocator, FileMerger $fileMerger)
    {
        $this->chunkFileLocator = $chunkFileLocator;
        $this->fileMerger = $fileMerger;
    }


    /**
     * @param Request $request
     * @param string $destinationFilePath without extension
     * @param string[] $allowedExtensions
     * @return int|null  response code
     * @throws FileUploadException
     */
    public function handle(Request $request, $destinationFilePath, $allowedExtensions = [])
    {
        if ($request->isMethod('GET')) {
            $chunkParams = new ChunkFileParameters($request->getParameters());

            $extension = pathinfo($chunkParams->filename, PATHINFO_EXTENSION);

            if ($allowedExtensions && !in_array($extension, $allowedExtensions)) {
                throw new FileUploadException("File extension '$extension' not supported.");
            }

            $chunkFilePath = $this->chunkFileLocator->getPath($chunkParams->identifier, $chunkParams->number);

            if (file_exists($chunkFilePath)) {
                return Response::S200_OK;
            } else {
                return Response::S404_NOT_FOUND;
            }
        }

        /** @var FileUpload[] $files */
        $files = $request->getFiles();

        if ($files) {
            $chunkFile = $files[key($files)];

            if ($chunkFile->isOk()) {
                foreach ($allowedExtensions as $extension) {
                    $filePath = "$destinationFilePath.$extension";

                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                }

                $chunkParams = new ChunkFileParameters($request->getPost());
                $chunkFilePath = $this->chunkFileLocator->getPath($chunkParams->identifier, $chunkParams->number);

                move_uploaded_file($chunkFile->temporaryFile, $chunkFilePath);

                if ($chunkParams->number == $chunkParams->totalCount) {
                    $extension = pathinfo($chunkParams->filename, PATHINFO_EXTENSION);

                    $this->fileMerger->merge("$destinationFilePath.$extension", $chunkParams);
                }
            }
        }

        return NULL;
    }

}
