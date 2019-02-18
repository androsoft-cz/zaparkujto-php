<?php

use Tracy\Debugger;

if (!function_exists('d')) {

    /**
     * Shortcut for Tracy\Debugger::dump
     *
     * @param mixed
     */
    function d($var) // @codingStandardsIgnoreLine
    {
        array_map('Tracy\Debugger::dump', func_get_args());
    }

}

if (!function_exists('de')) {

    /**
     * Shortcut for Tracy\Debugger::dump & exit
     *
     * @param mixed
     */
    function de($var) // @codingStandardsIgnoreLine
    {
        array_map('Tracy\Debugger::dump', func_get_args());
        exit;
    }

}


if (!function_exists('bd')) {

    /**
     * Shortcut for Tracy\Debugger::barDump
     *
     * @param mixed
     * @param mixed
     */
    function bd($var, $title = NULL)
    {
        $backtrace = debug_backtrace();
        $source = (isset($backtrace[1]['class']) ? $backtrace[1]['class'] : basename($backtrace[0]['file']));
        $line = $backtrace[0]['line'];
        $title .= (empty($title) ? '' : ' – ');

        Debugger::barDump($var, $title . $source . ' (' . $line . ')');
    }

}
