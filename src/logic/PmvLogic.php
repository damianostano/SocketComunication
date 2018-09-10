<?php

/**
 * Created by PhpStorm.
 * User: Sisas
 * Date: 28/02/2017
 * Time: 12:15
 */
include_once ("AbstractDispoLogic.php");
include_once (__DIR__."/../model/CmdDispo.php");

class PmvLogic extends AbstractDispoLogic
{


    /**
     * PmvLogic constructor.
     * @param array $cmds
     */
    function __construct($map_cmd){
        $this->map_cmd = $map_cmd;

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
        $max8char = function($value) {
            if(count($value)<=8){
                return true;
            }else{
                return "Il valore è superiore agli 8 caratteri.";
            }
        };
        $min1max40 = function($value) {
            if(1<=$value && $value <=40){
                return true;
            }else{
                return "Il valore è compreso tra 1 e 40.";
            }
        };
        $min1max10 = function($value) {
            if(1<=$value && $value <=10){
                return true;
            }else{
                return "Il valore è compreso tra 1 e 10.";
            }
        };
        $min1max11 = function($value) {
            if(1<=$value && $value <=11){
                return true;
            }else{
                return "Il valore è compreso tra 1 e 11.";
            }
        };
        $min0max15 = function($value) {
            if(0<=$value && $value <=15){
                return true;
            }else{
                return "Il valore è compreso tra 1 e 15.";
            }
        };
        $min0max255 = function($value) {
            if(0<=$value && $value <=255){
                return true;
            }else{
                return "Il valore è compreso tra 0 e 255.";
            }
        };
        $min0max2 = function($value) {
            if(0<=$value && $value <=1.99){
                return true;
            }else{
                return "Il valore è compreso tra 0 e 1,99.";
            }
        };
        $max21char = function($value) {
            if(count($value)<=21){
                return true;
            }else{
                return "Il valore è superiore ai 21 caratteri.";
            }
        };

        $regexMsg = function($value) {
            if(preg_match("/^([0-3][0-9]|40)[0-2][0-9][0-1](\*\*.{1,29}){1,4}$/",$value)){
                return true;
            }else{
                return "Il valore è formattato correttamente es: 01021**msg row1**msg row2**msg row3.";
            }
        };


        $this->addCmd(new CmdDispo("*iwa", $max8char)); //scrivi identificativo
        $this->addCmd(new CmdDispo("*dwa", $validaData)); //scrivi data sistema
        $this->addCmd(new CmdDispo("*twa", $validaOra)); //scrivi orario sistema
        $this->addCmd(new CmdDispo("*ewa", $min1max40)); //messaggi in sequenza max
        $this->addCmd(new CmdDispo("*hwa", $min1max40)); //attivazione msg specifico solo se msg max=0
        $this->addCmd(new CmdDispo("*cwa", $min1max11)); //num spazi separatori ultima riga
        $this->addCmd(new CmdDispo("*wwa", $min0max15)); //ritardo scroll ultima riga               0-15
        $this->addCmd(new CmdDispo("*mwa", $validaOra)); //orario spegnimento sistema
        $this->addCmd(new CmdDispo("*nwa", $validaOra)); //orario accensione sistema
        $this->addCmd(new CmdDispo("*qwj", $max21char)); //inserimento point
        $this->addCmd(new CmdDispo("*qwk", $max21char)); //inserimento site
        $this->addCmd(new CmdDispo("*qwl", $max8char)); //inserimento password wifi                MAX 8
        $this->addCmd(new CmdDispo("*qwh", $min0max255)); //inserimento luminosità minima          0-255
        $this->addCmd(new CmdDispo("*qwi", $min0max2)); //inserimento amplificazione luminosità    0-1.99
        $this->addCmd(new CmdDispo("*###", $regexMsg)); //inserimento/modifica messaggio

        $this->addCmd(new CmdDispo("*gra")); //lettura dei messaggi: TUTTI se seguito da 99, il numero singolo se da 01 a 40
        $this->addCmd(new CmdDispo("*ura")); //richiedere la configurazione generale del Pmv
    }

