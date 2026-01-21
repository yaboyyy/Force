<?php
require_once 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['eMail']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $address = $conn->real_escape_string($_POST['address']);
    $password = $_POST['password'];
    $repPassword = $_POST['repPassword'];
    
    // Validate inputs
    $errors = [];
    
    if (empty($username)) {
        $errors[] = "Потребителското име е задължително.";
    }
    
    if (empty($email)) {
        $errors[] = "Имейл адресът е задължителен.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Невалиден имейл адрес.";
    }
    
    if (empty($password)) {
        $errors[] = "Паролата е задължителна.";
    } elseif (strlen($password) < 6) {
        $errors[] = "Паролата трябва да бъде поне 6 символа.";
    } elseif ($password !== $repPassword) {
        $errors[] = "Паролите не съвпадат.";
    }
    
    // Check if username or email already exists
    if (empty($errors)) {
        $check = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $check->bind_param("ss", $username, $email);
        $check->execute();
        $check->store_result();
        
        if ($check->num_rows > 0) {
            $errors[] = "Потребителското име или имейл адресът вече са заети.";
        }
        $check->close();
        
    }
    
    // If no errors, register the user
    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $profilePicPath = null;

if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    $filename = basename($_FILES['profile_picture']['name']);
    $targetFile = $uploadDir . uniqid() . "_" . $filename;

    if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetFile)) {
        $profilePicPath = $targetFile;
    } else {
        $errors[] = "Неуспешно качване на профилна снимка.";
    }
}

        
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, phone, address, profile_picture) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $username, $email, $hashedPassword, $phone, $address, $profilePicPath);

        
        if ($stmt->execute()) {
            $success = "Регистрацията е успешна! Можете да влезете в системата.";
        } else {
            $errors[] = "Възникна грешка при регистрацията. Моля, опитайте отново.";
        }
        
        $stmt->close();
    }
    
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="bg">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FORCE BG - Регистриране</title>
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
      <h2>Регистриране в системата</h2>
      <form action="register.php" method="post" enctype="multipart/form-data">
          <div class="form-group">
          <label for="email">Електронна поща:</label>
          <input type="email" id="eMail" name="eMail" required>
        </div>
        <div class="form-group">
          <label for="username">Потребителско име:</label>
          <input type="text" id="username" name="username" required>
        </div>
        <div class="form-group">
          <label for="password">Парола:</label>
          <input type="password" id="password" name="password" required>
        </div>
          <div class="form-group">
          <label for="repPassword">Повтори парола:</label>
          <input type="password" id="repPassword" name="repPassword" required>
          <div class="form-group">
          <label for="phone">Телефонен номер:</label>
          <input type="text" id="phone" name="phone">
          </div>

          <div class="form-group">
            <label for="address">Адрес:</label>
            <input type="text" id="address" name="address">
          </div>

<div class="form-group">
  <label for="profile_picture">Профилна снимка:</label>
  <input type="file" id="profile_picture" name="profile_picture" accept="image/*">
</div>

        </div>
        
        <?php if(!empty($errors)): ?>
        <div class="error-messages" style="color: red; margin-bottom: 15px;">
            <?php foreach($errors as $error): ?>
                <p><?php echo $error; ?></p>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        
        <?php if(isset($success)): ?>
        <div class="success-message" style="color: green; margin-bottom: 15px;">
            <?php echo $success; ?>
        </div>
        <?php endif; ?>
        
        <button type="submit" class="login-btn">Регистрирация</button>
      </form>
      <div class="login-footer">
      </div>
    </div>
  </div>

  <footer>
    <p>&copy; 2025 FORCE BG. Всички права запазени.</p>
  </footer>
</body>
</html>