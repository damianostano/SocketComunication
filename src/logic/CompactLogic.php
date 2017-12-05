<?php

/**
 * Created by PhpStorm.
 * User: Sisas
 * Date: 28/02/2017
 * Time: 12:15
 */
include_once ("AbstractDispoLogic.php");
include_once (__DIR__."/../model/CmdDispo.php");

class CompactLogic extends AbstractDispoLogic
{


    /**
     * CompactLogic constructor.
     * @param array $cmds
     */
    function __construct($map_cmd){
        $this->map_cmd = $map_cmd;

        $max21char = function($value) {
            if(count($value)<=21){
                return true;
            }else{
                return "Il valore è superiore ai 21 caratteri.";
            }
        };
        $max30char = function($value) {
            if(count($value)<=30){
                return true;
            }else{
                return "Il valore è superiore ai 34 caratteri.";
            }
        };
        $min0max3 = function($value) {
            if(0<=$value && $value <=3){
                return true;
            }else{
                return "Il valore è compreso tra 0 e 3.";
            }
        };
        $min2max4 = function($value) {
            if(2<=$value && $value <=4){
                return true;
            }else{
                return "Il valore è compreso tra 2 e 4.";
            }
        };
        $min0max10 = function($value) {
            if(0<=$value && $value <10){
                return true;
            }else{
                return "Il valore è compreso tra 0 e 9.99.";
            }
        };
        $validaData = function($value) {
            if(preg_match("/^(0[1-9]|[1-2][0-9]|3[0-1])\/(0[1-9]|1[0-2])\/[0-9]{2}$/",$value)){
                return true;
            }else{
                return "Il valore non è una data nel formato GG/MM/AA.";
            }
        };
        $validaOra = function($value) {
            if(preg_match("/^([0-1][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])$/",$value)){
                return true;
            }else{
                return "Il valore non è un'ora nel formato HH:MM:SS.";
            }
        };

        $this->addCmd(new CmdDispo("||jw", $min2max4)); //imposta counting (2=misura 2 corsie, 3=misura corsia avanti, 4=misura corsie indietro)

        $this->addCmd(new CmdDispo("||mw", $min0max3)); //imposta sensibilità da 0 a 3, 0 default
        $this->addCmd(new CmdDispo("||nr")); //legge correzione avanti
        $this->addCmd(new CmdDispo("||or")); //legge correzione dietro
        $this->addCmd(new CmdDispo("||nw", $min0max10)); //scrive correzione avanti
        $this->addCmd(new CmdDispo("||ow", $min0max10)); //scrive correzione dietro
        $this->addCmd(new CmdDispo("||vw", $validaOra)); //imposta l'orario di sistema HH:MM:SS
        $this->addCmd(new CmdDispo("||ww", $validaData)); //imposta la data di sistema GG/MM/AA

        $this->addCmd(new CmdDispo("**iw", $max21char)); //imposta l'identificativo del dispo max 21 char
        $this->addCmd(new CmdDispo("**jw", $max30char)); //imposta la via max 34 char
        $this->addCmd(new CmdDispo("**kw", $max30char)); //imposta il kilometro max 34 char
        $this->addCmd(new CmdDispo("**lw", $max30char)); //imposta direzione avanti max 34 char
        $this->addCmd(new CmdDispo("**mw", $max30char)); //imposta direzione indietro max 34 char
        $this->addCmd(new CmdDispo("**nw", $max30char)); //imposta nome della città max 34 char
        $this->addCmd(new CmdDispo("**vw", $max21char)); //imposta site
        $this->addCmd(new CmdDispo("**ww", $max21char)); //imposta point

        $this->addCmd(new CmdDispo("**ur")); //richiedere la configurazione generale del Compact
        $this->addCmd(new CmdDispo("||lr"));
    }

