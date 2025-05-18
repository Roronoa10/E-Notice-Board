<?php
session_start();
require 'db.php';

// Redirect if already logged in
if (isset($_SESSION['logged_in'])) {
    header("Location: admin_public.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Basic validation
    if (empty($username) || empty($password)) {
        $error = "Username and password are required";
    } else {
        $stmt = $conn->prepare("SELECT id, password FROM admins WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            $stmt->bind_result($id, $hashed_password);
            $stmt->fetch();

            if (password_verify($password, $hashed_password)) {
                $_SESSION['logged_in'] = true;
                $_SESSION['user_id'] = $id;
                header("Location: admin_public.php");
                exit;
            } else {
                $error = "Invalid credentials";
            }
        } else {
            $error = "Invalid credentials";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<div class="login-container">
    <div class="university-brand">
        <img src="gumsbanner.png" alt="Green University Logo">
        <link rel="stylesheet" href="login.css">
        <h2>Green University Notice Board</h2>
    </div>
    
    <h1>Admin Login</h1>
    
    <?php if ($error): ?>
        <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <form method="POST" class="login-form">
        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" name="username" id="username" required>
        </div>
        
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" name="password" id="password" required>
        </div>
        
        <button type="submit" class="submit-btn">Login</button>
       <div style="text-align: center;">
  <a href="public.php" style="display: inline-block; padding: 10px 20px; background-color: #007BFF; color: white; text-decoration: none; border-radius: 5px; font-family: Arial, sans-serif; font-size: 16px;">Notice</a>
</div>

    </form>
</div>