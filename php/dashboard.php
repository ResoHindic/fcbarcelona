<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once '../helper/login-info.php';

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) die($conn->connect_error);

$username = $_SESSION['username'];
$role     = $_SESSION['role'];
$isAdmin  = ($role === 'admin');

$matchRowsHTML = "";

$query = "
    SELECT MatchId, Date, Time, OpponentTeam, MatchType
    FROM matches
    WHERE Date >= CURDATE()
    ORDER BY Date ASC, Time ASC
";

$result = $conn->query($query);
if ($result && $result->num_rows > 0) {
    while ($m = $result->fetch_assoc()) {

        $id    = (int)$m['MatchId'];
        $date  = htmlspecialchars($m['Date']);
        $time  = htmlspecialchars(substr($m['Time'], 0, 5));
        $team  = htmlspecialchars($m['OpponentTeam']);
        $type  = htmlspecialchars($m['MatchType']);

        // admin-only edit/delete
        $editBtn = $isAdmin 
            ? "<a href='../php/edit_match.php?id=$id' class='btn btn-outline-secondary btn-sm rounded-pill px-3'>E</a>"
            : "";

        $deleteBtn = $isAdmin
            ? "<a href='../php/delete_match.php?id=$id' class='btn btn-outline-secondary btn-sm rounded-pill px-3'
                 onclick=\"return confirm('Delete match?');\">D</a>"
            : "";

        // user-only purchase button
        $purchaseBtn = !$isAdmin
            ? "<a href='../php/available_tickets.php?id=$id' class='btn btn-outline-secondary btn-sm rounded-pill px-3'>P</a>"
            : "";

        // build row
        $matchRowsHTML .= "
            <tr class='border-bottom border-secondary'>
                <td>
                    <div class='fw-semibold'>Barcelona vs $team</div>
                    <div class='small text-secondary'>$type</div>
                </td>
                <td>$date</td>
                <td>$time</td>
                " . ($isAdmin ? "<td class='text-center'>$editBtn</td><td class='text-center'>$deleteBtn</td>"
                              : "<td class='text-center'>$purchaseBtn</td>") . "
            </tr>";
    }
} else {
    $matchRowsHTML = "
        <tr>
            <td colspan='5' class='text-center text-muted py-4'>
                No upcoming matches found.
            </td>
        </tr>";
}

$conn->close();

$addMatchButton = $isAdmin
    ? "<a href='../php/add_match.php' class='btn btn-outline-secondary rounded-pill px-5'>Add Match</a>"
    : "";
$addViewTicketsButton = !$isAdmin 
    ? "<a href='../php/view_tickets.php' class='btn btn-outline-secondary rounded-pill px-5'>View Purchased Tickets</a>" 
    : "";

?> 
<html>
<head>
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
  <div class="container py-5">

    <div class="d-flex align-items-center justify-content-center gap-5">
        <h1 class="mb-4">Welcome, <?php echo $username; ?>!</h1>
        <img src="../assets/images/FCBarcelona.png"
             alt="FC Barcelona"
             class="rounded-circle mb-4"
             style="width:100px;height:100px;">
    </div>

    <div class="card bg-transparent border-secondary">
      <div class="card-body">
        
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h4 class="mb-0">Upcoming Matches</h4>
          <?php echo $addMatchButton; ?>
          <?php echo $addViewTicketsButton; ?>
          <a href="../helper/logout.php" class="btn btn-outline-secondary rounded-pill px-5">Log Out</a>
        </div>

        <div class="table-responsive">
          <table class="table table-borderless align-middle mb-4">
            <thead>
              <tr class="border-bottom border-secondary">
                <th class="text-uppercase small text-secondary">Match</th>
                <th class="text-uppercase small text-secondary">Date</th>
                <th class="text-uppercase small text-secondary">Time</th>

                <?php if ($isAdmin): ?>
                  <th class="text-uppercase small text-secondary text-center">Edit</th>
                  <th class="text-uppercase small text-secondary text-center">Delete</th>
                <?php else:?>
                  <th class="text-uppercase small text-secondary text-center">Purchase</th>
                <?php endif; ?>

              </tr>
            </thead>
            <tbody>
              <?php echo $matchRowsHTML; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
