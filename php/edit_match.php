<?php
session_start();
require_once '../helper/login-info.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) die($conn->connect_error);

// save edits to db
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $matchId  = $_POST['id'];
    $date     = $_POST['match_date'];
    $time     = $_POST['match_time'];
    $opponent = $_POST['opponent_name'];
    $type     = $_POST['match_type'];

    $updateQuery = "
        UPDATE matches
        SET Date = '$date',
            Time = '$time',
            OpponentTeam = '$opponent',
            MatchType = '$type'
        WHERE MatchId = $matchId
    ";

    if ($conn->query($updateQuery)) {
        header("Location: dashboard.php");
        exit;
    } else {
        die("Error updating match: " . $conn->error);
    }
}

// load data from db
if (!isset($_GET['id'])) {
    die("Missing match ID.");
}

$matchId = intval($_GET['id']);

$query = "SELECT * FROM matches WHERE MatchId = $matchId LIMIT 1";
$result = $conn->query($query);

if ($result->num_rows !== 1) {
    die("Match not found.");
}

$match = $result->fetch_assoc();

$date = $match['Date'];
$time = substr($match['Time'], 0, 5);
$team = $match['OpponentTeam'];
$type = $match['MatchType'];

$conn->close();
?>

<html>
  <head>
    <title>Edit Match</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  </head>
  <body>
    <form action="" method="POST" style="display:inline;">
      <input type="hidden" name="form_source" value="edit_match">
      <input type="hidden" name="id" value="<?php echo $matchId; ?>">
      <div class="container py-5">
        <div class="d-flex align-items-center justify-content-center gap-5">
          <h1 class="mb-4">Edit Match</h1>
          <img src="../assets/images/FCBarcelona.png" alt="FC Barcelona" class="rounded-circle mb-4" style="width:100px;height:100px;">
        </div>
        
        <h6>You are editing match details for: <strong><?php echo $team; ?></strong></h6><br>

        <div class="mb-3">
          <label class="form-label">Opponent Team Name</label>
          <input type="text" name="opponent_name" class="form-control" value="<?php echo $team; ?>" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Type</label>
          <input type="text" name="match_type" class="form-control" value="<?php echo $type; ?>" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Date</label>
          <input type="date" name="match_date" class="form-control" value="<?php echo $date; ?>">
        </div>

        <div class="mb-3">
          <label class="form-label">Time</label>
          <input type="time" name="match_time" class="form-control" value="<?php echo $time; ?>">
        </div>

        <div class="pt-3">
          <button type="submit" class="btn btn-outline-secondary btn-md rounded-pill px-4 me-4">Save</button>
          <a href="./dashboard.php" class="btn btn-outline-secondary btn-md rounded-pill px-4">Cancel</a>
        </div>
      </div>
    </form>
  </body>
</html>