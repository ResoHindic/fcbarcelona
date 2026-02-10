<?php

session_start();
require_once '../helper/login-info.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

if (isset($_GET['id'])) {
    $matchId = intval($_GET['id']);
} else {
    die("Missing match ID.");
}

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) die($conn->connect_error);

$deleteTickets = "DELETE FROM Ticket WHERE MatchId = $matchId";
$deleteMatch = "DELETE FROM matches WHERE MatchId = $matchId";

if ($conn->query($deleteTickets) && $conn->query($deleteMatch)) {
    header("Location: dashboard.php");
    exit;
} else {
    die("Delete error: " . $conn->error);
}

?>
<!-- <html>
  <head>
    <title>Deleted Match</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  </head>
  <body>
    <input type="hidden" name="form_source" value="edit_match">
    <input type="hidden" name="id" value="<?php echo $matchId; ?>">
    <div class="container py-5">
        <div class="d-flex align-items-center justify-content-center gap-5">
        <h1 class="mb-4">Match Deleted!</h1>
        <img src="../assets/images/FCBarcelona.png" alt="FC Barcelona" class="rounded-circle mb-4" style="width:100px;height:100px;">
        </div>
        <div class="pt-3 d-flex justify-content-center">
            <a href="./dashboard.php" class="btn btn-outline-secondary btn-md rounded-pill px-4">Back to Dashboard</a>
        </div>
    </div>
  </body>
</html> -->