<?php

/**
 * Created by PhpStorm.
 * User: Sisas
 * Date: 28/02/2017
 * Time: 12:15
 */
include_once ("AbstractDispoLogic.php");

class ServerLogic extends AbstractDispoLogic
{

    /**
     * CompactLogic constructor.
     * @param array $cmds
     */
    function __construct($map_cmd){
        $this->map_cmd = $map_cmd;

        $this->addCmd(new CmdDispo("quit")); //spegnere il server (non funziona come dovrebbe)
        $this->addCmd(new CmdDispo("list_dispo")); //richiede la lista dei dispositivi connessi per una certa istanza
        $this->addCmd(new CmdDispo("list_user")); //richiede la lista di tutti gli utenti attualmente connessi
        $this->addCmd(new CmdDispo("logout_user")); //richiedere la disconnessione di uno user
        $this->addCmd(new CmdDispo("WAIT")); //richiedere la disconnessione di uno user
        $this->addCmd(new CmdDispo("READY")); //richiedere la disconnessione di uno user
    }

    function elaboraRisposta(Cmd $cmd): string{
        $return="";
        $c = explode("*", trim($cmd->getCmd()));
        $keyCmd = $c[0];
        $valCmd = $c[1];
        if($cmd->getResponse()===RES_DELETE){
            $return = $cmd->getResponse();
        }else{
            switch ($keyCmd) {
                case "quit":break;
                case "list_dispo":
                case "list_user":
                case "WAIT":
                case "READY":
                    $return = $cmd->getResponse();
                    break;
                default: $return = $cmd->getResponse();
            }
        }

        return $return;
    }

    function encodeCmd(array $keysValues, String $idDispo){
        $idDispo= Cmd::$SERVER;
        $strCmd = array();
        $server = $this->map_cmd['server'];
        foreach($keysValues as $key => $istanza){
            $strCmd[] = $server['r_cmd'][$key]."*".$istanza."@@".$idDispo."\r";
        }
        return $strCmd;
    }


    function isCmd($cmd){
        $nomeCmd = "";
        if(is_string($cmd)){
            $c = explode("*", trim($cmd));
            $nomeCmd = $c[0];
        }elseif($cmd instanceof DecodeCmd){
            $c = explode("*", trim($cmd->getCmd()));
            $nomeCmd = $c[0];
        }
        return array_key_exists($nomeCmd, $this->cmds);
    }

    static function getCmd(Cmd $cmd){
        $c = explode("*", trim($cmd->getCmd()));
        return $c[0];
    }
    static function getValue(Cmd $cmd){
        $c = explode("*", trim($cmd->getCmd()));
        if(isset($c[1]))
            return $c[1];
        else
            return null;
    }

}
