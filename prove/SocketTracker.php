<?php
/***********************************************************************
 * mstracker is developed with GPL Licence 2.0
 *
 * GPL License: http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * Developed by : Mario Spada from an idea of Cyril Feraudet
 * at: https://github.com/feraudet/tracker/blob/master/trackerTK102
 *
 * Web: http://www.spadamar.com
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 *    For information : spadamar@spadamar.com
 ***********************************************************************/

/***********************************************************************
 * SETTINGS
 *********************************************************************/
// Constants -----------------------------------------------------------
/* HOST                                                               */
define("IP_ADDR",'0.0.0.0'); // "0.0.0.0" = listen all ip
define("TCP_PORT",5050); // 5050
define("DEBUG", true);
/* TRACKER                                                            */
define("POLL_TIME",60); // SET POLL TIME 20,30,60,300,600 default:60 secs
define("SPEED_CONV",1.852); //From NM mile to Km
define("DFLT_MSG","tracker");
/* DATABASE                                                           */
define("DBHOST","127.0.0.1");
define("DBUSER","your_db_user");
define("DBPASS","your_db_password");
define("DBNAME","gts");
/* OTHER SETTINGS                                                     */
define("OPENGTS",true);
define("USE_SERVER_TIME",true);
// Variables -----------------------------------------------------------
$__server_listening = true;
$sendPollTime = false;
// ---------------------------------------------------------------------
/***********************************************************************
 * INIT
 *********************************************************************/
error_reporting(E_ALL);
set_time_limit(0);
ob_implicit_flush();
declare(ticks = 1);

$pid = become_daemon();
write_pid($pid);

/* nobody/nogroup, change to your host's uid/gid of the non-priv user */
change_identity(65534, 65534);

/* handle signals */
pcntl_signal(SIGTERM, 'sig_handler');
pcntl_signal(SIGINT, 'sig_handler');
pcntl_signal(SIGCHLD, 'sig_handler');

/* set default timezone */
date_default_timezone_set('Europe/Rome');
/***********************************************************************
 * Listens for requests and forks on each connection
 *********************************************************************/
server_loop();
/***********************************************************************
 * Change the identity to a non-priv user
 * @param int $uid linux user id
 * @param int $gid linux group id
 *********************************************************************/
function change_identity( $uid, $gid ) {
    if( !posix_setgid( $gid ) ) {
        $msg = "Unable to setgid to " . $gid . "!";
        writeLog($msg,true);
        exit;
    }
    if( !posix_setuid( $uid ) ) {
        $msg = "Unable to setuid to " . $uid . "!";
        writeLog($msg,true);
        exit;
    }
}
/***********************************************************************
 * Creates a server socket and listens for incoming client connections
 * @param string $address The address to listen on
 * @param int $port The port to listen on
 *********************************************************************/
function server_loop() {
    GLOBAL $__server_listening;
    if(($sock = socket_create(AF_INET, SOCK_STREAM, 0)) < 0) {
        $msg = "failed to create socket: ".socket_strerror($sock);
        writeLog($msg,true);
        exit;
    }
    if(($ret = socket_bind($sock, IP_ADDR, TCP_PORT)) < 0) {
        $msg = "failed to bind socket: ".socket_strerror($ret);
        writeLog($msg,true);
        exit;
    }
    if( ( $ret = socket_listen( $sock, 0 ) ) < 0 ) {
        $msg = "failed to listen to socket: ".socket_strerror($ret);
        writeLog($msg,true);
        exit;
    }
    socket_set_nonblock($sock);
    writeLog("waiting for clients to connect");
    while ($__server_listening) {
        $connection = @socket_accept($sock);
        if ($connection === false) {
            usleep(100);
        } elseif ($connection > 0) {
            handle_client($sock, $connection);
        } else {
            $msg = "error: ".socket_strerror($connection);
            writeLog($msg,true);
        }
    }
}
/***********************************************************************
 * Signal handler
 * @param int $sig The signal number
 *********************************************************************/
function sig_handler($sig) {
    switch($sig) {
        case SIGTERM:
        case SIGINT:
            exit;
            break;
        case SIGCHLD:
            pcntl_waitpid(-1, $status);
            break;
    }
}
/***********************************************************************
 * Handle a new client connection
 * @param $ssock resource
 * @param $csock resource
 *********************************************************************/
