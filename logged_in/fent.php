<?php
session_start();
require_once '../db.php';

// Initialize variables
$order_success = false;
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order'])) {
    // Check if user is logged in
    if (!isset($_SESSION['loggedin']) || !isset($_SESSION['userid'])) {
        $error_message = "Моля, влезте в профила си, за да поръчате.";
    } else {
        $user_id = $_SESSION['userid'];
        $product_id = 3; // ID for Fent model
        $order_date = date('Y-m-d H:i:s');
        $status = 'Pending';

        try {
            // Verify the car exists
            $check_stmt = $conn->prepare("SELECT id FROM cars WHERE id = ?");
            $check_stmt->bind_param("i", $product_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows === 0) {
                $error_message = "Избраният автомобил не съществува.";
            } else {
                // Create the order
                $stmt = $conn->prepare("INSERT INTO orders (user_id, product_id, order_date, status) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("iiss", $user_id, $product_id, $order_date, $status);
                
                if ($stmt->execute()) {
                    $order_success = true;
                } else {
                    $error_message = "Грешка при обработка на поръчката. Моля, опитайте отново.";
                }
            }
            
            if (isset($check_stmt)) $check_stmt->close();
            if (isset($stmt)) $stmt->close();
        } catch (Exception $e) {
            $error_message = "Възникна грешка: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="bg">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FORCE BG | Force Fent</title>
  <link rel="stylesheet" href="../style.css">
  <style>
    .model-detail {
      max-width: 1200px;
      margin: 0 auto;
      padding: 60px 20px;
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 40px;
    }
    
    .model-image {
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 5px 25px rgba(0, 0, 0, 0.1);
    }
    
    .model-image img {
      width: 100%;
      height: auto;
      display: block;
    }
    
    .model-info h1 {
      color: var(--primary-blue);
      font-size: 2.5rem;
      margin-bottom: 20px;
    }
    
    .model-specs {
      margin: 30px 0;
    }
    
    .spec-item {
      display: flex;
      justify-content: space-between;
      padding: 15px 0;
      border-bottom: 1px solid #eee;
    }
    
    .spec-name {
      font-weight: 600;
      color: var(--primary-blue);
    }
    
    .spec-value {
      color: var(--dark-gray);
    }
    
    .price {
      font-size: 1.8rem;
      color: var(--secondary-blue);
      font-weight: 700;
      margin: 20px 0;
    }
    
    .btn-contact {
      display: inline-block;
      background: var(--primary-blue);
      color: white;
      padding: 12px 30px;
      border-radius: 30px;
      text-decoration: none;
      font-weight: 600;
      transition: all 0.3s;
      border: none;
      cursor: pointer;
      font-family: inherit;
      font-size: inherit;
    }
    
    .btn-contact:hover {
      background: var(--secondary-blue);
      transform: translateY(-3px);
      box-shadow: 0 5px 15px rgba(42, 111, 219, 0.3);
    }
    
    .alert {
      padding: 15px;
      margin-bottom: 20px;
      border-radius: 5px;
      text-align: center;
    }
    
    .alert-success {
      background-color: #dff0d8;
      color: #3c763d;
    }
    
    .alert-error {
      background-color: #f2dede;
      color: #a94442;
    }
    
    @media (max-width: 768px) {
      .model-detail {
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
        <li><a href="../index.html">Профил</a></li>
        <li><a href="../index.html#home">Начало</a></li>
        <li><a href="../index.html#about">За нас</a></li>
        <li><a href="../index.html#models">Модели</a></li>
        <li><a href="../index.html#contact">Контакти</a></li>
        <li><a href="../logout.php">Изход</a></li>
      </ul>
    </nav>
  </header>

  <main class="model-detail">
    <div class="model-image">
      <img src="../car3.jpg" alt="Fent 1">
    </div>
    
    <div class="model-info">
      <h1>Модел 3 - Force Fent</h1>
      <p>Force Fent e първа по рода си спортен електрически автомобил.Качествена е,компактна е с уникален интериор и мощен двигател Force Fent ще ви накара да карате с удоволствие на пътя.</p>
      
      <div class="model-specs">
        <div class="spec-item">
          <span class="spec-name">Двигател:</span>
          <span class="spec-value">2.0L Turbo (250 к.с.)</span>
        </div>
        <div class="spec-item">
          <span class="spec-name">Скоростна кутия:</span>
          <span class="spec-value">7-степенна автоматична</span>
        </div>
        <div class="spec-item">
          <span class="spec-name">Разход:</span>
          <span class="spec-value">6.0L/100km комбиниран</span>
        </div>
        <div class="spec-item">
          <span class="spec-name">Задвижване:</span>
          <span class="spec-value">Пълно (AWD)</span>
        </div>
        <div class="spec-item">
          <span class="spec-name">Брой места:</span>
          <span class="spec-value">5</span>
        </div>
      </div>
      
      <div class="price">Цена: 80 000 лв.</div>
      
      <?php if($order_success): ?>
        <div class="alert alert-success">
          Успешно направихте поръчка! Можете да видите статуса ѝ в <a href="profile.php">профила си</a>.
        </div>
      <?php elseif(!empty($error_message)): ?>
        <div class="alert alert-error">
          <?php echo $error_message; ?>
          <?php if(strpos($error_message, 'влезте') !== false): ?>
            <a href="../login.php">Вход</a> или <a href="../register.php">Регистрация</a>
          <?php endif; ?>
        </div>
      <?php endif; ?>
      
      <form method="post" action="">
        <button type="submit" name="order" class="btn-contact">Поръчай сега</button>
        <a href="../index.html#contact" class="btn-contact">Запитване</a>
      </form>
    </div>
  </main>

  <footer>
    <p>&copy; 2025 FORCE BG</p>
  </footer>
</body>
</html>