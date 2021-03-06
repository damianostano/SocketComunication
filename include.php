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

//include_once(ROOT.'\src\dao\DB.php');//è importante che sia dopo la configurazione del Logger

$sec = 1000000;     //facendo le cose precise il cliclo non era di 1 sec perchè giustamente ci sono i rallentamenti del tempo di esecuzione concreto oltre quello di attesa.
define("SECONDO", $sec);
//$margine = $sec/7;  //Questo tempo è all'incirca 1/7 di sec, sottraendolo si ottengono dei cicli di 1 min teorico in circa 58 sec in condizioni di riposo, avendo ulteriori 2 sec circa a min di ulteriore mergine.


define("FRAZ_SEC", 2);  //ogni quanto si ripete un ciclo di controllo
define("KEEPALIVE_SEC", 20); //ogni quanto (in secondi) manda un segnale di broadcast per mantenere vive le connessioni con i clients
//define("N_TRY", 5); //numero di tentativi di check connection falliti dopo i quali si chiude la connessione con quel particolare client forzatamente
define("CMD_TIME_OUT", 6); //numero di secondi massimo che si aspetta la risposta dal dispositivo
define("TIME_OUT_USER", 60); //numero di secondi massimo che si tiene aperta la connessione con gli Users

define("SLEEP", $sec/FRAZ_SEC);// es: 1/5 di sec = 0.2 sec
define("TIME_OUT_NORMAL", 40);//numero di secondi senza pong dal dispositivo dopo cui si stacca la connessione in condizioni normali
define("TIME_OUT_WAIT", 150); //numero di secondi senza pong dal dispositivo dopo cui si stacca la connessione in stato di WAIT
define("MAX_CHARS_READ", 512);
define("MAX_QUEUE", 20);//max n° di connessioni che vengono incodate
define("SOGLIA_CMD_EXEC", 10); //numero di comandi in coda di esecuzione superata la quale ci potrebbe essere un problema

define("CMD_KEEP_ALIVE", "."); //comando per mantenere aperta la connessione
define("RES_KEEP_ALIVE", "-"); //response che il dispo è ancora connesso e risponde
define("RES_DELETE", "X");     //risposta da inviare all'utente che il comando in questione non ha ricevuto risposta ed è stato cancellato
define("CMD_ESEGUITO", "V");   //da mettere sulla response per comandi eseguiti (anche se non deve essere data risposta allo user)
define("CMD_INVALID", "I");    //risposta da inviare all'utente che il comando in questione non è valido

define("CMD_WAIT", "WAIT");    //comando di wait inviato dal dispositivo per comunicare al server che non può processare le sue richieste, nemmeno i keepalive
define("CMD_READY","READY");   //comando di ready inviato dal dispositivo per comunicare al server che è tornato disponibile a processare i comandi
define("CMD_MAIL","MAIL");   //comando di ready inviato dal dispositivo per comunicare al server che è tornato disponibile a processare i comandi
define("CMD_DATI_CMPT","DATI_CMPT");//comando dal Compact per inviare i dati rilevati

define("RES_BUSY","BUSY");     //risposta da inviare all'utente che il comando in questione non può essere eseguito perchè il dispositivo è occupato

define("OK","ok");

include_once('Autoload.php');

//include_once('src\Master.php');
//include_once('src\KeepAliveSingle.php');



//spl_autoload_register(function ($class_name) {
//    include "src/".$class_name.'.php';
//});