<?php

/**
 * Created by PhpStorm.
 * User: Sisas
 * Date: 28/02/2017
 * Time: 12:15
 */
include_once ("AbstractDispoLogic.php");

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
        $this->addCmd(new CmdDispo("||nr")); //legge correzione corsia 1
        $this->addCmd(new CmdDispo("||or")); //legge correzione corsia 2
        $this->addCmd(new CmdDispo("||vw", $validaOra)); //imposta l'orario di sistema HH:MM:SS
        $this->addCmd(new CmdDispo("||ww", $validaData)); //imposta la data di sistema GG/MM/AA

        $this->addCmd(new CmdDispo("**iw", $max21char)); //imposta l'identificativo del dispo max 21 char
        $this->addCmd(new CmdDispo("**jw", $max30char)); //imposta la via max 34 char
        $this->addCmd(new CmdDispo("**kw", $max30char)); //imposta il kilometro max 34 char
        $this->addCmd(new CmdDispo("**lw", $max30char)); //imposta direzione avanti max 34 char
        $this->addCmd(new CmdDispo("**mw", $max30char)); //imposta direzione indietro max 34 char
        $this->addCmd(new CmdDispo("**nw", $max30char)); //imposta nome della città max 34 char
        $this->addCmd(new CmdDispo("**vw", $max30char)); //imposta site max 32 char
        $this->addCmd(new CmdDispo("**ww", $max30char)); //imposta point max 32 char

        $this->addCmd(new CmdDispo("**ur")); //richiedere la configurazione generale del Compact
        $this->addCmd(new CmdDispo("||lr"));
    }

    function elaboraRisposta(Cmd $cmd): string{
        $return="";
        $keyCmd = substr($cmd->getCmd(), 0,4);
        $valCmd = substr($cmd->getCmd(), 4);
        if($cmd->getResponse()===RES_DELETE){
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

    function encodeCmd(array $keysValues, String $idDispo){
        $strCmd = array();
        $compact = $this->map_cmd['compact'];
        foreach($keysValues as $key => $value){
            $strCmd[] = $compact['w_cmd'][$key].$value."@@".$idDispo."\r";
        }
        return $strCmd;
    }

}
