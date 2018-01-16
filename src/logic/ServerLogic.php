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
        $this->addCmd(new CmdDispo("WAIT")); //il dispo ci comunica che è occupato a fare altro e non può ne ricevere comandi dal server tantomeno rispondere
        $this->addCmd(new CmdDispo("READY")); //il dispo ci comunica che è tornato ricevente
        $this->addCmd(new CmdDispo(".")); //keepalive del dispo
        $this->addCmd(new CmdDispo("MAIL")); //keepalive del dispo
    }

    function elaboraRisposta(Cmd $cmd): string{
        $return="";
        $c = explode("*", trim($cmd->getCmd()));
        $keyCmd = explode("{", $c[0]);//tolgo i parametri eventualmente presenti CMD{Param1=val1;Param2=val2;}*idDispo
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
                case ".":
                case "MAIL":
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
            $c = explode("{", $c[0]);//tolgo i parametri eventualmente presenti CMD{Param1=val1;Param2=val2;}*idDispo
            $nomeCmd = $c[0];
        }elseif($cmd instanceof DecodeCmd){
            $c = explode("*", trim($cmd->getCmd()));
            $c = explode("{", $c[0]);//tolgo i parametri eventualmente presenti CMD{Param1=val1;Param2=val2;}*idDispo
            $nomeCmd = $c[0];
        }
        return array_key_exists($nomeCmd, $this->cmds);
    }

    /**
     * Per utilizzi futuri...forse
     * @param String $conf
     * @return array
     */
    function decodeConfigInDbForm(String $conf): array{
        return null;
    }

    /**
     * Per utilizzi futuri...forse
     * @return string
     */
    function getCmdReadConfig(): string{
        return null;
    }

    /**
     * Per utilizzi futuri...forse
     * @return string
     */
    function updateConfig(array $config, PDO $db=null): string{
        return null;
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
