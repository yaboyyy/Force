<?php
session_start();
require_once 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];

    // Prepare statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        // Verify password (assuming passwords are hashed)
        if (password_verify($password, $user['password'])) {
            $_SESSION['loggedin'] = true;
            $_SESSION['username'] = $user['username'];
            $_SESSION['userid'] = $user['id'];
            
            header("Location: logged_in/index.html");
            exit;
        } else {
            $error = "Невалидно потребителско име или парола.";
        }
    } else {
        $error = "Невалидно потребителско име или парола.";
    }
    
    $stmt->close();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="bg">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FORCE BG - Вход</title>
  <link rel="stylesheet" href="style.css">
  <style>
    /* Login-specific styles */
    .login-container {
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: calc(100vh - 120px);
      padding: 40px 20px;
      background-color: var(--light-blue);
    }
    
    .login-form {
      background: var(--white);
      padding: 40px;
      border-radius: 10px;
      box-shadow: 0 5px 25px rgba(0, 0, 0, 0.1);
      width: 100%;
      max-width: 450px;
      text-align: center;
    }
    
    .login-form h2 {
      color: var(--primary-blue);
      margin-bottom: 30px;
      font-size: 2rem;
    }
    
    .form-group {
      margin-bottom: 25px;
      text-align: left;
    }
    
    .form-group label {
      display: block;
      margin-bottom: 8px;
      font-weight: 600;
      color: var(--primary-blue);
    }
    
    .form-group input {
      width: 100%;
      padding: 12px 15px;
      border: 2px solid #ddd;
      border-radius: 5px;
      font-size: 1rem;
      transition: border-color 0.3s;
    }
    
    .form-group input:focus {
      border-color: var(--secondary-blue);
      outline: none;
    }
    
    .login-btn {
      background: var(--primary-blue);
      color: white;
      border: none;
      padding: 12px 30px;
      font-size: 1rem;
      font-weight: 600;
      border-radius: 30px;
      cursor: pointer;
      transition: all 0.3s ease;
      width: 100%;
      margin-top: 10px;
    }
    
    .login-btn:hover {
      background: var(--secondary-blue);
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(42, 111, 219, 0.3);
    }
    
    .login-footer {
      margin-top: 20px;
      font-size: 0.9rem;
    }
    
    .login-footer a {
      color: var(--secondary-blue);
      text-decoration: none;
      font-weight: 500;
    }
    
    .login-footer a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <header>
    <div class="logo"><img src="logo.png">ORCE BG</div>
    <nav>
      <ul>
        <li><a href="login.html" class="active">Вход</a></li>
        <li><a href="index.html#home">Начало</a></li>
        <li><a href="index.html#about">За нас</a></li>
        <li><a href="index.html#models">Модели</a></li>
        <li><a href="index.html#contact">Контакти</a></li>
      </ul>
    </nav>
  </header>

  <div class="login-container">
    <div class="login-form">
      <h2>Вход в системата</h2>
      <form action="login.php" method="post">
        <div class="form-group">
          <label for="username">Потребителско име:</label>
          <input type="text" id="username" name="username" required>
        </div>
        <div class="form-group">
          <label for="password">Парола:</label>
          <input type="password" id="password" name="password" required>
        </div>
        <?php if(isset($error)): ?>
        <div class="error-message" style="color: red; margin-bottom: 15px;">
            <?php echo $error; ?>
        </div>
        <?php endif; ?>
        <button type="submit" class="login-btn">Вход</button>
      </form>

      <div class="login-footer">
        <p>Нямате акаунт? <a href="register.php">Регистрирайте се</a></p>
        <!--<p><a href="forgot-password.html">Забравена парола?</a></p>-->
      </div>
    </div>
  </div>

  <footer>
    <p>&copy; 2025 FORCE BG. Всички права запазени.</p>
  </footer>
    <?php
session_start();
$valid_username = 'admin';
$valid_password = 'pass';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $username = $_POST['username'];
  $password = $_POST['password'];

  if ($username === $valid_username && $password === $valid_password) {
    $_SESSION['loggedin'] = true;
    header("Location: logged_in/index.html");
    exit;
  } else {
    echo "Невалидно потребителско име или парола.";
  }
}
?>
</body>
</html>