<?php
  @session_start();
  @session_regenerate_id();
  @ini_set('display_errors', 0);
  $_SESSION['numberOfRooms'] = 0;
  $conn = new mysqli("localhost","root","");
  $conn->query("CREATE DATABASE myDB");
  $conn->close();

  $conn = new mysqli("localhost","root","", "mydb");
  $conn->query("CREATE TABLE gradeLevels(id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY, year varchar(100) NOT NULL, sessionID varchar(100) NOT NULL)");
  $conn->query("CREATE TABLE rooms(id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY, roomNumber varchar(100) NOT NULL, arrange varchar(100) NOT NULL, sessionID varchar(100) NOT NULL)");
  $conn->query("CREATE TABLE seatcoordinate(id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY, columnNumber INT(6) NOT NULL, rowNumber INT(6) NOT NULL, year varchar(100) NOT NULL, roomNumber varchar(100) NOT NULL, sessionID varchar(100) NOT NULL)");
  $conn->query("CREATE TABLE records(id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY, studentNumber varchar(100) NOT NULL, lastName varchar(100) NOT NULL, firstName varchar(100) NOT NULL, middleName varchar(100) NOT NULL, level varchar(100) NOT NULL, year varchar(100) NOT NULL, roomNumber varchar(100) NOT NULL, columnNumber varchar(100) NOT NULL, rowNumber varchar(100) NOT NULL, sessionID varchar(100) NOT NULL)");
  $conn->close();
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <link rel="stylesheet" href="css/bootstrap.min.css" />
    <script src="js/bootstrap.min.js"></script>
		<script src="js/jquery.min.js"></script>
    <script src="js/dynamicfield.js"></script>
    <meta charset="utf-8">
    <title></title>
  </head>
  <body>
    <nav class="navbar navbar-expand-sm bg-dark navbar-dark fixed-top">
      <img src="favicon.ico" class="navbar-brand" width="25px">
      <font class="navbar-brand">
        Seating Arrangement Generator
      </font>
    </nav>
    <br>
    <div class="container" style="margin-top:80px">
      <form class="form-group" action="index1.php" method="post">
        <div class="table-responsive">
          <h5>Number of Rooms</h5>
          <table class="table table-bordered">
            <tr>
              <td><input type="number" name="number" class="form-control" required></td>
            </tr>
          </table>
        </div>
				<div class="table-responsive">
          <h5>Grade Levels to be Included</h5>
					<table class="table table-bordered" id="dynamic_field">
						<tr>
							<td><input type="text" name="grades[]" placeholder="Grade Level" class="form-control name_list" /></td>
							<td style="width:200px"><button type="button" name="add" id="add" class="btn btn-success">Add Another Grade</button></td>
						</tr>
					</table>
					<input type="submit" name="submit" id="submit" class="btn btn-primary" value="Set" />
				</div>
			</form>
    </div>
  </body>
</html>