function handle_client($ssock, $csock) {
    GLOBAL $__server_listening;
    $pid = pcntl_fork();
    if ($pid == -1) {
        writeLog("fork failure!",true);
        exit;
    } elseif ($pid == 0) {
        /* child process */
        writeLog("CONNECT");
        $__server_listening = false;
        socket_close($ssock);
        interact($csock);
        socket_close($csock);
        writeLog("SOCKET CLOSE");
    } else {
        socket_close($csock);
        writeLog("SOCKET CLOSE");
    }
}
/***********************************************************************
 * Talk to client
 * @param $socket resource
 *********************************************************************/
function interact($socket) {
    global $sendPollTime;
    try {
        $buf = "";
        socket_recv($socket, $buf, 2048, 0);
        writeLog("RECEIVED: ".$buf);
        $outData = array();
        $rec = trim($buf); // clean up input string
        $multiplePos = (substr($buf, 0, 2) == "##") ? false : true;
        $actualIMEI = (!$multiplePos) ? substr($buf, 8, 15) : substr($buf, 5, 15);  // returns IMEI
        if (!$multiplePos){
            $output = "LOAD". "\n";
            writeLog("SEND: LOAD");
            // Send intructions
            socket_write($socket, $output, strlen($output));
            if (FALSE === ($buf = socket_read($socket, 2048))) {
                throw new Exception("socket_read() failed: " . socket_strerror(socket_last_error($socket)));
            }
            $buf = trim($buf);
            writeLog("RECEIVED: ".$buf);
            if (empty($buf)){
                throw new Exception("Received: nothing, disconnect");
            }
            if (($sendPollTime === false)) {
                $output = "ON". "\n";
                writeLog("SEND: ON");
                socket_write($socket, $output, strlen($output));
                $output = "**,imei:".$actualIMEI."," . pollTimeString(POLL_TIME)."\n";
                socket_write($socket, $output, strlen($output));
                writeLog("SEND: ".$output);
                if (FALSE === ($buf = socket_read($socket, 2048))) {
                    $sendPollTime = false;
                    throw new Exception("socket_read() failed: " . socket_strerror(socket_last_error($msgsock)));
                }
                $buf = empty($buf) ? "NO DATA" : trim($buf);
                $sendPollTime = true;
            }
        }
        $outData = explode ( "," , $buf );
        if(count($outData)>=5){
            $outDecodedData = decodeData($outData);
            if ($outDecodedData['DATA_FL'] == "F" || $outDecodedData['MSG'] !== DFLT_MSG){
                writeLog(print_r($outDecodedData,true));
                if (OPENGTS) {
                    $res = updatePosOpenGTS($outDecodedData);
                } else {
                    $res = updatePosGpsd($outDecodedData);
                }
            }
        }
    } catch (Exception $e) {
        writeLog(" ".$e->getMessage(),true);
    }

}
/***********************************************************************
 * Become a daemon by forking and closing the parent
 *********************************************************************/
function become_daemon() {
    $pid = pcntl_fork();
    if ($pid == -1) {
        /* fork failed */
        echo "fork failure!\n";
        exit();
    } elseif ($pid) {
        /* close the parent */
        exit();
    } else {
        /* child becomes our daemon */
        posix_setsid();
        chdir('/');
        umask(0);
        return posix_getpid();
    }
}
/***********************************************************************
 * Write pid
 * @param int $pid Process ID
 *********************************************************************/
function write_pid($pid) {
    $fn = preg_replace('/\.php$/', '', __FILE__);
    $fn .= "_pid";
    $fp = fopen($fn, 'w');
    fwrite($fp, $pid);
    fclose($fp);
}
/***********************************************************************
 * Write log
 * @param string $msg Text to write
 * @param bool $is_error If is error is always written
 *********************************************************************/
function writeLog($msg,$is_error=false) {
    $msg = now()." ".$msg."\n";
    if($is_error){
        echo $msg;
    } else {
        if(DEBUG)
            echo $msg;
    }
    return true;
}
/***********************************************************************
 * Return date and time
 * *******************************************************************/
function now($secs=0){
    return date("Y-m-d H:i:s", time()+$secs);
}
/***********************************************************************
 * Check if is a valid date
 * @param $str string Date string
 * *******************************************************************/
