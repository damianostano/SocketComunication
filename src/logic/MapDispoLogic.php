<?php

/**
 * Created by PhpStorm.
 * User: Sisas
 * Date: 22/08/2017
 * Time: 11:55
 *
 * Si occupa di reperire dal DB la lista dei dispositivi censiti ed assegna ad ogniuno di loro in base all'ID la logica corretta.
 * Funge anche da interfaccia per arrivare alla logica giusa a partire dall'ID del dipositivo
 */
include_once('CompactLogic.php');
include_once(__DIR__."/../exception/NotMappedException.php");
include_once (__DIR__."/../dao/DB.php");




class MapDispoLogic
{
    public $db = null;
    public $map_cmd = null;
    public $mapLogic = array();//tipo del dispositivo -> logica del tipo
    public $mapDispo = array("server"=>"server");//idDispo -> tipo del dispositivo
    public $mapIstanzeDispo = array();//istanza -> lista dei dispositivi


    /**
     * MapDispoLogic constructor.
     * @param array $mapLogic
     */
    public function __construct(array $map_cmd){
        $this->refreshDispositivi();
        $this->map_cmd = $map_cmd;
        foreach ($this->map_cmd as $typeDispo => $configDispo){
            $this->mapLogic[$typeDispo] = $configDispo['logic'];
        }
    }

    public function refreshDispositivi(){
        $this->db = DB::getDb();
        $stmt_select = $this->db->prepare("SELECT * FROM dispositivo ");
        $stmt_select->execute();
        $dispositivi = $stmt_select->fetchAll(PDO::FETCH_ASSOC);
//        $this->mapDispo = array(); //è inzializzato con "server=>"server"
        foreach ($dispositivi as $key => $dispo){
            $this->mapDispo[$dispo['id_dispo']] = $dispo['tipo'];
            $this->mapIstanzeDispo[$dispo['istanza']][] = $dispo['id_dispo'];
        }
    }


    public function getLogic($idDispo){
        if(isset($this->mapLogic[$this->mapDispo[$idDispo]])){
            $logic = new $this->mapLogic[$this->mapDispo[$idDispo]]($this->map_cmd);
        }else{
            throw new NotMappedException("Dispositivo ".$idDispo." non mappato in this->mapLogic.");
        }
        return $logic;
    }
    public function cmd(String $cmd, String $tipoDispo){
        throw new Exception("Funzione da rivedere... usato ec_cmd che non c'è più");
        return $this->map_cmd[$tipoDispo]['ec_cmd'];
    }


}


