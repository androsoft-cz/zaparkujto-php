<?php

namespace App\Model\WebServices\Locator;

class PlaceTypeIconLocator
{
    /** @var string */
    private $wwwDirUrl;


    /**
     * @param string $wwwDirUrl
     */
    public function __construct($wwwDirUrl)
    {
        $this->wwwDirUrl = $wwwDirUrl;
    }


    /**
     * @param int $type
     * @return string
     */
    public function getUrl($type)
    {
        return "$this->wwwDirUrl/$type.png";
    }

}
