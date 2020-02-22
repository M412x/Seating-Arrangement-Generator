<?php
  @session_start();
  @ini_set('display_errors', 0);
  $conn = new mysqli("localhost","root","", "mydb");
  $sql = '';
  $i = 0;
  foreach ($_POST['rooms'] as $key => $value) {
    $session_id = session_id();
    $query = "INSERT INTO rooms(roomNumber, arrange, sessionID) VALUES ('" . implode('',explode(' ', $value)) . "','" . $_POST['rows'][$i] . "x" . $_POST['columns'][$i++] . "','$session_id')" ;
    $conn->query($query);
    ?>
      <script>
        window.alert('<?$query?>');
      </script>
    <?php
  }
  $conn->close();
  header('Location: ../index2.php');
 ?>