    function elaboraRisposta(Cmd $cmd): string{
        $return="";
        $keyCmd = substr($cmd->getCmd(), 0,4);
        $valCmd = substr($cmd->getCmd(), 4);
        if($cmd->getResponse()===RES_DELETE || $cmd->getResponse()===RES_BUSY){
            $return = $cmd->getResponse();
        }else{
            switch ($keyCmd) {
                case "*iwa":
                case "*dwa":
                case "*twa":
                case "*ewa":
                case "*hwa":
                case "*cwa":
                case "*wwa":
                case "*mwa":
                case "*nwa":
                case "*qwj":
                case "*qwk":
                case "*qwl":
                case "*qwh":
                case "*qwi":
                    if($cmd->getResponse()==="ok"){
                        $return = $this->map_cmd['pmv']['campo'][$keyCmd]."=".$valCmd;
                    }else{
                        $return = $cmd->getResponse();
                    }
                    break;
                case "*###":
                    $return = $cmd->getResponse();
                    break;
                case "*gra":
                    $list_msg = explode("*###", trim($cmd->getResponse()));
                    array_shift($list_msg); //il primo è "LIST MESSAGE"
                    $n_msg = count($list_msg); //l'ultimo messaggio però finisce con "END LIST MESSAGE"
                    if($valCmd=="99") {
                        $list_msg[$n_msg - 1] = str_replace("END LIST MESSAGE", "", $list_msg[$n_msg - 1]);
                    }
                    $return = implode(";;", $list_msg);
                    break;
                case "*ura":
                    $response = explode(";", trim($cmd->getResponse()));
                    $campi = $this->map_cmd['pmv']['campo'];
                    $n_campi = count($campi);
                    if(count($response)==$n_campi+2 && $response[0]==="GENERAL SETTING" && $response[$n_campi+1]==="END GENERAL SETTING"){
                        array_pop($response); //tolgo l'ultimo
                        array_shift($response); //tolgo il 1°
                        $i=0;
                        foreach ($this->map_cmd['pmv']['campo'] as $key => $value){
                            $return.=$value."=".$response[$i].";";
                            $i++;
                        }
                    }else{
                        throw new Exception("Messaggio di risposta alla configurazione generale del Pmv non conforme!");
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
        $dispo = $this->map_cmd['pmv'];
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
        return parent::filterConfig($dati_dispo, 'pmv');
    }

    public function updateConfig(array $dispo, PDO $db=null){
        $ok=true;
        try{
            if($db==null){
                throw new Exception("In updateConfig non è stato passato il DB");
            }
            $db->beginTransaction();

            /**
             * Nella configurazione dentro al dispositivo non ci sono parametri salvati nella tabella generale "dispositivo"
             * Ha invece una tabella di dettaglio 1 a N per i messaggi e la rispettiva configurazione
             */
//            $sql = "UPDATE dispositivo
//                    SET citta = :citta,
//                        via = :via,
//                        rif_km = :rif_km
//                    WHERE id_dispo=:id_dispo ";
//            $stmt = $db->prepare($sql);
//            $stmt->bindValue(':id_dispo',       $dispo['id_dispo'],   PDO::PARAM_STR);
//            $stmt->bindValue(':citta',          $dispo['citta'],      PDO::PARAM_STR);
//            $stmt->bindValue(':via',            $dispo['via'],        PDO::PARAM_STR);
//            $stmt->bindValue(':rif_km',         $dispo['rif_km'],     PDO::PARAM_STR);
//            $n_affected1 = $stmt->execute();

            $sql = "UPDATE dispo_pmv
                    SET v_bios = :v_bios,
                        volt_batteria = :volt_batteria,
                        giorno = :giorno,
                        ora = :ora,
                        site = :site,
                        point = :point,
                        max_sequenza_msg = :max_sequenza_msg,
                        num_msg_attivo = :num_msg_attivo,
                        num_blank_last_msg = :num_blank_last_msg,
                        ritardo_scroll = :ritardo_scroll,
                        ora_off_pmv = :ora_off_pmv,
                        ora_on_pmv = :ora_on_pmv,
                        num_colonne = :num_colonne,
                        num_righe = :num_righe,
                        pwd_wifi = :pwd_wifi,
                        min_luminosita = :min_luminosita,
                        ampl_luminosita = :ampl_luminosita
                    WHERE id_dispo=:id_dispo ";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':id_dispo',       $dispo['id_dispo'],       PDO::PARAM_STR);
            $stmt->bindValue(':v_bios',         $dispo['v_bios'],         PDO::PARAM_STR);
            $stmt->bindValue(':volt_batteria',  $dispo['volt_batteria'],  PDO::PARAM_STR);
            $stmt->bindValue(':giorno',         $dispo['giorno'],         PDO::PARAM_STR);
            $stmt->bindValue(':ora',            $dispo['ora'],            PDO::PARAM_INT);
            $stmt->bindValue(':site',           $dispo['site'],           PDO::PARAM_STR);
            $stmt->bindValue(':point',          $dispo['point'],          PDO::PARAM_STR);
            $stmt->bindValue(':max_sequenza_msg',   $dispo['max_sequenza_msg'],     PDO::PARAM_STR);
            $stmt->bindValue(':num_msg_attivo',     $dispo['num_msg_attivo'],       PDO::PARAM_STR);
            $stmt->bindValue(':num_blank_last_msg', $dispo['num_blank_last_msg'],   PDO::PARAM_STR);
            $stmt->bindValue(':ritardo_scroll',     $dispo['ritardo_scroll'],       PDO::PARAM_STR);
            $stmt->bindValue(':ora_off_pmv',        $dispo['ora_off_pmv'],          PDO::PARAM_STR);
            $stmt->bindValue(':ora_on_pmv',         $dispo['ora_on_pmv'],           PDO::PARAM_STR);
            $stmt->bindValue(':num_colonne',        $dispo['num_colonne'],          PDO::PARAM_STR);
            $stmt->bindValue(':num_righe',          $dispo['num_righe'],            PDO::PARAM_STR);
            $stmt->bindValue(':pwd_wifi',           $dispo['pwd_wifi'],             PDO::PARAM_STR);
            $stmt->bindValue(':min_luminosita',     $dispo['min_luminosita'],       PDO::PARAM_STR);
            $stmt->bindValue(':ampl_luminosita',    $dispo['ampl_luminosita'],      PDO::PARAM_STR);
            $stmt->execute();

            //TODO: vedere i messaggi che ci sono

            //TODO: quelli che ci sono vanno salvati e gli altri (quelli di cui non c'è il num) cancellati
//            $dispo['msg'];

            $db->commit();
        }catch (PDOException $e){
            $ok=false;
            Logger::getLogger("monitor.disconnTime")->error("!!!! ERRORE nell'aggiornamento della configurazione di ".$dispo['id_dispo']." dopo la sua connessione!", $e);
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
        return $this->selectDispoById($id_dispo, "dispo_pmv", $db);
    }


    function getCmdReadConfig(): string{
        return $this->map_cmd["pmv"]["r_cmd"]["config"];
    }

    function getCmdReadMsg(string $num="99"): string{
        if( ($num<1 || 40<$num) && $num!=99){
            throw new Exception("Numero del Messaggio da leggere non valido! Il PMV consente da 01 a 40 più 99 per leggere tutti i messaggi.");
        }
        $cmd = $this->map_cmd["pmv"]["r_cmd"]["read_msg"];
        $cmd .= $num;
        return $cmd;
    }

    function getCmdWriteMsg($idDispo,$num_msg,$effetto,$tempo_visualizzazione,$attivo,$msg): string{
        $cmd = $this->map_cmd["pmv"]["w_cmd"]["inserimento/modifica messaggio"];
        $cmd .= $num_msg.$effetto.$tempo_visualizzazione.$attivo."**".$msg."@@".$idDispo."\r";
        return $cmd;
    }

//    function isCmd($cmd){
//        $nomeCmd = "";
//        if(is_string($cmd)){
//            $nomeCmd = explode("@", $cmd)[0];
//        }elseif($cmd instanceof DecodeCmd){
//            $nomeCmd = explode("@", $cmd->getCmd())[0];
//        }
//        return array_key_exists($nomeCmd, $this->cmds);
//    }


}
