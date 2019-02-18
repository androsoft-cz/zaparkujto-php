<?php

namespace App\Core\Utils;

use Nette\Utils\Image;

class Thumbnails
{

    /**
     * Vytvoreni miniatury obrazku a vraceni jeho URI
     *
     * @param  string relativni URI originalu (zacina se v document_rootu)
     * @param  NULL|int sirka miniatury
     * @param  NULL|int vyska miniatury
     * @return string absolutni URI miniatury
     */
    public function thumb($origName, $width, $height = NULL)
    {
        $thumbDirPath = dirname($origName);

        if (($width === NULL && $height === NULL) || !is_file($origName) || !is_dir($thumbDirPath) || !is_writable($thumbDirPath))
            return $origName;

        $thumbName = $this->getThumbName($origName, $width, $height);

        // miniatura jiz existuje
        if (is_file($thumbName)) {
            return $thumbName;
        }

        try {

            $image = Image::fromFile($origName);

            // zachovani pruhlednosti u PNG
            $image->alphaBlending(FALSE);
            $image->saveAlpha(TRUE);

            $origWidth = $image->getWidth();
            $origHeight = $image->getHeight();

            $image->resize($width, $height,
                $width !== NULL && $height !== NULL ? Image::STRETCH : Image::FIT)
                ->sharpen();

            $newWidth = $image->getWidth();
            $newHeight = $image->getHeight();

            // doslo ke zmenseni -> ulozime miniaturu
            if ($newWidth !== $origWidth || $newHeight !== $origHeight) {

                $image->save($thumbName);

                if (is_file($thumbName))
                    return $thumbName;
                else return $origName;

            } else {
                return $origName;
            }
        } catch (Exception $e) {
            return $origName;
        }
    }


    /**
     * Vytvori jmeno generovane miniatury
     *
     * @param  string relativni cesta (document_root/$relPath)
     * @param  int sirka
     * @param  int vyska
     * @param  int timestamp zmeny originalu
     * @param  bool zda se bue generovat md5 hash
     * @return string
     */
    public function getThumbName($relPath, $width, $height, $mtime = 0, $md5 = FALSE)
    {
        $sep = '.';
        $tmp = explode($sep, $relPath);
        $ext = array_pop($tmp);

        // cesta k obrazku (ale bez pripony)
        $relPath = implode($sep, $tmp);

        // pripojime rozmery a mtime
        if ($mtime == 0) {
            $relPath .= '-' . $width . 'x' . $height;
        } else {
            $relPath .= '-' . $width . 'x' . $height . '-' . $mtime;
        }

        // zahashujeme a vratime priponu
        if ($md5) {
            $relPath = md5($relPath) . $sep . $ext;
        } else {
            $relPath = $relPath . $sep . $ext;
        }

        return $relPath;
    }
}
