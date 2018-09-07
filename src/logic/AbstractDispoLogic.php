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
     * Aggiorna la configurazione del dispositivo nel DB copiandoci quella del dispo reale
     * @param array $config La configurazione come ritornata da decodeConfigInDbForm();
     * @return mixed
     */
    abstract function updateConfig(array $config, PDO $db=null);

    /**
     * Ottieni la configurazione del dispositivo nel DB
     * @param array $id_dispo
     * @return mixed
     */
    abstract function getConfig(string $id_dispo, PDO $db=null);
    public function selectDispoById($id_dispo, $tabelle_dettaglio, PDO $db=null){
        if ($db == null) {
            throw new Exception("In updateConfig non è stato passato il DB");
        }
        $sql = "SELECT * FROM dispositivo di
                JOIN ".$tabelle_dettaglio." as de ON di.id_dispo = de.id_dispo 
                    AND di.id_dispo = :id_dispo ";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':id_dispo', $id_dispo, PDO::PARAM_STR);
        $stmt->execute();
        $models = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if($models==null || count($models)===0){
            return array();
        }else{
            return $models[0];
        }
    }

    public function resetToSaveInDispo(string $id_dispo, PDO $db=null){
        try{
            if($db==null){
                throw new Exception("In resetToSaveInDispo non è stato passato il DB");
            }
            $sql = "UPDATE dispositivo  
                    SET to_save_in_dispo = 0,
                    WHERE id_dispo=:id_dispo ";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':id_dispo', $id_dispo, PDO::PARAM_BOOL);
            $n_affected1 = $stmt->execute();
        }catch (PDOException $e){
            Logger::getLogger("monitor.disconnTime")->error("!!!! ERRORE nel retettaggio di 'to_save_in_dispo' di '$id_dispo' !", $e);
        }
    }

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
