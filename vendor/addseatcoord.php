<?php
  @session_start();
  @ini_set('display_errors', 0);
  $conn = new mysqli("localhost","root","", "mydb");
  $i = 0;
  $session_id = session_id();
  foreach ($_SESSION['rooms'] as $key => $room) {
    foreach ($_POST[$room] as $key => $value) {
      $conn->query("INSERT INTO seatcoordinate(columnNumber, rowNumber, year, roomNumber, sessionID) VALUES (" . intval($key/explode("x",$_SESSION['arrange'][$i])[0]+1) . "," . (fmod($key,explode("x",$_SESSION['arrange'][$i])[0])+1) . ",'" . $value . "','" . $room . "','" . $session_id . "')");
    }
    $i++;
  }
  $conn->close();
  header('Location: ../index3.php');
 ?>
