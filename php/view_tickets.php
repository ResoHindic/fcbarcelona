<?php
session_start();
require_once '../helper/login-info.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId      = $_SESSION['user_id'];
$displayName = $_SESSION['username'] ?? 'User';

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) die($conn->connect_error);


// load user tickets
$tickets = [];
$ticketsSql = "
    SELECT 
        t.UserId,
        t.MatchId,
        t.SeatId,
        t.PurchaseDate,
        t.Price,
        m.OpponentTeam,
        m.Date AS MatchDate,
        m.Time AS MatchTime
    FROM Ticket t
    JOIN matches m ON t.MatchId = m.MatchId
    WHERE t.UserId = $userId
    ORDER BY t.PurchaseDate DESC
";

$ticketsRes = $conn->query($ticketsSql);
if ($ticketsRes) {
    while ($row = $ticketsRes->fetch_assoc()) {
        $tickets[] = $row;
    }
} else {
    die("Error loading tickets: " . $conn->error);
}

$conn->close();
?>
<!doctype html>
<html>
  <head>
    <title>Your Tickets</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
  </head>
  <body>
    <div class="container py-5">

      <div class="d-flex align-items-center justify-content-center gap-5 mb-4">
        <h1 class="mb-0">View Your Tickets</h1>
        <img src="../assets/images/FCBarcelona.png"
             alt="FC Barcelona"
             class="rounded-circle"
             style="width:80px;height:80px;">
      </div>

      <p class="text-center text-muted mb-4">
        Tickets purchased for account: <strong><?php echo htmlspecialchars($displayName); ?></strong>
      </p>

      <div class="card bg-transparent border-secondary">
        <div class="card-body">

          <?php if (empty($tickets)): ?>
            <div class="alert alert-info text-center mb-0">
              You have not purchased any tickets yet.
            </div>
          <?php else: ?>

            <div class="table-responsive">
              <table class="table table-borderless align-middle mb-0">
                <thead>
                  <tr class="border-bottom border-secondary">
                    <th class="text-uppercase small text-secondary">Match</th>
                    <th class="text-uppercase small text-secondary">Match Date</th>
                    <th class="text-uppercase small text-secondary">Match Time</th>
                    <th class="text-uppercase small text-secondary">Amount</th>
                    <th class="text-uppercase small text-secondary">Payment Date</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($tickets as $t): ?>
                    <tr class="border-bottom border-secondary">
                      <td><?php echo htmlspecialchars($t['OpponentTeam']); ?></td>
                      <td><?php echo htmlspecialchars($t['MatchDate']); ?></td>
                      <td><?php echo htmlspecialchars($t['MatchTime']); ?></td>
                      <td>€<?php echo number_format((float)$t['Price'], 2); ?></td>
                      <td><?php echo htmlspecialchars($t['PurchaseDate']); ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>

          <?php endif; ?>

          <div class="text-center mt-4">
            <a href="dashboard.php" class="btn btn-outline-secondary rounded-pill px-4">Back to Dashboard</a>
          </div>

        </div>
      </div>
    </div>
  </body>
</html>
