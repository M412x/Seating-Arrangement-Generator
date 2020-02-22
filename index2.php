<?php
  @session_start();
  @ini_set('display_errors', 0);
  $conn = mysqli_connect("localhost", "root", "", "mydb");
  $session_id = session_id();
  $result = mysqli_query($conn, "SELECT * FROM rooms WHERE sessionID='$session_id'")->fetch_all(MYSQLI_ASSOC);
  mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="css/bootstrap.min.css" />
    <script src="js/jquery.min.js"></script>
    <script src="js/functions.js" charset="utf-8"></script>
    <title></title>
  </head>
  <body>
    <nav class="navbar navbar-expand-sm bg-dark navbar-dark fixed-top">
      <img src="favicon.ico" class="navbar-brand" width="25px">
      <font class="navbar-brand">
        Seating Arrangement Generator
      </font>
    </nav>
    <form class="form-group" action="vendor/addseatcoord.php" method="post">
      <div class="card-header row" style="margin-top:53px">
        <div class="col">
          <select class="custom-select col" style="background:none; border:0px">
            <?php
            $i = 0;
            foreach ($result as $row) {
              $_SESSION['rooms'][$i] = $row['roomNumber'];
              $_SESSION['arrange'][$i++] = $row['arrange'];
              echo '<option value="' . $row['roomNumber'] . '">' . $row['roomNumber'] . ' (' . $row['arrange'] . ')' . '</option>';
            }
            ?>
          </select>
        </div>
        <div class="">
          <input type="submit" name="submit" value="Submit" class="btn btn-primary">
        </div>
      </div>
    <?php
      foreach ($result as $row) {
        $dimension = explode("x", $row['arrange']);
        echo '<div class="data ' . $row['roomNumber'] . ' card-body">';
        echo '<div id="table" class=" table-responsive-md">';
        echo '<table class="table table-bordered text-center">';
        echo '<tbody>';
        echo '<tr><td></td>';
        for ($j=0; $j < $dimension[0] ; $j++) {
          echo '<th>' . ($j+1) . '</th>';
        }
        echo '</tr>';
        for ($i=0; $i < $dimension[1]; $i++) {
          echo '<tr>';
          echo '<th>' . ($i+1) . '</th>';
          for ($j=0; $j < $dimension[0] ; $j++) {
            echo '<td><input class="container" style="background:none; border:0px; text-align:center" type="text" name="' . $row['roomNumber'] . '[]"></td>';
          }
          echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
        echo '</div>';
        ?>
        <div class="form-row">
          <div class="col-md-offset-3">
            <?php echo '<button type="button" class="btn btn-success copy" onclick=copy("' . $row['roomNumber'] . '[]","' . $row['arrange'] . '")>Copy Layout</button>'; ?>
          </div>
          <div class="col-md-offset-3">
            <?php echo '<button type="button" class="btn btn-success paste" onclick=paste("' . $row['roomNumber'] . '[]") value="' . $row['arrange'] . '" disabled>Paste Layout</button>'; ?>
          </div>
        </div>
        <?php
        echo '</div>';
      }
    ?>
    </form>
  </body>
</html>
