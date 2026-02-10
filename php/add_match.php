<?php
session_start();
require_once '../helper/login-info.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) die($conn->connect_error);

// insert new match
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $opponent = $_POST['match_name'];
    $date     = $_POST['match_date'];
    $time     = $_POST['match_time'];
    $type     = $_POST['match_type'];

    $insertQuery = "
        INSERT INTO matches (Date, Time, OpponentTeam, MatchType)
        VALUES ('$date', '$time', '$opponent', '$type')
    ";

    if ($conn->query($insertQuery)) {
        header("Location: dashboard.php");
        exit;
    } else {
        die("Error inserting match: " . $conn->error);
    }
}

$conn->close();
?>
<html>
  <head>
    <title>Add Match</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  </head>
  <body>
    <form action="" method="POST" style="display:inline;">

      <div class="container py-5">
        <div class="d-flex align-items-center justify-content-center gap-5">
          <h1 class="mb-4">Add Match</h1>
          <img src="../assets/images/FCBarcelona.png"
               alt="FC Barcelona"
               class="rounded-circle mb-4"
               style="width:100px;height:100px;">
        </div>
        <div class="mb-3">
          <label class="form-label">Opponent Team</label>
          <input type="text" name="match_name" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Match Type</label>
          <input type="text" name="match_type" class="form-control" placeholder="LaLiga, Champions League, Copa del Rey..." required>
        </div>
        <div class="mb-3">
          <label class="form-label">Date</label>
          <input type="date" name="match_date" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Time</label>
          <input type="time" name="match_time" class="form-control" required>
        </div>
        <div class="pt-3">
          <button type="submit" class="btn btn-outline-secondary btn-md rounded-pill px-4 me-4">Add Match</button>
          <a href="./dashboard.php" class="btn btn-outline-secondary btn-md rounded-pill px-4">Cancel</a>
        </div>
      </div>

    </form>
  </body>
</html>
