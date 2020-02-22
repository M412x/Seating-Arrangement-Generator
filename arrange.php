<?php
@session_start();
@ini_set('display_errors', 0);
$conn = new mysqli("localhost","root","", "mydb");

//get distinct years
$result = mysqli_query($conn, "SELECT DISTINCT year FROM records");
$years = array();
foreach ($result as $row) {
   array_push($years,$row['year']);
}
//end of getting distinct $years

$query = "SELECT * FROM records ORDER BY level";
$temp = mysqli_query($conn, $query);
$records = array();
foreach ($temp as $record) {
  array_push($records, $record);
}

$query = "SELECT * FROM seatcoordinate ORDER BY year DESC,rand()";
$temp = mysqli_query($conn, $query);
$rooms = array();
foreach ($temp as $room) {
  array_push($rooms, $room);
}
$conn->close();
?>

<table>
<?php
$records_table = array(array(),array(),array(),array(),array(),array(),array(),array(),array(),array(),array(),array());
for ($i=0; $i < count($years); $i++) {
  for ($j=0; $j < count($records); $j++) {
    if ($years[$i] == $records[$j]['year']) {
      array_push($records_table[$i], $records[$j]);
    }
  }
}

$rooms_table = array(array(),array(),array(),array(),array(),array(),array(),array(),array(),array(),array(),array());
for ($i=0; $i < count($years); $i++) {
  for ($j=0; $j < count($rooms); $j++) {
    if ($years[$i] == $rooms[$j]['year']) {
      array_push($rooms_table[$i], $rooms[$j]);
    }
  }
}

for ($i=0; $i < count($years); $i++) {
  for ($j=0; $j < count($records_table[$i]) ; $j++) {
    $records_table[$i][$j]['roomNumber'] = $rooms_table[$i][$j]['roomNumber'];
    $records_table[$i][$j]['columnNumber'] = $rooms_table[$i][$j]['columnNumber'];
    $records_table[$i][$j]['rowNumber'] = $rooms_table[$i][$j]['rowNumber'];
    if(empty($rooms_table[$i][$j])){
      $records_table[$i][$j]['roomNumber'] = '#N/A';
      $records_table[$i][$j]['columnNumber'] = '#N/A';
      $records_table[$i][$j]['rowNumber'] = '#N/A';
    }
    unset($rooms_table[$i][$j]);
  }
}

foreach ($years as $key => $value) {
  foreach ($records_table[$key] as $record) {
  ?>
    <tr>
      <td><?php echo $record['id']; ?></td>
      <td><?php echo $record['studentNumber']; ?></td>
      <td><?php echo $record['lastName']; ?></td>
      <td><?php echo $record['firstName']; ?></td>
      <td><?php echo $record['middleName']; ?></td>
      <td><?php echo $record['level']; ?></td>
      <td><?php echo $record['year']; ?></td>
      <td><?php echo $record['roomNumber']; ?>
      <?php echo $record['rowNumber']; ?>
      <?php echo $record['columnNumber']; ?></td>
    </tr>
    <?php
    }
  }
  ?>
</table>
