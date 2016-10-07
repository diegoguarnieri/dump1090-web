<?php

include_once('DB.php');

//wait a few seconds to initilize dump1090
sleep(15);

$con = new DB();

//where is dump1090 or Virtual Radar Server rebroadcaster (VRS)
//port 30003 for dump1090 or 33001 for VRS
$host = "localhost";
$port = "30003";
$timeout = 15;
$time = time();
$total_packets = 0;
$heartbeat = 300; // 10 mins

$sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP) or die("Unable to create socket\n");

if(isset($sock)) {
   // let's try and connect
   echo "Connecting to dump1090...\n";
   while (!@socket_connect($sock, $host, $port)) {
      $err = socket_last_error($sock);

      if ($err == 115 || $err == 114) {
         if((time() - $time) >= $timeout) {
            socket_close($sock);
            die("Connection timed out.\n");
         }
      }
      die(socket_strerror($err) . "\n");
   }

   echo "Connected!\n";

   //wait a few seconds again
   sleep(1);

   echo "\nSCAN MODE\n\n";
   while($buffer = socket_read($sock, 3000, PHP_NORMAL_READ)) {
      //lets play nice and handle signals such as ctrl-c/kill properly
      pcntl_signal_dispatch();

      //SBS format is CSV format
      $line = explode(',', $buffer);
      if(is_array($line) && isset($line[4])) {
         $total_packets++;

         //output periodic 1 line health check
         if(($time - time()) % $heartbeat == 0) {
            if($printed_heartbeat === false) {
               $printed_heartbeat = true;
               echo "HEARTBEAT - " . date("H:i:s d-m-Y") . " - PID: " . getmypid() . " - Packets: {$total_packets} - SYSTEM OK\n";
            }
         } else {
            $printed_heartbeat = false;
         }

         //the most important fields
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

            $icao = ($icao == "") ? "null" : "'$icao'";
            $callsign = ($callsign == "") ? "null" ? "'$callsign'";
            if($latitude == "") $latitude = 'null';
            if($longitude == "") $longitude = 'null';
            if($track == "") $track = 'null';
            if($altitude == "") $altitude = 'null';
            if($ground_speed == "") $ground_speed = 'null';
            if($vertical_speed == "") $vertical_speed = 'null';
            if($squawk == "") $squawk = 'null';

            $data = date('Y-m-d H:i:s');
            $sql = "insert into dump1090 values(null,$icao,$callsign,$latitude,$longitude,$track,$altitude,$ground_speed,$vertical_speed,$squawk,'$data')";
            $res = $con->execSQL($sql);
            
            echo $data . ": " . $sql . "\n";
         }
      }
   }
}
?>

