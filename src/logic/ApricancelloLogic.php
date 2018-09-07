<?php

/**
 * Created by PhpStorm.
 * User: Sisas
 * Date: 28/02/2017
 * Time: 12:15
 */
include_once ("AbstractDispoLogic.php");
include_once (__DIR__."/../model/CmdDispo.php");

class ApricancelloLogic extends AbstractDispoLogic
{


    /**
     * CompactLogic constructor.
     * @param array $cmds
     */
    function __construct($map_cmd){
        $this->map_cmd = $map_cmd;
        $this->addCmd(new CmdDispo("**ur")); //richiedere la configurazione generale del Compact
    }

    function elaboraRisposta(Cmd $cmd): string{
        $return="";
        $keyCmd = substr($cmd->getCmd(), 0,4);
        $valCmd = substr($cmd->getCmd(), 4);
        if($cmd->getResponse()===RES_DELETE || $cmd->getResponse()===RES_BUSY){
            $return = $cmd->getResponse();
        }else{
            switch ($keyCmd) {
                case "**ur":
                    $response = explode(";", trim($cmd->getResponse()));
//                    if(count($response)==1){
//                        $i=0;
//                        foreach ($this->map_cmd['apricancello']['campo'] as $key => $value){
//                            $return.=$value."=".$response[$i].";";
//                            $i++;
//                        }
//                    }else{
                    if(count($response)==4 && $response[0]==="GENERAL SETTING" && $response[3]==="END GENERAL SETTING"){
                        array_pop($response); //tolgo l'ultimo
                        array_shift($response); //tolgo il 1°
                        $i=0;
                        foreach ($this->map_cmd['apricancello']['campo'] as $key => $value){
                            $return.=$value."=".$response[$i].";";
                            $i++;
                        }
                    }else{
                        throw new Exception("Messaggio di risposta alla configurazione generale dell'Apricancello non conforme!");
                    }
                    break;
                default: $return = $cmd->getResponse();
            }
        }

        return $return;
    }

    function decodeConfigInDbForm(String $conf): array{
        $array = explode(';', $conf);
        $new_array = array();
        foreach($array as $key => $val){
            if($val){
                $e = explode('=',$val);
                $new_array[$e[0]] = $e[1];
            }
        }
        return $new_array;
    }


    function encodeCmd(array $keysValues, String $idDispo, $completo=true){
        $strCmd = array();
        $dispo = $this->map_cmd['apricancello'];
        foreach($keysValues as $key => $value){
            if($completo){
                $strCmd[] = $dispo['w_cmd'][$key].$value."@@".$idDispo."\r";
            }else{
                $strCmd[] = $dispo['w_cmd'][$key].$value;
            }
        }
        return $strCmd;
    }

    function filterConfig(array $dati_dispo): array{
        $conf = array();
        foreach ($dati_dispo as $key => $val){
            if(array_key_exists($key, $this->map_cmd['apricancello']['w_cmd'])){
                $conf[$key] = $val;
            }
        }
        return $conf;
    }

    public function updateConfig(array $apricancello, PDO $db=null){
        try{
            if($db==null){
                throw new Exception("In updateConfig non è stato passato il DB");
            }
            $db->beginTransaction();

            $sql = "UPDATE dispo_apricancello
                    SET v_bios = :v_bios
                    WHERE id_dispo=:id_dispo ";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':id_dispo', $apricancello['id_dispo'], PDO::PARAM_STR);
            $stmt->bindValue(':v_bios',   $apricancello['v_bios'],   PDO::PARAM_STR);
            $stmt->execute();
            $db->commit();
        }catch (PDOException $e){
            Logger::getLogger("monitor.disconnTime")->error("!!!! ERRORE nell'aggiornamento della configurazione di ".$apricancello['id_dispo']." dopo la sua connessione!", $e);
            try{
                $db->rollBack();
            }catch (PDOException $e){
                Logger::getLogger("monitor.disconnTime")->error("!!!!!!!!!!!!!! ERRORE nel ROLL-BACK della transazione!", $e);
            }
            return false;
        }
        return true;
    }

    function getCmdReadConfig(): string{
        return $this->map_cmd["compact"]["r_cmd"]["config"];
    }

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


    public function getMailFromDispo(string $idDispo){
        $this->db = DB::getDb();
        $stmt_select = $this->db->prepare("SELECT mail_riferimento FROM dispo_apricancello WHERE id_dispo = :id_dispo");
        $stmt_select->bindValue(":id_dispo", $idDispo, PDO::PARAM_STR);
        $stmt_select->execute();
        $mail_dispo = $stmt_select->fetchAll(PDO::FETCH_ASSOC);
        if($mail_dispo==null || count($mail_dispo)===0){
            return false;
        }else{
            return $mail_dispo[0]['mail_riferimento'];
        }
    }

}
