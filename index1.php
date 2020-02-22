<?php
  @session_start();
  @ini_set('display_errors', 0);
  $conn = new mysqli("localhost","root","", "mydb");
  if(!isset($_SESSION['numberOfRooms']) || $_SESSION['numberOfRooms'] < 1 || !isset($_SESSION['gradeLevels'])){
    if($_POST['number'] < 1 || (!isset($_POST['number']) || !(array_filter($_POST['grades'])))){
      header('Location: index.php');
    }
    if(isset($_POST['number']) && isset($_POST['grades'])){
        $_SESSION['numberOfRooms'] = (int)$_POST['number'];
        $_SESSION['gradeLevels'] = array_filter($_POST['grades']);
        foreach ($_POST['grades'] as $key => $value) {
          $conn->query("INSERT INTO gradeLevels(year,sessionID) VALUES('$value','".session_id()."')");
        }
    }else if(!isset($_SESSION['numberOfRooms']) || !isset($_SESSION['gradeLevels'])){
      header('Location: index.php');
    }
  }
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="css/bootstrap.min.css" />
    <title></title>
  </head>
  <body>
    <nav class="navbar navbar-expand-sm bg-dark navbar-dark fixed-top">
      <img src="favicon.ico" class="navbar-brand" width="25px">
      <font class="navbar-brand">
        Seating Arrangement Generator
      </font>
    </nav>
    <?php
      echo '<div class="container" style="margin-top:80px">';
      echo '<form id="myform" class="form-group" action="vendor/addrooms.php" method="post">';
      echo '<table class="table table-borderless">';
      echo '<td><h4 align="center"> Rooms </h4></td>';
      echo '<td><h4 align="center"> Rows </h4></td>';
      echo '<td><h4 align="center"> Columns </h4></td>';
      for ($i = 0; $i < $_SESSION['numberOfRooms']; $i++) {
        echo '<tr>';
        echo '<td>' . '<input name="rooms[]" type="text" placeholder="Room ' . ($i+1) . '" class="form-control" required>' . '</td>';
        echo '<td>' . '<input name="rows[]" type="number" placeholder="Row" class="form-control" required>' . '</td>';
        echo '<td>' . '<input name="columns[]" type="number" placeholder="Column" class="form-control" required>' . '</td>';
        echo '</tr>';
      }?>
        </table>
        <input type="submit" name="submit" id="submit" class="btn btn-primary">
      </form>
    </div>
  </body>
</html>
