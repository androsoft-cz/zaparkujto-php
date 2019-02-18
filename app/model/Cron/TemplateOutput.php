<?php

namespace App\Model\Cron;

use Symfony\Component\Console\Output\Output;

final class TemplateOutput extends Output
{

    /**
     * @param string $message
     * @param bool $newline
     */
    protected function doWrite($message, $newline)
    {
        echo nl2br($message);
    }

}
