<?php

/**
 * Created by PhpStorm.
 * User: Sisas
 * Date: 28/02/2017
 * Time: 12:15
 */

interface AbstractDispoLogic
{

    function __construct();

    function addCmd(CmdDispo $cmd);

    function cmd($key):CmdDispo;

    function isCmd($cmd);

    function validaRispConfig($value);

    function elaboraRisposta(Cmd $cmd): string;

}