function is_valid_date($str){
    $str = str_replace("-"," ",$str);
    $str = str_replace(":"," ",$str);
    $arrDate = explode(" ",$str);
    foreach($arrDate as $val){
        if (!is_numeric($val)){
            return false;
        }
    }
    $res = checkdate(intval($arrDate[1]),intval($arrDate[2]),intval($arrDate[0]));
    $res = $res && ($arrDate[3] >=0 && $arrDate[3] <=24);
    $res = $res && ($arrDate[4] >=0 && $arrDate[4] <=59);
    return $res;
}
/***********************************************************************
 * Compose time interval string
 * @param $sec int seconds
 * *******************************************************************/
function pollTimeString($secs){
    $secs = intval($secs);
    switch ($secs) {
        case 20:
            $res = "20s";
            break;
        case 30:
            $res = "30s";
            break;
        case 60:
            $res = "01m";
            break;
        case 300:
            $res = "05m";
            break;
        case 600:
            $res = "10m";
            break;
        default:
            $res = "01m";
    }
    return "C,".$res;
}
/***********************************************************************
 * Decode data array
 * @param $arr array Data array
 * *******************************************************************/
function decodeData($arr){
    /* *********************************************************************
    *   0 = imei:000000000000000	[imei]
    *   1 = tracker					[Msg: help me / low battery / stockade /
    * 											dt /move / speed / tracker]
    *   2 = 0809231929				[acquisition time: YYMMDDhhmm +8GMT cn]
    *   3 = 13554900601				[adminphone?]
    *   4 = F						[Data: F - full / L - low]
    *   5 = 112909.397				[Time (HHMMSS.SSS)]
    *   6 = A						[A = available?]
    *   7 = 2234.4669				[Latitude (DDMM.MMMM)]
    *   8 = N						[Lat direction: N / S]
    *   9 = 11354.3287				[Longitude (DDDMM.MMMM)]
    *  10 = E						[Lon direction: E / O]
    *  11 = 0.11					[speed Mph]
    ***********************************************************************/
    $out = array();
    $out['IMEI'] = substr($arr[0], 5, 15);
    $out['MSG'] = trim($arr[1]);
    $out['ACQUISITION_TIME'] = substr($arr[2], 0, 2)."-".
        substr($arr[2], 2, 2)."-".substr($arr[2], 4, 2).
        " ".substr($arr[2],6,2).":".substr($arr[2],8,2);
    $out['ADMINPHONE'] = trim($arr[3]);
    $out['DATA_FL'] = trim($arr[4]);
    if ($out['DATA_FL'] === "F"){
        $out['TIME'] = substr($arr[5], 0, 2).":" . substr($arr[5], 2, 2).":" . substr($arr[5], 4, 2); // skip milliseconds...
        $out['AVAILABLE'] = $arr[6]==="A" ? 1 : 0;
        $out['LAT'] = floatval(substr($arr[7], 0, 2)) +
            floatval(substr($arr[7], 2, 7)) / 60;
        $out['LAT'] = $arr[8]==="N" ? $out['LAT'] : -$out['LAT'];
        $out['LON'] = floatval(substr($arr[9], 0, 3))  +
            floatval(substr($arr[9], 3, 7)) / 60;
        $out['LON'] = $arr[10]==="E" ? $out['LON'] : -$out['LON'];
        $out['SPEED'] = floatval($arr[11]) * SPEED_CONV;
    }
    else {
        $out['TIME'] = "00:00:00";
        $out['AVAILABLE'] = 0;
        $out['LAT'] = (float) 0;
        $out['LON'] = (float) 0;
        $out['SPEED'] = (float) 0;
    }
    return $out;
}
/***********************************************************************
 * Update position on OpenGTS DB
 * @param $data array Data array
 * *******************************************************************/
