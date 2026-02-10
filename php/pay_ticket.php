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

$stage = 'review'; // or 'paid'

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $matchId = intval($_POST['match_id'] ?? 0);
    $seatId  = intval($_POST['seat_id'] ?? 0);

    if ($matchId <= 0 || $seatId <= 0) {
        die("Invalid match or seat.");
    }

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

    // load seat info (with price)
    $seatSql = "
        SELECT SeatID, Section, `Row`, `Number`, Category, Price
        FROM Seat
        WHERE SeatID = $seatId
        LIMIT 1
    ";

    $seatRes = $conn->query($seatSql);
    if (!$seatRes || $seatRes->num_rows !== 1) {
        die("Seat not found.");
    }
    $seat = $seatRes->fetch_assoc();

    if (isset($_POST['card_number'])) {

        $amount        = (float)$seat['Price'];
        $paymentMethod = 'Credit Card';
        $now           = date('Y-m-d H:i:s');

        // insert ticket into Ticket table
        $ticketSql = "
            INSERT INTO Ticket (UserId, MatchId, SeatId, PurchaseDate, Price)
            VALUES ($userId, $matchId, $seatId, '$now', '$amount')
        ";
        if (!$conn->query($ticketSql)) {
            die("Error inserting ticket: " . $conn->error);
        }
        // get ticket_id from last insert
        $ticketId = $conn->insert_id;

        // insert into transactions table
        $txSql = "
            INSERT INTO Transaction (UserId, TicketId, Amount, TransactionDate, PaymentMethod)
            VALUES ($userId, $ticketId, '$amount', '$now', '$paymentMethod')
        ";
        if (!$conn->query($txSql)) {
            die("Error inserting transaction: " . $conn->error);
        }

        $stage = 'paid';
    }

} else {
    die("Invalid access. Please select a seat first.");
}

$conn->close();

$matchDate = htmlspecialchars($match['Date']);
$matchTime = htmlspecialchars(substr($match['Time'], 0, 5));
$opponent  = htmlspecialchars($match['OpponentTeam']);
$matchType = htmlspecialchars($match['MatchType']);

$section   = htmlspecialchars($seat['Section']);
$row       = htmlspecialchars($seat['Row']);
$number    = htmlspecialchars($seat['Number']);
$category  = htmlspecialchars($seat['Category']);
$price     = number_format((float)$seat['Price'], 2);

?>
<html>
  <head>
    <title>Payment - FC Barcelona Ticket</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  </head>
  <body>
    <div class="container py-5 mt-4">

      <div class="d-flex align-items-center justify-content-center gap-5 mb-4">
        <div>
          <h1 class="mb-2">
            <?php echo ($stage === 'paid') ? "Payment Successful" : "Payment Details"; ?>
          </h1>
          <h5 class="mb-1">Barcelona vs <?php echo $opponent; ?></h5>
          <p class="text-muted mb-0">
            <?php echo $matchType; ?> • <?php echo $matchDate; ?> at <?php echo $matchTime; ?>
          </p>
          <p class="text-muted mb-0">
            Seat: Section <?php echo $section; ?>, Row <?php echo $row; ?>, Seat <?php echo $number; ?>
            (<?php echo $category; ?>)
          </p>
          <p class="fw-bold mt-2 mb-0">
            Total: €<?php echo $price; ?>
          </p>
        </div>
        <img src="../assets/images/FCBarcelona.png"
             alt="FC Barcelona"
             class="rounded-circle"
             style="width:100px;height:100px;">
      </div>

      <?php if ($stage === 'paid'): ?>

        <div class="alert alert-success text-center">
          Your payment was successful and your ticket has been created.
        </div>
        <div class="text-center mt-3">
          <a href="dashboard.php" class="btn btn-outline-secondary rounded-pill px-4">Back to Dashboard</a>
        </div>

      <?php else: ?>

        <form action="" method="POST" class="mx-auto" style="max-width: 480px;">
          <input type="hidden" name="match_id" value="<?php echo $matchId; ?>">
          <input type="hidden" name="seat_id" value="<?php echo $seatId; ?>">

          <div class="card border-secondary">
            <div class="card-body">
              <h4 class="mb-3">Enter Payment Information</h4>

              <div class="mb-3">
                <label class="form-label">Name on Card</label>
                <input type="text" name="card_name" class="form-control" required>
              </div>

              <div class="mb-3">
                <label class="form-label">Card Number</label>
                <input type="text" name="card_number" class="form-control" placeholder="4111 1111 1111 1111" required>
              </div>

              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">Expiry</label>
                  <input type="text" name="card_expiry" class="form-control" placeholder="MM/YY" required>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">CVV</label>
                  <input type="text" name="card_cvv" class="form-control" placeholder="123" required>
                </div>
              </div>

              <div class="text-center mt-3">
                <button type="submit" class="btn btn-outline-secondary rounded-pill px-5">
                  Pay €<?php echo $price; ?>
                </button>
              </div>
            </div>
          </div>

          <div class="text-center mt-3">
            <a href="available_tickets.php?id=<?php echo $matchId; ?>" class="btn btn-link text-secondary">Cancel</a>
          </div>
        </form>

      <?php endif; ?>

    </div>
  </body>
</html>
