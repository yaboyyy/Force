<?php
session_start();
require_once '../db.php';

// Redirect to login if not logged in
if (!isset($_SESSION['loggedin'])) {
    header("Location: ../login.php");
    exit;
}

// Get user data
$user_id = $_SESSION['userid'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Handle profile updates
$update_success = false;
$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = $conn->real_escape_string(trim($_POST['first_name']));
    $last_name = $conn->real_escape_string(trim($_POST['last_name']));
    $email = $conn->real_escape_string(trim($_POST['email']));
    $phone = $conn->real_escape_string(trim($_POST['phone']));
    $address = $conn->real_escape_string(trim($_POST['address']));
    
    // Handle profile image upload
    $profile_image = $user['profile_image'];
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = $_FILES['profile_image']['type'];
        
        if (in_array($file_type, $allowed_types)) {
            $upload_dir = 'uploads/profile_images/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_ext = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
            $new_filename = 'user_' . $user_id . '_' . time() . '.' . $file_ext;
            $destination = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $destination)) {
                // Delete old image if it exists
                if ($profile_image && file_exists($profile_image)) {
                    unlink($profile_image);
                }
                $profile_image = $destination;
            } else {
                $errors[] = "Грешка при качване на изображението.";
            }
        } else {
            $errors[] = "Само JPG, PNG и GIF файлове са позволени.";
        }
    }
    
    // Update user data in database
    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, phone = ?, address = ?, profile_image = ? WHERE id = ?");
        $stmt->bind_param("ssssssi", $first_name, $last_name, $email, $phone, $address, $profile_image, $user_id);
        
        if ($stmt->execute()) {
            $update_success = true;
            // Refresh user data
            $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
        } else {
            $errors[] = "Грешка при актуализиране на профила.";
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="bg">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FORCE BG - Профил</title>
  <link rel="stylesheet" href="../style.css">
  <style>
    .profile-container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 40px 20px;
      display: grid;
      grid-template-columns: 300px 1fr;
      gap: 40px;
    }
    
    .profile-sidebar {
      background: var(--light-blue);
      border-radius: 10px;
      padding: 30px;
      text-align: center;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }
    
    .profile-image {
      width: 150px;
      height: 150px;
      border-radius: 50%;
      object-fit: cover;
      border: 5px solid var(--white);
      margin: 0 auto 20px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }
    
    .profile-name {
      color: var(--primary-blue);
      font-size: 1.5rem;
      margin-bottom: 5px;
    }
    
    .profile-username {
      color: var(--dark-gray);
      font-size: 1rem;
      margin-bottom: 20px;
    }
    
    .profile-menu {
      list-style: none;
      padding: 0;
      margin: 20px 0;
    }
    
    .profile-menu li {
      margin-bottom: 10px;
    }
    
    .profile-menu a {
      display: block;
      padding: 10px;
      color: var(--primary-blue);
      text-decoration: none;
      border-radius: 5px;
      transition: all 0.3s;
    }
    
    .profile-menu a:hover, .profile-menu a.active {
      background: var(--primary-blue);
      color: white;
    }
    
    .profile-content {
      background: var(--white);
      border-radius: 10px;
      padding: 30px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }
    
    .profile-section-title {
      color: var(--primary-blue);
      font-size: 1.8rem;
      margin-bottom: 20px;
      padding-bottom: 10px;
      border-bottom: 2px solid var(--light-blue);
    }
    
    .form-group {
      margin-bottom: 20px;
    }
    
    .form-group label {
      display: block;
      margin-bottom: 8px;
      font-weight: 600;
      color: var(--primary-blue);
    }
    
    .form-group input, .form-group textarea {
      width: 100%;
      padding: 12px 15px;
      border: 2px solid #ddd;
      border-radius: 5px;
      font-size: 1rem;
      transition: border-color 0.3s;
    }
    
    .form-group input:focus, .form-group textarea:focus {
      border-color: var(--secondary-blue);
      outline: none;
    }
    
    .form-group textarea {
      min-height: 100px;
      resize: vertical;
    }
    
    .btn-update {
      background: var(--primary-blue);
      color: white;
      border: none;
      padding: 12px 30px;
      font-size: 1rem;
      font-weight: 600;
      border-radius: 30px;
      cursor: pointer;
      transition: all 0.3s ease;
    }
    
    .btn-update:hover {
      background: var(--secondary-blue);
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(42, 111, 219, 0.3);
    }
    
    .success-message {
      color: green;
      margin-bottom: 20px;
      padding: 10px;
      background: #e6f7e6;
      border-radius: 5px;
    }
    
    .error-message {
      color: red;
      margin-bottom: 20px;
      padding: 10px;
      background: #ffebeb;
      border-radius: 5px;
    }
    
    @media (max-width: 768px) {
      .profile-container {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>
  <header>
    <div class="logo"><img src="../logo.png">ORCE BG</div>
    <nav>
      <ul>
        <li><a href="search.html" class="search"><img src="../search.png"></a></li>
        <li><a href="index.html#home">Начало</a></li>
        <li><a href="profile.php">Профил</a></li>
        <li><a href="my_orders.php" class="active">Моите Поръчки</a></li>
        <li><a href="logout.php">Изход</a></li>
      </ul>
    </nav>
  </header>

  <main class="profile-container">
    <aside class="profile-sidebar">
      <div class="profile-image-container">
        <img src="<?php echo !empty($user['profile_image']) ? $user['profile_image'] : 'images/default-profile.png'; ?>" 
             alt="Profile Image" class="profile-image">
      </div>
      <h2 class="profile-name"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h2>
      <p class="profile-username">@<?php echo htmlspecialchars($user['username']); ?></p>
      
      <ul class="profile-menu">
        <li><a href="profile.php" class="active">Моят профил</a></li>
        <li><a href="my_orders.php">Моите поръчки</a></li>
      </ul>
    </aside>
    
    <div class="profile-content">
      <h1 class="profile-section-title">Моят профил</h1>
      
      <?php if($update_success): ?>
        <div class="success-message">Профилът ви е актуализиран успешно!</div>
      <?php endif; ?>
      
      <?php if(!empty($errors)): ?>
        <div class="error-message">
          <?php foreach($errors as $error): ?>
            <p><?php echo $error; ?></p>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
      
      <form action="profile.php" method="post" enctype="multipart/form-data">
        <div class="form-group">
          <label for="profile_image">Профилна снимка:</label>
          <input type="file" id="profile_image" name="profile_image" accept="image/*">
        </div>
        
        <div class="form-group">
          <label for="first_name">Име:</label>
          <input type="text" id="first_name" name="first_name" 
                 value="<?php echo htmlspecialchars(isset($user['first_name']) ? $user['first_name'] : ''); ?>">
        </div>
        
        <div class="form-group">
          <label for="last_name">Фамилия:</label>
          <input type="text" id="last_name" name="last_name" 
                 value="<?php echo htmlspecialchars(isset($user['last_name']) ? $user['last_name'] : ''); ?>">
        </div>
        
        <div class="form-group">
          <label for="username">Потребителско име:</label>
          <input type="text" id="username" name="username" 
                 value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
        </div>
        
        <div class="form-group">
          <label for="email">Имейл:</label>
          <input type="email" id="email" name="email" 
                 value="<?php echo htmlspecialchars($user['email']); ?>">
        </div>
        
        <div class="form-group">
          <label for="phone">Телефон:</label>
          <input type="text" id="phone" name="phone" 
                 value="<?php echo htmlspecialchars(isset($user['phone']) ? $user['phone'] : ''); ?>">
        </div>
        
        <div class="form-group">
          <label for="address">Адрес:</label>
          <textarea id="address" name="address"><?php echo htmlspecialchars(isset($user['address']) ? $user['address'] : ''); ?></textarea>
        </div>
        
        <button type="submit" class="btn-update">Актуализирай профила</button>
      </form>
    </div>
  </main>

  <footer>
    <p>&copy; 2025 FORCE BG. Всички права запазени.</p>
  </footer>
</body>
</html>