function updatePosOpenGTS($data){
    $res = false;
    if (false !== ($cn = db_connect())) {
        try {
            //escape data
            clean_array($cn,$data);
            $sql = "SELECT imeiNumber FROM Device WHERE imeiNumber = '{$data['IMEI']}' AND isActive;";
            writeLog($sql);
            if ($result = $cn->query($sql)) {
                if ($result->num_rows == 1) {
                    $result->close();
                    $rawData = implode(",",$data);
                    if (USE_SERVER_TIME) {
                        $acq_time = now(-POLL_TIME);
                    } else {
                        $date = new DateTime($data['ACQUISITION_TIME']);
                        $data['ACQUISITION_TIME'] = $date->format('Y-m-d H:i:s');
                        $exactDate = substr($data['ACQUISITION_TIME'],0,10)." ".$data['TIME'];
                        $acq_time = is_valid_date($exactDate) ? $exactDate : now(-POLL_TIME);
                    }
                    $sql = <<<STR
START TRANSACTION;
	UPDATE Device SET lastValidLatitude = {$data['LAT']}, lastValidLongitude = {$data['LON']}, lastGPSTimestamp = UNIX_TIMESTAMP('{$acq_time}'), lastUpdateTime = UNIX_TIMESTAMP(NOW()) WHERE imeiNumber = RIGHT(CONCAT('000000000000000', '{$data['IMEI']}'), 15);
	SELECT @accountID := accountID, @deviceID := deviceID FROM Device WHERE imeiNumber = RIGHT(CONCAT('000000000000000', '{$data['IMEI']}'), 15);
	INSERT INTO EventData (accountID, deviceID, timestamp, statusCode, latitude, longitude, speedKPH, heading, altitude, rawData, creationTime, address)
	VALUES (@accountID, @deviceID, UNIX_TIMESTAMP('{$acq_time}'), 0, {$data['LAT']}, {$data['LON']}, {$data['SPEED']}, 0, 0, '{$rawData}', UNIX_TIMESTAMP(NOW()), '');
COMMIT;
STR;
                    writeLog($sql);
                    $res = $cn->multi_query($sql);
                    while ($cn->next_result()) {;} // flush multi_queries
                } else {
                    writeLog("Invalid IMEI or device is not active!");
                    $result->close();
                }
            }
        } catch (Exception $e) {
            writeLog(" ".$e->getMessage(),true);
        }
        db_close($cn);
    }
    return $res;
}
/***********************************************************************
 * Update position on Gpsd DB
 * @param $data array Data array
 * *******************************************************************/
function updatePosGpsd($data) {
    $res = false;
    if (false !== ($cn = db_connect())) {
        try {
            //escape data
            clean_array($cn,$data);
            $sql = "SELECT imeiNumber FROM devices WHERE imeiNumber = '{$data['IMEI']}';";
            writeLog($sql);
            if ($result = $cn->query($sql)) {
                if ($result->num_rows == 1) {
                    if (USE_SERVER_TIME) {
                        $acq_time = now(-POLL_TIME);
                    } else {
                        $date = new DateTime($data['ACQUISITION_TIME']);
                        $data['ACQUISITION_TIME'] = $date->format('Y-m-d H:i:s');
                        $exactDate = substr($data['ACQUISITION_TIME'],0,10)." ".$data['TIME'];
                        $acq_time = is_valid_date($exactDate) ? $exactDate : now(-POLL_TIME);
                    }
                    $sql = <<<STR
START TRANSACTION;
	SELECT @deviceID := devices.id FROM devices WHERE imeiNumber = '{$data['IMEI']}';
	INSERT INTO positions (msg,timestamp,acq_time,track_time,is_valid,latitude,longitude,speedKPH,course,device_id) 
	VALUES ('{$data['MSG']}',UNIX_TIMESTAMP(NOW()),'{$acq_time}','{$data['TIME']}',{$data['AVAILABLE']},{$data['LAT']},{$data['LON']},{$data['SPEED']},0,@deviceID);
COMMIT;
STR;
                    writeLog($sql);
                    $res = $cn->multi_query($sql);
                    while ($cn->next_result()) {;} // flush multi_queries
                } else {
                    writeLog("Invalid IMEI!");
                    $result->close();
                }
            }
        } catch (Exception $e) {
            writeLog(" ".$e->getMessage(),true);
        }
        db_close($cn);
    }
    return $res;
}
/***********************************************************************
 * Clean data array
 * *******************************************************************/
function clean_array($cn,&$data) {
    foreach ($data as $key => $val) {
        $data[$key] = mysqli_real_escape_string($cn,$val);
    }
}
/***********************************************************************
 * Connect to DB
 * *******************************************************************/
function db_connect() {
    $link = mysqli_connect(DBHOST, DBUSER, DBPASS, DBNAME);
    if (!$link) {
        writeLog( "Error: Unable to connect to MySQL.",true);
        return false;
    }
    return	$link;
}
/***********************************************************************
 * Disconnect from DB
 * @param $link resource mysqli connection
 * *******************************************************************/
function db_close($link) {
    return mysqli_close($link);
}
?>