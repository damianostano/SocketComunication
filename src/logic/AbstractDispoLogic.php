<?php

/**
 * Created by PhpStorm.
 * User: Sisas
 * Date: 28/02/2017
 * Time: 12:15
 */

abstract class AbstractDispoLogic
{
    protected $map_cmd = array();
    protected $cmds = array();


    abstract function __construct($map_cmd);

    function addCmd(CmdDispo $cmd){
        $this->cmds[$cmd->getComando()] = $cmd;
    }

    function cmd($key):CmdDispo{
        return $this->cmds[$key];
    }

    function isCmd($cmd){
        $nomeCmd = "";
        if(is_string($cmd)){
            $nomeCmd = substr($cmd, 0,4);
        }elseif($cmd instanceof DecodeCmd){
            $nomeCmd = substr($cmd->getCmd(), 0,4);
        }
        return array_key_exists($nomeCmd, $this->cmds);
    }

    function validaRispConfig($value) {
        if(preg_match("/(GENERAL SETTING;)([^;\r\n]+;){18}(END GENERAL SETTING)$$/",$value)){
            return true;
        }else{
            return false;
        }
    }



    abstract function elaboraRisposta(Cmd $cmd): string;

    abstract function encodeCmd(array $keysValues, String $idDispo);

}
