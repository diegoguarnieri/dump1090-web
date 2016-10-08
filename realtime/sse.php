<?php
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');

function sendMsg($id , $msg) {
   echo "id: $id" . PHP_EOL;
   echo "data: {$msg} \n";

   echo PHP_EOL;
   
   ob_flush();
   flush();
}

$host    = "localhost";
$port    = "30003"; //30003
$timeout = 15;
$time    = time();
$total_packets = 0;
$heartbeat = 300; // 10 mins
$debug = true;

$sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP) or die("Unable to create socket\n");

while(!@socket_connect($sock, $host, $port)) {
   $err = socket_last_error($sock);

   if($err == 115 || $err == 114) {
      if((time() - $time) >= $timeout) {
         socket_close($sock);
         die("Connection timed out.\n");
      }
   }
   die(socket_strerror($err) . "\n");
}

sleep(1);

$id = uniqid();


while($buffer = socket_read($sock, 3000, PHP_NORMAL_READ)) {
   //pcntl_signal_dispatch();

   if($buffer != '') {
      $buffer = preg_replace("/\r\n|\r|\n/",'',$buffer);

      $line = explode(',', $buffer);

      if(is_array($line) && isset($line[4])) {
         $icao = $line[4];
         $callsign = $line[10];
         $latitude = $line[14];
         $longitude = $line[15];
         $track = $line[13];
         $altitude = $line[11];
         $ground_speed = $line[12];
         $vertical_speed = $line[16];
         $squawk = $line[17];

         if($callsign != "" ||
            $latitude != "" ||
            $longitude != "" ||
            $track != "" ||
            $altitude != "" ||
            $ground_speed != "" ||
            $vertical_speed != "" ||
            $squawk != "") {

            if($icao == "") $icao = "";
            if($callsign == "") $callsign = '';
            if($latitude == "") $latitude = '';
            if($longitude == "") $longitude = '';
            if($track == "") $track = '';
            if($altitude == "") $altitude = '';
            if($ground_speed == "") $ground_speed = '';
            if($vertical_speed == "") $vertical_speed = '';
            if($squawk == "") $squawk = '';

            $flight['icao'] = $icao;
            $flight['callsign'] = $callsign;
            $flight['latitude'] = $latitude;
            $flight['longitude'] = $longitude;
            $flight['track'] = $track;
            $flight['altitude'] = $altitude;
            $flight['ground_speed'] = $ground_speed;
            $flight['vertical_speed'] = $vertical_speed;
            $flight['squawk'] = $squawk;
            $flight['sys_time'] = date('Y-m-d H:i:s');

            sendMsg($id , json_encode($flight));
         }
      }
   }

}

/*$startedAt = time();

do {
  sendMsg($startedAt , time());
  sleep(5);

} while(true);*/
?>
