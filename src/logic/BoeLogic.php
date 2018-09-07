<?php

/**
 * Created by PhpStorm.
 * User: Sisas
 * Date: 28/02/2017
 * Time: 12:15
 */
include_once ("AbstractDispoLogic.php");
include_once (__DIR__."/../model/CmdDispo.php");

class BoeLogic extends AbstractDispoLogic
{


    /**
     * BoeLogic constructor.
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
        $min1max8 = function($value) {
            if(1<=$value && $value <=8){
                return true;
            }else{
                return "Il valore è compreso tra 1 e 8.";
            }
        };
        $min0max10 = function($value) {
            if(0.1<=$value && $value <10){
                return true;
            }else{
                return "Il valore è compreso tra 0,1 e 9,99.";
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

        $this->addCmd(new CmdDispo("||jw", $min1max8)); //imposta numero di corsie

        $this->addCmd(new CmdDispo("||mw", $min0max3)); //imposta sensibilità da 0 a 3, 0 default
        $this->addCmd(new CmdDispo("||nr")); //legge correzione avanti
        $this->addCmd(new CmdDispo("||or")); //legge correzione dietro
        $this->addCmd(new CmdDispo("||nw", $min0max10)); //scrive distanza boe F1
        $this->addCmd(new CmdDispo("||ow", $min0max10)); //scrive distanza boe B1
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
                case "||vw":
                case "||ww":
                case "**iw":
                case "**jw":
                case "**kw":
                case "**lw":
                case "**nw":
                case "**vw":
                case "**ww":
                case "||nw":
                case "||ow":
                case "**mw":
                    if($cmd->getResponse()==="ok"){
                        $return = $this->map_cmd['boe']['campo'][$keyCmd]."=".$valCmd;
                    }else{
                        $return = $cmd->getResponse();
                    }
                    break;
                case "||nr":
                case "||or":
                case "||lr":
                    $return = $this->map_cmd['boe']['campo'][$keyCmd]."=".$cmd->getResponse();
                    break;
                case "**ur":
                    $response = explode(";", trim($cmd->getResponse()));
                    $campi = $this->map_cmd['boe']['campo'];
                    $n_campi = count($campi);
                    if(count($response)==$n_campi+2 && $response[0]==="GENERAL SETTING" && $response[$n_campi+1]==="END GENERAL SETTING"){
                        array_pop($response); //tolgo l'ultimo
                        array_shift($response); //tolgo il 1°
                        $i=0;
                        foreach ($this->map_cmd['boe']['campo'] as $key => $value){
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
                    $e[1] = AbstractDispoLogic::toMySqlDate($e[1]);
                }
                $new_array[$e[0]] = $e[1];
            }
        }
        return $new_array;
    }


    function encodeCmd(array $keysValues, String $idDispo, $completo=true){
        $strCmd = array();
        $dispo = $this->map_cmd['boe'];
        foreach($keysValues as $key => $value){
            if($key=="giorno"){
                $value = AbstractDispoLogic::fromMySqlDate($value);
            }
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
            if(array_key_exists($key, $this->map_cmd['boe']['w_cmd'])){
                $conf[$key] = $val;
            }
        }
        return $conf;
    }

    public function updateConfig(array $boe, PDO $db=null){
        $ok = false;
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
            $stmt->bindValue(':id_dispo',       $boe['id_dispo'],   PDO::PARAM_STR);
            $stmt->bindValue(':citta',          $boe['citta'],      PDO::PARAM_STR);
            $stmt->bindValue(':via',            $boe['via'],        PDO::PARAM_STR);
            $stmt->bindValue(':rif_km',         $boe['rif_km'],     PDO::PARAM_STR);
            $n_affected1 = $stmt->execute();

            $sql = "UPDATE dispo_boe
                    SET v_bios = :v_bios,
                        volt_batteria = :volt_batteria,
                        dir_avanti = :dir_avanti,
                        dir_dietro = :dir_dietro,
                        n_corsie = :n_corsie,
                        giorno = :giorno,
                        ora = :ora,
                        distanza_boe_F1 = :distanza_boe_F1,
                        distanza_boe_F1 = :distanza_boe_F1,
                        site = :site,
                        point = :point
                    WHERE id_dispo=:id_dispo ";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':id_dispo',       $boe['id_dispo'],       PDO::PARAM_STR);
            $stmt->bindValue(':v_bios',         $boe['v_bios'],         PDO::PARAM_STR);
            $stmt->bindValue(':volt_batteria',  $boe['volt_batteria'],  PDO::PARAM_STR);
            $stmt->bindValue(':dir_avanti',     $boe['dir_avanti'],     PDO::PARAM_STR);
            $stmt->bindValue(':dir_dietro',     $boe['dir_dietro'],     PDO::PARAM_STR);
            $stmt->bindValue(':n_corsie',       $boe['n_corsie'],       PDO::PARAM_STR);
            $stmt->bindValue(':amplificazione', $boe['amplificazione'], PDO::PARAM_STR);
            $stmt->bindValue(':giorno',         $boe['giorno'],         PDO::PARAM_STR);
            $stmt->bindValue(':ora',            $boe['ora'],            PDO::PARAM_INT);
            $stmt->bindValue(':distanza_boe_F1',$boe['distanza_boe_F1'],PDO::PARAM_STR);
            $stmt->bindValue(':distanza_boe_F1',$boe['distanza_boe_F1'],PDO::PARAM_STR);
            $stmt->bindValue(':site',           $boe['site'],           PDO::PARAM_STR);
            $stmt->bindValue(':point',          $boe['point'],          PDO::PARAM_STR);
            $n_affected2 = $stmt->execute();

            $ok = $n_affected1 === $n_affected2;
            if($ok) {
                $db->commit();
            }else{
                $db->rollBack();
            }
        }catch (PDOException $e){
            Logger::getLogger("monitor.disconnTime")->error("!!!! ERRORE nell'aggiornamento della configurazione di ".$boe['id_dispo']." dopo la sua connessione!", $e);
            try{
                $db->rollBack();
            }catch (PDOException $e){
                Logger::getLogger("monitor.disconnTime")->error("!!!!!!!!!!!!!! ERRORE nel ROLL-BACK della transazione!", $e);
            }
        }
        return $ok;
    }

    /**
     * Ottieni la configurazione del dispositivo nel DB
     * @param array $id_dispo
     * @return mixed
     */
    function getConfig(string $id_dispo, PDO $db = null){
        return $this->selectDispoById($id_dispo, "dispo_boe", $db);
    }


    function getCmdReadConfig(): string{
        return $this->map_cmd["boe"]["r_cmd"]["config"];
    }




}
