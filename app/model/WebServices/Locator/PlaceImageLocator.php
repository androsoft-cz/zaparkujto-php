<?php

namespace App\Model\WebServices\Locator;

class PlaceImageLocator
{

    /** @var string */
    private $wwwDirUrl;

    /** @var string */
    private $wwwDirPath;

    /** @var string[] */
    private $allowedExtensions = ['jpg', 'png', 'gif'];


    /**
     * @param string $wwwDirUrl
     * @param string $wwwDirPath
     */
    public function __construct($wwwDirUrl, $wwwDirPath)
    {
        $this->wwwDirUrl = $wwwDirUrl;
        $this->wwwDirPath = $wwwDirPath;
    }


    /**
     * @param int $id
     * @param string|null $extension
     * @return string
     */
    public function getPath($id, $extension = NULL)
    {
        $path = "$this->wwwDirPath/$id";

        if ($extension !== NULL) {
            $path .= ".$extension";
        }

        return $path;
    }


    /**
     * @param int $id
     * @return string|null
     */
    public function getUrl($id)
    {
        foreach ($this->allowedExtensions as $extension) {
            $path = $this->getPath($id, $extension);

            if (file_exists($path)) {
                return "$this->wwwDirUrl/$id.$extension";
            }
        }

        return NULL;
    }


    /**
     * @return string[]
     */
    public function getAllowedExtensions()
    {
        return $this->allowedExtensions;
    }

}
