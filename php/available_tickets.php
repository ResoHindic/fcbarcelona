<?php
session_start();
require_once '../helper/login-info.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) die($conn->connect_error);

// Get match id from URL
if (!isset($_GET['id'])) {
    die("Missing match ID.");
}
$matchId = $_GET['id'];

// pagination setup
$pageSize = 20;
$page = isset($_GET['page']) ? max(1, $_GET['page']) : 1;
$offset = ($page - 1) * $pageSize;

// load match info
$matchSql = "
    SELECT MatchId, Date, Time, OpponentTeam, MatchType
    FROM matches
    WHERE MatchId = $matchId
    LIMIT 1
";
$matchRes = $conn->query($matchSql);
if (!$matchRes || $matchRes->num_rows !== 1) {
    die("Match not found.");
}
$match = $matchRes->fetch_assoc();

$matchDate = htmlspecialchars($match['Date']);
$matchTime = htmlspecialchars(substr($match['Time'], 0, 5));
$opponent  = htmlspecialchars($match['OpponentTeam']);
$matchType = htmlspecialchars($match['MatchType']);

// count sets available 
$countSql = "
    SELECT COUNT(*) AS total
    FROM Seat s
    LEFT JOIN Ticket t 
        ON t.SeatId = s.SeatID AND t.MatchId = $matchId
    WHERE t.TicketId IS NULL
";
$countRes = $conn->query($countSql);
if (!$countRes) die("Error counting seats: " . $conn->error);
$totalRow = $countRes->fetch_assoc();
$totalSeats = (int)$totalRow['total'];
$totalPages = max(1, ceil($totalSeats / $pageSize));

// load available seats for this page
$seats = [];
$seatSql = "
    SELECT s.SeatID, s.Section, s.Row, s.Number, s.Category, s.Price
    FROM Seat s
    LEFT JOIN Ticket t 
        ON t.SeatId = s.SeatID AND t.MatchId = $matchId
    WHERE t.TicketId IS NULL
    ORDER BY s.Section, s.Row, s.Number
    LIMIT $pageSize OFFSET $offset
";
$seatRes = $conn->query($seatSql);
if ($seatRes) {
    while ($row = $seatRes->fetch_assoc()) {
        $seats[] = $row;
    }
} else {
    die("Error loading seats: " . $conn->error);
}

$conn->close();
?>
<html>
  <head>
    <title>Select Seat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  </head>
  <body>
    <div class="container py-5">
      <div class="d-flex align-items-center justify-content-center gap-5 mb-4">
        <div>
          <h1 class="mb-2">Select Your Seat</h1>
        </div>
        <img src="../assets/images/FCBarcelona.png"
             alt="FC Barcelona"
             class="align-items-center mb-4"
             style="width:100px;height:100px;">
      </div>

      <div class="d-flex justify-content-center mt-4 mb-4">
        <div class="text-center"><br><br>
          <img src="../assets/images/camp-nou-plattegrond.png"
               alt="Camp Nou Seating Map"
               class="rounded-3"
               style="width:500px; height:400px; object-fit: cover;">
          <p class="mt-3 text-secondary small">
            Select your preferred seating section below.
          </p>
        </div>
      </div>

      <?php if ($totalSeats === 0): ?>
        <div class="alert alert-warning">
          No available seats for this match.
        </div>
        <div class="text-center mt-3">
          <a href="./dashboard.php" class="btn btn-outline-secondary rounded-pill px-4">Back to Dashboard</a>
        </div>
      <?php else: ?>

        <form action="pay_ticket.php" method="POST">
          <input type="hidden" name="match_id" value="<?php echo $matchId; ?>">

          <div class="card bg-transparent border-secondary">
            <div class="card-body">
              <h4 class="mb-3">
                Available Seats (page <?php echo $page; ?> of <?php echo $totalPages; ?>)
              </h4>
              <h5 class="mb-1">Barcelona vs <?php echo $opponent; ?></h5>
              <p class="text-muted mb-0">
                <?php echo $matchType; ?> • <?php echo $matchDate; ?> at <?php echo $matchTime; ?>
              </p><br>
              <div class="table-responsive">
                <table class="table table-borderless align-middle mb-0">
                  <thead>
                    <tr class="border-bottom border-secondary">
                      <th class="text-uppercase small text-secondary">Select</th>
                      <th class="text-uppercase small text-secondary">Section</th>
                      <th class="text-uppercase small text-secondary">Row</th>
                      <th class="text-uppercase small text-secondary">Seat</th>
                      <th class="text-uppercase small text-secondary">Category</th>
                      <th class="text-uppercase small text-secondary">Price</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($seats as $s): ?>
                      <tr class="border-bottom border-secondary">
                        <td>
                          <input type="radio" name="seat_id"
                                 value="<?php echo (int)$s['SeatID']; ?>" required>
                        </td>
                        <td><?php echo htmlspecialchars($s['Section']); ?></td>
                        <td><?php echo htmlspecialchars($s['Row']); ?></td>
                        <td><?php echo htmlspecialchars($s['Number']); ?></td>
                        <td><?php echo htmlspecialchars($s['Category']); ?></td>
                        <td>$<?php echo number_format((float)$s['Price'], 2); ?></td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>

              <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                  <?php if ($page > 1): ?>
                    <a href="purchase_ticket.php?id=<?php echo $matchId; ?>&page=<?php echo $page - 1; ?>"
                       class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                      &laquo; Previous
                    </a>
                  <?php endif; ?>
                </div>

                <div class="text-muted small">
                  Page <?php echo $page; ?> of <?php echo $totalPages; ?>
                </div>

                <div>
                  <?php if ($page < $totalPages): ?>
                    <a href="available_tickets.php?id=<?php echo $matchId; ?>&page=<?php echo $page + 1; ?>"
                       class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                      Next &raquo;
                    </a>
                  <?php endif; ?>
                </div>
              </div>

              <div class="pt-4 d-flex justify-content-center">
                <button type="submit" class="btn btn-outline-secondary btn-md rounded-pill px-4 me-3">
                  Confirm Purchase
                </button>
                <a href="./dashboard.php" class="btn btn-outline-secondary btn-md rounded-pill px-4">
                  Cancel
                </a>
              </div>
            </div>
          </div>
        </form>
      <?php endif; ?>
    </div>
  </body>
</html>
