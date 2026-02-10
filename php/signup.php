<?php
session_start();

require_once '../helper/login-info.php';
require_once '../helper/sanitize.php';

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) die($conn->connect_error);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $fullName = mysql_entities_fix_string($conn, $_POST['full-name'] ?? '');
    $email    = mysql_entities_fix_string($conn, $_POST['email'] ?? '');
    $phone    = mysql_entities_fix_string($conn, $_POST['phone'] ?? '');
    $username = mysql_entities_fix_string($conn, $_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $role     = 'user';

    // check if username exists
    $checkQuery = "SELECT user_id FROM User WHERE username = '$username'";
    $result = $conn->query($checkQuery);
    if (!$result) die($conn->error);

    if ($result->num_rows > 0) {
        $error = "Username already exists.";
    } else {

        // hash the password
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        // insert into User table
        $insertUserQuery = "
            INSERT INTO User (username, email, password, role)
            VALUES ('$username', '$email', '$passwordHash', '$role')
        ";

        if (!$conn->query($insertUserQuery)) {
            $error = "Error inserting user: " . $conn->error;
        } else {
            $user_id = $conn->insert_id;

            // insert into user_details
            $insertDetailsQuery = "
                INSERT INTO User_Details (user_id, full_name, phone_number)
                VALUES ($user_id, '$fullName', '$phone')
            ";

            if (!$conn->query($insertDetailsQuery)) {
                $error = "User created but error adding details: " . $conn->error;
            } else {
                $success = "Account created successfully.";
            }
        }
    }
  header("Location: login.php");
}

?>

<!DOCTYPE html>
<html>
  <head>
    <title>Sign Up</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
  </head>
  <body>
    <div class="container py-5">
      <div class="d-flex align-items-center justify-content-center gap-5">
        <h1 class="mb-4">Create an Account</h1>
        <img src="../assets/images/FCBarcelona.png" alt="FC Barcelona" class="rounded-circle mb-4" style="width:100px;height:100px;">
      </div>

      <?php if (!empty($error)): ?>
        <div class="alert alert-danger">
          <?php echo htmlspecialchars($error); ?>
        </div>
      <?php endif; ?>

      <?php if (!empty($success)): ?>
        <div class="alert alert-success">
          <?php echo htmlspecialchars($success); ?>
        </div>
      <?php endif; ?>

      <form action="" method="POST">
        <div class="mb-3">
          <label class="form-label">Full Name</label>
          <input
            type="text"
            name="full-name"
            class="form-control border-secondary"
            required
            value="<?php echo isset($fullName) ? htmlspecialchars($fullName) : ''; ?>"
          >
        </div>
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input
            type="email"
            name="email"
            placeholder="user@domain.com"
            class="form-control border-secondary"
            required
            value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>"
          >
        </div>
        <div class="mb-3">
          <label class="form-label">Phone Number</label>
          <input
            type="tel"
            name="phone"
            placeholder="000-000-0000"
            class="form-control border-secondary"
            required
            value="<?php echo isset($phone) ? htmlspecialchars($phone) : ''; ?>"
          >
        </div>
        <div class="mb-3">
          <label class="form-label">Username</label>
          <input
            name="username"
            class="form-control border-secondary"
            required
            value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>"
          >
        </div>
        <div class="mb-3">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control border-secondary" required>
        </div>

        <button type="submit" class="btn btn-outline-secondary btn-sm rounded-pill px-3 me-4">
          Create Account
        </button>
        <a href="login.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3">Cancel</a>
      </form>
    </div>
  </body>
</html>
<?php
$conn->close();
?>
