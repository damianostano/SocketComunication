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
        if(preg_match("/(GENERAL SETTING;)([^;\r\n]+;)+(END GENERAL SETTING)$$/",$value)){
            return true;
        }else{
            return false;
        }
    }

    abstract function decodeConfigInDbForm(String $conf): array;

    /**
     * Aggiorna la configurazione del dispositivo specifico
     * @param array $config La configurazione come ritornata da decodeConfigInDbForm();
     * @return mixed
     */
    abstract function updateConfig(array $config, PDO $db=null);

    /**
     * Ottiene il comando che serve per avere la configurazione del dispositivo
     * @return string (es.: return $this->map_cmd["compact"]["r_cmd"]["config"];)
     */
    abstract function getCmdReadConfig(): string;

    abstract function elaboraRisposta(Cmd $cmd): string;

    abstract function encodeCmd(array $keysValues, String $idDispo);

    /**
     * @param $data La DATA nel formato gg-mm-aa
     * @return string Viene convertita nel formato aaaa-mm-gg
     */
    public static function toMySqlDate($data){
        $newDate = date_create_from_format("d/m/y", $data);
        return $newDate->format('Y-m-d');
    }

    /**
     * @param $data La DATA nel formato aaaa-mm-gg
     * @return string Viene convertita nel formato gg-mm-aa
     */
    public static function fromMySqlDate($data){
        $newDate = DateTime::createFromFormat("Y-m-d", $data);
        return $newDate->format('d/m/y');
    }
}
