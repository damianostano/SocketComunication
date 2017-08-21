<?php
// Il file da scaricare
$file = 'index.html';

// Impostiamo dove si deve connettere
$host = "www.google.it";

// La porta a cui connettersi
$port = 80;

// Creiamo la socket (Una socket basata su IPv4, Socket Stream [ovvero un sistema di comunicazione con il controllo dell'errore] ed infine SOL_TCP gli specifica di usare il layer di comunicazione TCP
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

// Dice a php di connettersi a HOST:PORT usando la socket creata in precedenza
socket_connect($socket, $host, $port);

// Prepariamo il contenuto da inviare
$header_send = '';
$header_send .= "GET /{$file} HTTP/1.0\n";
$header_send .= "HOST: {$host}:{$port}\n";
$header_send .= "\n";

// Inviamo i dati
socket_write($socket, $header_send);

// Inzializziamo la variabile che conterra' i dati del buffer
$buffer = '';

// Legge i dati dalla sockeet a blocchi di 512 byte mettendoli dentro la variabile $tmpdata. Se socket_read restituisce FALSE, vuol dire che la comunciazione si è chiusa
while (($tmpdata = socket_read($socket, 512)) != FALSE) {

// Aggiungo i dati al buffer
    $buffer .= $tmpdata;
}

// Il protocollo HTTP 1.0 è composto da due sezioni separate da un duplice invio. La prima sezione sono le informazioni sul file ricevuto, la seconda è il contenuto
$tmp = explode("\r\n\r\n", $buffer);

// Estraggo gli headers
$headers = array_shift($tmp);

// Il contenuto poteva contenere a sua volta dei duplici invii, quindi per evitare di perdere dati o di averli malformati reimplodiamo l'array da cui abbiamo tolto gli headers
$content = implode("\r\n\r\n", $tmp);

// A questo punto mettiamo tutto dentro un file e abbiamo scritto il nostro "downloader" :)
$fp = fopen(basename($file), "wb+t");
fwrite($fp, $content);
fclose($fp);

?>