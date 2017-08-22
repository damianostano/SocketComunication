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
//include_once(__DIR__.'..\dao\DB.php');
//include_once(__DIR__.'\..\..\config\map_cmd.php');

class MapDispoLogic
{

    public $mapLogic = array();//tipo del dispositivo -> logica del tipo
    public $mapDispo = array();//idDispo -> tipo del dispositivo

    /**
     * MapDispoLogic constructor.
     * @param array $mapLogic
     */
    public function __construct(){
        global $db;
        $sql = "SELECT * FROM dispositivo ";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $dispositivi = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($dispositivi as $key => $dispo){
            $this->mapDispo[$dispo['id_dispo']] = $dispo['tipo'];
        }
        global $map_cmd;
        foreach ($map_cmd as $typeDispo => $configDispo){
            $this->mapLogic[$typeDispo] = new $configDispo['logic']();
        }
    }

    public function get($idDispo){
        return $this->mapLogic[$this->mapDispo[$idDispo]];
    }



}


