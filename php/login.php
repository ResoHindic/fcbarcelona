<?php
session_start();

require_once '../helper/login-info.php';
require_once '../helper/sanitize.php';

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) die($conn->connect_error);

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $username_raw = $_POST['username'] ?? '';
  $pass_raw     = $_POST['password'] ?? '';

  // basic trim
  $username_raw = trim($username_raw);
  $pass_raw     = trim($pass_raw);

  $username = $conn->real_escape_string($username_raw);

  // select info from User table for 'username'
  $query = "SELECT user_id, username, email, password, role
            FROM `User`
            WHERE username = '$username'
            LIMIT 1";

  $result = $conn->query($query);
  if (!$result) {
      die("Query error: " . $conn->error);
  }

  if ($result->num_rows === 1) {
    $row = $result->fetch_assoc();

    $hashFromDb = $row['password'];

    if (password_verify($pass_raw, $hashFromDb)) {

      $_SESSION['user_id']  = $row['user_id'];
      $_SESSION['username'] = $row['username'];
      $_SESSION['email']    = $row['email'];
      $_SESSION['role']     = $row['role'];

      header("Location: dashboard.php");
      exit;

    } else {
      $error = "Invalid username or password (password mismatch).";
    }

  } else {
    // no row for that username
    $error = "Invalid username or password (username not found).";
  }
}
?>

<html>
  <head>
    <title>FC Barcelona Ticketing Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
  </head>

  <body>
    <div class="container py-5">
      <div class="row align-items-center justify-content-center">
        
        <div class="col-12 col-md-6 text-center mb-4 mb-md-0">
          <img src="../assets/images/FCBarcelona.png" alt="FC Barcelona" class="rounded-circle mb-4" style="width:220px;height:220px;">
          <h2 class="fw-bold">Welcome to FC Barcelona<br>Ticketing Portal</h2>
        </div>

        <div class="col-12 col-md-5">
          <div class="border border-secondary p-4 rounded">
            <h5 class="mb-3 border-bottom border-secondary pb-2">Sign In</h5>

            <?php if (!empty($error)): ?>
              <div class="alert alert-danger py-2">
                <?php echo htmlspecialchars($error); ?>
              </div>
            <?php endif; ?>

            <form action="" method="POST">
              <input type="hidden" name="form_source" value="login">

              <div class="mb-3">
                <input type="text" class="form-control border-secondary" name="username" placeholder="username" required>
              </div>

              <div class="mb-3">
                <input type="password" class="form-control border-secondary" name="password" placeholder="password" required>
              </div>

              <button type="submit" class="btn btn-primary w-100 mb-3 fw-bold">SIGN IN</button>

              <div class="mb-3 text-center">
                <a href="#" class="text-info">Forgot Password?</a>
              </div>

              <hr class="border-secondary mt-4">

              <div class="text-center mt-3">
                <p>New User</p>
                <a href="../php/signup.php" class="btn btn-outline-primary w-100 fw-bold">SIGN UP</a>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>
<?php
$conn->close();
?>
