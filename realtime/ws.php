<?php

include_once('../DB/DB.php');

$con = new DB();

$sql = "select icao,
               callsign,
               latitude,
               longitude,
               track,
               altitude,
               ground_speed,
               vertical_speed,
               squawk,
               max(sys_time) sys_time,
               date_format(max(sys_time),'%Y%m%d%H%i%s') date,
               date_format(max(sys_time) + INTERVAL 10 MINUTE,'%Y%m%d%H%i%s') date_10m
          from dump1090
         where sys_time > now() - interval 1 day 
      group by icao, callsign, latitude, longitude, track, altitude, ground_speed, vertical_speed, squawk
      order by icao, sys_time, id";
$res = $con->executaSQL($sql);

$flight = null;
$i = 0;
$icao = '';
$icao_ant = '';
$qtd_flight = 0;
$flight_time = null;
while($r = $con->getFetch($res)) {
   if($icao_ant != $r['icao']) {
      $qtd_flight++;
   } else {
      if($r['date'] > $flight_time) {
         $qtd_flight++;
      }
   }

   $icao_ant = $r['icao'];
   $icao = $r['icao'] . $qtd_flight;
   
   $flight[$icao]['icao'] = $r['icao'];
   $flight[$icao]['date'] = $r['date'];

   if($r['callsign'] != null)       $flight[$icao]['callsign'] = trim($r['callsign']);
   if($r['latitude'] != null)       $flight[$icao]['latitude'] = $r['latitude'];
   if($r['longitude'] != null)      $flight[$icao]['longitude'] = $r['longitude'];
   if($r['track'] != null)          $flight[$icao]['track'] = $r['track'];
   if($r['altitude'] != null)       $flight[$icao]['altitude'] = $r['altitude'];
   if($r['ground_speed'] != null)   $flight[$icao]['ground_speed'] = $r['ground_speed'];
   if($r['vertical_speed'] != null) $flight[$icao]['vertical_speed'] = $r['vertical_speed'];
   if($r['squawk'] != null)         $flight[$icao]['squawk'] = $r['squawk'];
   if($r['sys_time'] != null)       $flight[$icao]['sys_time'] = $r['sys_time'];

   $flight_time = $r['date_10m'];
   
   $i++;
}

$now_time = date_create(date("Y-m-d H:i:s"));

$now_time_2 = date_create(date("Y-m-d H:i:s"));
date_modify($now_time_2, '-2 min');

$now_time_20 = date_create(date("Y-m-d H:i:s"));
date_modify($now_time_20, '-20 min');

foreach($flight as $icao => $f) {
   $flight_time = date_create($flight[$icao]['sys_time']);
   
   /*echo $icao . ' flight_time ';
   var_dump($flight_time);
   echo 'now_time ';
   var_dump($now_time);
   echo 'now_time_2 ';
   var_dump($now_time_2);
   echo 'now_time_20 ';
   var_dump($now_time_20);*/

   if($flight_time >= $now_time_2) {
      $flight[$icao]['alive'] = 'N';

   } else if($flight_time >= $now_time_20) {
      $flight[$icao]['alive'] = 'R';

   } else {
      $flight[$icao]['alive'] = 'O';
   }
}

usort($flight, function($a, $b) {
    return $a['date'] - $b['date'];
});

echo json_encode($flight);

?>