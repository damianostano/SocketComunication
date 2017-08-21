<?php
/**
 * Created by PhpStorm.
 * User: Client
 * Date: 10/01/2017
 * Time: 11:30
 */
define("ROOT", __DIR__);
include_once(ROOT.'\log4php\Logger.php');
include_once(ROOT.'\config\map_cmd.php');

$configurator = new LoggerConfiguratorDefault();
$config = $configurator->parse('C:\xampp\htdocs\PhpStorm\SocketComunication\config\log4php_conf.xml');
Logger::configure($config);


$sec = 1000000;     //facendo le cose precise il cliclo non era di 1 sec perchè giustamente ci sono i rallentamenti del tempo di esecuzione concreto oltre quello di attesa.
//$margine = $sec/7;  //Questo tempo è all'incirca 1/7 di sec, sottraendolo si ottengono dei cicli di 1 min teorico in circa 58 sec in condizioni di riposo, avendo ulteriori 2 sec circa a min di ulteriore mergine.


define("FRAZ_SEC", 2);  //ogni quanto si ripete un ciclo di controllo
define("KEEPALIVE_SEC", 30); //ogni quanto (in secondi) manda un segnale di broadcast per mantenere vive le connessioni con i clients
define("N_TRY", 3); //numero di tentativi di check connection falliti dopo i quali si chiude la connessione con quel particolare client forzatamente

define("SLEEP", $sec/FRAZ_SEC);// es: 1/5 di sec = 0.2 sec
define("TIME_OUT", KEEPALIVE_SEC*N_TRY); //numero di secondi dopo cui si stacca la connessione in assenza di risposta e si eliminano i comandi appesi di quel dispositivo
define("MAX_CHARS_READ", 2048);
define("MAX_QUEUE", 10);//max n° di connessioni che vengono incodate
define("SOGLIA_CMD_EXEC", 10); //numero di comandi in coda di esecuzione superata la quale ci potrebbe essere un problema

include_once('Autoload.php');

//include_once('src\Master.php');
//include_once('src\KeepAliveSingle.php');



//spl_autoload_register(function ($class_name) {
//    include "src/".$class_name.'.php';
//});