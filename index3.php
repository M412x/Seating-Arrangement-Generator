<?php
session_start();
ini_set('display_errors', 0);
require_once('vendor/php-excel-reader/excel_reader2.php');
require_once('vendor/SpreadsheetReader.php');
require('vendor/PHPExcel.php');
?>
<!DOCTYPE html>
<html>
<head>
  <link rel="stylesheet" href="css/bootstrap.min.css" />
  <script src="js/jquery.min.js"></script>
  <script src="js/bootstrap.min.js"></script>
  <meta charset="utf-8">
</head>
<?php
$sessionID = session_id();
// $sessionID = '4gbmrjqq10frr709u135imj52b';
$conn = mysqli_connect("localhost","root","","mydb");
if (isset($_POST["import"]))
{
  $allowedFileType = ['application/vnd.ms-excel','text/xls','text/xlsx','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
  if(in_array($_FILES["file"]["type"],$allowedFileType)){
        $targetPath = 'uploads/'.md5($sessionID).".".pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
        move_uploaded_file($_FILES['file']['tmp_name'], $targetPath);
        $Reader = new SpreadsheetReader($targetPath);
        $first = true;
        foreach ($Reader as $Row)
        {
          if($first){
            $first = false;
            continue;
          }
            $student = mysqli_real_escape_string($conn,$Row[0]);
            $lastname = mysqli_real_escape_string($conn,$Row[1]);
            $firstname = mysqli_real_escape_string($conn,$Row[2]);
            $middlename = mysqli_real_escape_string($conn,$Row[3]);
            $level = mysqli_real_escape_string($conn,$Row[4]);
            $year = mysqli_real_escape_string($conn,$Row[5]);
            $query = "insert into records(studentNumber,lastName,firstName,middleName,level,year,sessionID) values('$student','$lastname','$firstname','$middlename','$level','$year','$sessionID')";
            $result = mysqli_query($conn, $query);
            if (!empty($result)) {
              $type="success";
            } else {
              $type="danger";
              $isValid=true;
            }
        }
        $query = "DELETE FROM records WHERE studentNumber = ''";
        $conn->query($query);
  }
  else
  {
    $type="danger";
    $isValid=false;
  }
  $result = mysqli_query($conn, "SELECT DISTINCT year FROM records WHERE sessionID='$sessionID'");
  $years = array();
  foreach ($result as $row) {
    array_push($years,$row['year']);
  }
  $query = "SELECT * FROM records WHERE sessionID='$sessionID' ORDER BY level";
  $temp = mysqli_query($conn, $query);
  $records = array();
  foreach ($temp as $record) {
    array_push($records, $record);
  }
  $query = "SELECT * FROM seatcoordinate WHERE sessionID='$sessionID' ORDER BY year DESC,rand()";
  $temp = mysqli_query($conn, $query);
  $rooms = array();
  foreach ($temp as $room) {
    array_push($rooms, $room);
  }
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
    $rooms_table[$i] = array_values($rooms_table[$i]);
  }
  for ($i=0; $i < count($years); $i++) {
    foreach ($records_table[$i] as $key=>$record){
      if($record['studentNumber'] == ''){
        break;
      }
      $record['roomNumber'] = $rooms_table[$i][$key]['roomNumber'];
      $record['columnNumber'] = $rooms_table[$i][$key]['columnNumber'];
      $record['rowNumber'] = $rooms_table[$i][$key]['rowNumber'];
      $noValue = false;
      if(empty($rooms_table[$i][$key])){
        $record['roomNumber'] = '#N/A';
        $record['columnNumber'] = '#N/A';
        $record['rowNumber'] = '#N/A';
        $noValue = true;
      }
      $roomNumber = $record['roomNumber'];
      $rowNumber = $record['rowNumber'];
      $columnNumber = $record['columnNumber'];
      $id = $record['id'];
      $query = "UPDATE records SET roomNumber='$roomNumber', rowNumber='$rowNumber', columnNumber='$columnNumber' WHERE id='$id'";
      $conn->query($query);
      unset($rooms_table[$i][$key]);
      if($noValue){
        $records_table[$i][$key]['isset'] = false;
      }else{
        $records_table[$i][$key]['isset'] = true;
      }
    }
  }
  $excessSeats = array();
  $unsetStudents = array();
  for ($i=0; $i < count($years); $i++) {
    foreach ($records_table[$i] as $record) {
      if($record['isset'] == false){
        $unsetStudents[] = $record;
      }
    }
    foreach ($rooms_table[$i] as $rooms) {
      $excessSeats[] = $rooms;
    }
  }
  foreach ($excessSeats as $key=>$seat) {
    $roomNumber = $seat['roomNumber'];
    $rowNumber = $seat['rowNumber'];
    $columnNumber = $seat['columnNumber'];
    $id = $unsetStudents[$key]['id'];
    $query = "UPDATE records SET roomNumber='$roomNumber', rowNumber='$rowNumber', columnNumber='$columnNumber' WHERE id='$id'";
    $conn->query($query);
  }
  $boldCells = array('A1','C1','A3','G1','L1','A2');
  $phpExcel = new PHPExcel;
  $phpExcel->getDefaultStyle()->getFont()->setName('Calibri (Body)');
  $writer = PHPExcel_IOFactory::createWriter($phpExcel, "Excel2007");
  $rooms = mysqli_query($conn, "SELECT roomNumber, arrange FROM rooms WHERE sessionID='$sessionID'");
  foreach ($rooms as $key=>$room) {
    $roomNumber = $room['roomNumber'];
    if($key > 0){
      $sheet = $phpExcel->createSheet();
      $sheet->setTitle($roomNumber);
    }else{
      $sheet = $phpExcel->getActiveSheet();
      $phpExcel->getActiveSheet()->setTitle($roomNumber);
    }
    //row X column
    $rowNumber = explode('x',$room['arrange'])[0];
    $columnNumber = explode('x',$room['arrange'])[1];
    $sheet->getCell('A1')->setValue('Exam Room');
    $sheet->getCell('C1')->setValue($roomNumber);
    $sheet->getCell('F1')->setValue('Proctor');
    $sheet->getCell('G1')->setValue('___________________________');
    $sheet->getCell('L1')->setValue('______________');
    $sheet->getCell('K1')->setValue('Quarter');
    $sheet->getCell('A2')->setValue('_______________________________________________________________________________________________________');
    $sheet->getCell('A3')->setValue('ATTENDANCE');
    for ($i=1; $i < $columnNumber; $i++) {
      $sheet->getCell('A'.(5+(($rowNumber+2)*($i-1))))->setValue('Column');
      $sheet->getStyle('A'.(5+(($rowNumber+2)*($i-1))))->getFont()->setBold(true);
      $sheet->getCell('C'.(5+(($rowNumber+2)*($i-1))))->setValue('SEAT');
      for ($j=1; $j <= $rowNumber; $j++) {
        $result = mysqli_query($conn, "SELECT * FROM records WHERE roomNumber='$roomNumber' AND columnNumber='$i' AND rowNumber='$j' AND sessionID='$sessionID'");
        $student = array();
        foreach ($result as $r) {
          $student['firstName'] = $r['firstName'];
          $student['lastName'] = $r['lastName'];
          $student['middleName'] = $r['middleName'];
          $student['level'] = $r['level'];
          break;
        }
        // echo $student['firstName'].'<br>';
        if($student['middleName'] == ''){
          $middleName = '';
        }else{
          $middleName = $student['middleName'][0];
        }
        $sheet->getCell('A'.(5+$j+(($rowNumber+2)*($i-1))))->setValue('_______________');
        $sheet->getCell('C'.(5+$j+(($rowNumber+2)*($i-1))))->setValue($j.' ');
        $sheet->getCell('D'.(5+$j+(($rowNumber+2)*($i-1))))->setValue($i);
        if($student['firstName']!='' || $student['lastName']!='')
          $sheet->getCell('E'.(5+$j+(($rowNumber+2)*($i-1))))->setValue($student['lastName'].', '.$student['firstName'].' '.$middleName);
        $sheet->getCell('J'.(5+$j+(($rowNumber+2)*($i-1))))->setValue($student['level']);
        $sheet->getCell('K'.(5+$j+(($rowNumber+2)*($i-1))))->setValue('_______________________');
      }
    }
    // foreach ($dimension as $key=>$value){
      // $columnNumber = $value['columnNumber']-1;
      // $records = mysqli_query($conn, "SELECT * FROM records WHERE roomNumber='$roomNumber' AND columnNumber='".($columnNumber+1)."' ORDER BY columnNumber");
      //
      // $sheet->getCell('A'.(5+(($room['rowNumber']+2)*$key)))->setValue('Column');
      // $sheet->getStyle('A'.(5+(($room['rowNumber']+2)*$key)))->getFont()->setBold(true);
      // $sheet->getCell('C'.(5+(($room['rowNumber']+2)*$key)))->setValue('SEAT');
      // foreach ($records as $i=>$record) {
      //   if($record['middleName'] == ''){
      //     $middleName = '';
      //   }else{
      //     $middleName = $record['middleName'][0];
      //   }
        // $sheet->getCell('A'.(7*($columnNumber+1)-1+$i))->setValue('_______________');
        // $sheet->getCell('C'.(7*($columnNumber+1)-1+$i))->setValue(($i+1).' ');
        // $sheet->getCell('D'.(7*($columnNumber+1)-1+$i))->setValue($columnNumber+1);
        // $sheet->getCell('E'.(7*($columnNumber+1)-1+$i))->setValue($record['lastName'].', '.$record['firstName'].' '.$middleName);
        // $sheet->getCell('J'.(7*($columnNumber+1)-1+$i))->setValue($record['level']);
        // $sheet->getCell('K'.(7*($columnNumber+1)-1+$i))->setValue('_______________________');
      // }
    // }
    foreach ($boldCells as $cell) {
      $sheet->getStyle($cell)->getFont()->setBold(true);
    }
    $sheet->getColumnDimension('A')->setWidth(13);
    $sheet->getColumnDimension('B')->setWidth(2);
    $sheet->getColumnDimension('D')->setWidth(2);
    $sheet->getColumnDimension('L')->setWidth(14);
  }
  $writer->save('files/'.$sessionID.'-1.xlsx');
  $phpExcel = new PHPExcel;
  $phpExcel->getDefaultStyle()->getFont()->setName('Calibri (Body)');
  $writer = PHPExcel_IOFactory::createWriter($phpExcel, "Excel2007");
  $records = mysqli_query($conn, "SELECT * FROM records WHERE sessionID='$sessionID' ORDER BY level,lastName,firstName");
  $sheet = $phpExcel->getActiveSheet();
  $sheet->getColumnDimension('A')->setWidth(6);
  $sheet->getColumnDimension('B')->setWidth(13);
  $sheet->getColumnDimension('C')->setWidth(42);
  $sheet->getColumnDimension('D')->setWidth(0);
  $sheet->getColumnDimension('E')->setWidth(0);
  $sheet->getColumnDimension('F')->setWidth(0);
  $sheet->getCell('A1')->setValue('Id');
  $sheet->getCell('B1')->setValue('Student');
  $sheet->getCell('C1')->setValue('FullName');
  $sheet->getCell('G1')->setValue('Level');
  $sheet->getCell('H1')->setValue('Year');
  $sheet->getCell('I1')->setValue('Room');
  $sheet->getCell('J1')->setValue('Column');
  $sheet->getCell('K1')->setValue('Row');
  foreach ($records as $key=>$record) {
    if($record['middleName'] == ''){
      $middleName = '';
    }else{
      $middleName = $record['middleName'][0];
    }
    $sheet->getCell('A'.($key+2))->setValue(($key+1).' ');
    $sheet->getCell('B'.($key+2))->setValue($record['studentNumber']);
    $sheet->getCell('C'.($key+2))->setValue($record['lastName'].', '.$record['firstName'].' '.$middleName);
    $sheet->getCell('G'.($key+2))->setValue($record['level']);
    $sheet->getCell('H'.($key+2))->setValue($record['year']);
    $sheet->getCell('I'.($key+2))->setValue($record['roomNumber']);
    $sheet->getCell('J'.($key+2))->setValue($record['columnNumber']);
    $sheet->getCell('K'.($key+2))->setValue($record['rowNumber']);
  }
  $writer->save('files/'.$sessionID.'-2.xlsx');
}
?>
<body>
  <nav class="navbar navbar-expand-sm bg-dark navbar-dark fixed-top">
    <img src="favicon.ico" class="navbar-brand" width="25px">
    <font class="navbar-brand">
      Seating Arrangement Generator
    </font>
  </nav>
  <br>
  <div class="container">
    <div class="card" style="margin-top:80px">
      <div class="card-header">
        Uploader
      </div>
      <div class="card-body text-center">
        <h5 class="card-title">Import Student Record</h5>
        <p class="card-text">Supported File types [.xls, .xlsx]</p>
        <form action="" method="post" name="frmExcelImport" id="frmExcelImport" enctype="multipart/form-data">
          <input type="file" name="file" id="file" accept=".xls,.xlsx">
          <button type="submit" id="submit" name="import" class="btn btn-primary">Import</button>
        </form>
        <br>
        <?php
        if($type=='success' || (file_exists('files/'.$sessionID.'-1.xlsx') && file_exists('files/'.$sessionID.'-2.xlsx')) ){
          ?>
          <input type="button" class="btn btn-info" value="Download Report 1" onclick="document.location.href='<?php echo 'files/'.$sessionID.'-1.xlsx';?>'">
          <input type="button" class="btn btn-info" value="Download Report 2" onclick="document.location.href='<?php echo 'files/'.$sessionID.'-2.xlsx';?>'">
          <?php
        }
         ?>
      </div>
    </div>
  </div>
<?php
  if(isset($_POST["import"])){
    ?>
    <br>
    <div style="margin-left:20px; width:350px" class="alert alert-<?php echo $type?> alert-dismissible">
      <button type="button" class="close" data-dismiss="alert">&times;</button>
      <strong><?php if($type=="success") echo "Success!"; else echo "Failed!";?></strong>
      <?php if($type=="success") echo " File Uploaded Successfully."; else if($isValid) echo " Error on parsing the file."; else echo " Invalid file type.";?>
    </div>
    <?php
  }
?>
</html>
</body>
