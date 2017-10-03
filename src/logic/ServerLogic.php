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

        $this->addCmd(new CmdDispo("quit")); //legge correzione corsia 1
        $this->addCmd(new CmdDispo("list_dispo")); //legge correzione corsia 2
        $this->addCmd(new CmdDispo("list_user")); //richiedere la configurazione generale del Compact
        $this->addCmd(new CmdDispo("logout_user")); //richiedere la disconnessione di un dispositivo o user
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