    function elaboraRisposta(Cmd $cmd): string{
        $return="";
        $keyCmd = substr($cmd->getCmd(), 0,4);
        $valCmd = substr($cmd->getCmd(), 4);
        if($cmd->getResponse()===RES_DELETE || $cmd->getResponse()===RES_BUSY){
            $return = $cmd->getResponse();
        }else{
            switch ($keyCmd) {
                case "||jw":
                case "||mw":
                case "||vw":
                case "||ww":
                case "**iw":
                case "**jw":
                case "**kw":
                case "**lw":
                case "**mw":
                case "**nw":
                case "**vw":
                case "**ww":
                case "||nw":
                case "||ow":
                case "**mw":
                    if($cmd->getResponse()==="ok"){
                        $return = $this->map_cmd['compact']['campo'][$keyCmd]."=".$valCmd;
                    }else{
                        $return = $cmd->getResponse();
                    }
                    break;
                case "||nr":
                case "||or":
                case "||lr":
                    $return = $this->map_cmd['compact']['campo'][$keyCmd]."=".$cmd->getResponse();
                    break;
                case "**ur":
                    $response = explode(";", trim($cmd->getResponse()));
                    if(count($response)==18 && $response[0]==="GENERAL SETTING" && $response[17]==="END GENERAL SETTING"){
                        array_pop($response); //tolgo l'ultimo
                        array_shift($response); //tolgo il 1°
                        $i=0;
                        foreach ($this->map_cmd['compact']['campo'] as $key => $value){
                            $return.=$value."=".$response[$i].";";
                            $i++;
                        }
                    }else{
                        throw new Exception("Messaggio di risposta alla configurazione generale del Compact non conforme!");
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
                if($e[0]=="giorno"){
                    $e[1] = CompactLogic::toMySqlDate($e[1]);
                }
                $new_array[$e[0]] = $e[1];
            }
        }
        return $new_array;
    }


    function encodeCmd(array $keysValues, String $idDispo){
        $strCmd = array();
        $compact = $this->map_cmd['compact'];
        foreach($keysValues as $key => $value){
            if($key=="giorno"){
                $value = CompactLogic::fromMySqlDate($value);
            }
            $strCmd[] = $compact['w_cmd'][$key].$value."@@".$idDispo."\r";
        }
        return $strCmd;
    }

    function filterConfig(array $dati_dispo): array{
        $conf = array();
        foreach ($dati_dispo as $key => $val){
            if(array_key_exists($key, $this->map_cmd['compact']['w_cmd'])){
                $conf[$key] = $val;
            }
        }
        return $conf;
    }

    public function updateConfig(array $compact, PDO $db=null){
        try{
            if($db==null){
                throw new Exception("In updateConfig non è stato passato il DB");
            }
            $db->beginTransaction();

            $sql = "UPDATE dispositivo  
                    SET citta = :citta,
                        via = :via,
                        rif_km = :rif_km
                    WHERE id_dispo=:id_dispo ";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':id_dispo',       $compact['id_dispo'],   PDO::PARAM_STR);
            $stmt->bindValue(':citta',          $compact['citta'],      PDO::PARAM_STR);
            $stmt->bindValue(':via',            $compact['via'],        PDO::PARAM_STR);
            $stmt->bindValue(':rif_km',         $compact['rif_km'],     PDO::PARAM_STR);
            $n_affected1 = $stmt->execute();

            $sql = "UPDATE dispo_compact
                    SET v_bios = :v_bios,
                        volt_batteria = :volt_batteria,
                        dir_avanti = :dir_avanti,
                        dir_dietro = :dir_dietro,
                        counting = :counting,
                        amplificazione = :amplificazione,
                        giorno = :giorno,
                        ora = :ora,
                        corr_avanti = :corr_avanti,
                        corr_dietro = :corr_dietro,
                        site = :site,
                        point = :point
                    WHERE id_dispo=:id_dispo ";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':id_dispo',       $compact['id_dispo'],       PDO::PARAM_STR);
            $stmt->bindValue(':v_bios',         $compact['v_bios'],         PDO::PARAM_STR);
            $stmt->bindValue(':volt_batteria',  $compact['volt_batteria'],  PDO::PARAM_STR);
            $stmt->bindValue(':dir_avanti',     $compact['dir_avanti'],     PDO::PARAM_STR);
            $stmt->bindValue(':dir_dietro',     $compact['dir_dietro'],     PDO::PARAM_STR);
            $stmt->bindValue(':counting',       $compact['counting'],       PDO::PARAM_STR);
            $stmt->bindValue(':amplificazione', $compact['amplificazione'], PDO::PARAM_STR);
            $stmt->bindValue(':giorno',         $compact['giorno'],         PDO::PARAM_STR);
            $stmt->bindValue(':ora',            $compact['ora'],            PDO::PARAM_INT);
            $stmt->bindValue(':corr_avanti',    $compact['corr_avanti'],    PDO::PARAM_STR);
            $stmt->bindValue(':corr_dietro',    $compact['corr_dietro'],    PDO::PARAM_STR);
            $stmt->bindValue(':site',           $compact['site'],           PDO::PARAM_STR);
            $stmt->bindValue(':point',          $compact['point'],          PDO::PARAM_STR);
            $n_affected2 = $stmt->execute();

            $ok = $n_affected1 === $n_affected2;
            if($ok) {
                $db->commit();
            }else{
                $db->rollBack();
            }
        }catch (PDOException $e){
            Logger::getLogger("monitor.appendCmd")->error("!!!! ERRORE nell'aggiornamento della configurazione di".$compact['id_dispo']." dopo la sua connessione!", $e);
            try{
                $db->rollBack();
            }catch (PDOException $e){
                Logger::getLogger("monitor.appendCmd")->error("!!!!!!!!!!!!!! ERRORE nel ROLL-BACK della transazione!", $e);
            }
        }
        return $ok;
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


}
