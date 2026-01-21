<?php
session_start();
require_once '../db.php';

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || !isset($_SESSION['userid'])) {
    header("Location: ../login.php");
    exit;
}

$userId = $_SESSION['userid'];

// Handle order deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_order'])) {
    $order_id = intval($_POST['order_id']);
    
    // Verify the order belongs to the user before deleting
    $verify_stmt = $conn->prepare("SELECT id FROM orders WHERE id = ? AND user_id = ?");
    $verify_stmt->bind_param("ii", $order_id, $userId);
    $verify_stmt->execute();
    $verify_result = $verify_stmt->get_result();
    
    if ($verify_result->num_rows > 0) {
        $delete_stmt = $conn->prepare("DELETE FROM orders WHERE id = ?");
        $delete_stmt->bind_param("i", $order_id);
        $delete_stmt->execute();
        $delete_stmt->close();
        
        // Set success message that will be shown after page refresh
        $_SESSION['delete_message'] = "Поръчката беше успешно изтрита.";
    }
    
    $verify_stmt->close();
    header("Location: my_orders.php");
    exit;
}

// Fetch orders with joined car information
$stmt = $conn->prepare("
    SELECT o.id, o.order_date, o.status, 
           c.id as car_id, c.model_name, c.price, c.fuel_type
    FROM orders o
    JOIN cars c ON o.product_id = c.id
    WHERE o.user_id = ?
    ORDER BY o.order_date DESC
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <title>Моите Поръчки</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        .orders-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .orders-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .orders-table th, .orders-table td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }
        
        .orders-table th {
            background-color: var(--light-blue);
            color: var(--primary-blue);
        }
        
        .orders-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .no-orders {
            text-align: center;
            padding: 40px;
            color: var(--dark-gray);
            font-size: 1.2rem;
        }

        .status-pending {
            color: #e67e22;
            font-weight: bold;
        }

        .status-completed {
            color: #27ae60;
            font-weight: bold;
        }

        .status-cancelled {
            color: #e74c3c;
            font-weight: bold;
        }

        .btn {
            display: inline-block;
            background: var(--primary-blue);
            color: white;
            padding: 10px 20px;
            border-radius: 30px;
            text-decoration: none;
            margin-top: 20px;
        }

        .btn:hover {
            background: var(--secondary-blue);
        }

        .btn-delete {
            background: #e74c3c;
            padding: 5px 10px;
            font-size: 0.9rem;
        }

        .btn-delete:hover {
            background: #c0392b;
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

    <div class="orders-container">
        <h2>Моите Поръчки</h2>
        
        <?php if (isset($_SESSION['delete_message'])): ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['delete_message']; ?>
                <?php unset($_SESSION['delete_message']); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($result->num_rows > 0): ?>
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>Номер на поръчка</th>
                        <th>Дата</th>
                        <th>Модел</th>
                        <th>Цена</th>
                        <th>Статус</th>
                        <th>Действие</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($order = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($order['id']); ?></td>
                        <td><?php echo date('d.m.Y H:i', strtotime($order['order_date'])); ?></td>
                        <td><?php echo htmlspecialchars($order['model_name']); ?></td>
                        <td><?php echo number_format($order['price'], 2, '.', ' '); ?> лв.</td>
                        <td class="status-<?php echo strtolower($order['status']); ?>">
                            <?php echo htmlspecialchars($order['status']); ?>
                        </td>
                        <td>
                            <form method="post" action="" onsubmit="return confirm('Сигурни ли сте, че искате да изтриете тази поръчка?');">
                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                <button type="submit" name="delete_order" class="btn-contact btn-delete">Изтрий</button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-orders">
                <p>Нямате направени поръчки.</p>
                <a href="index.html#models" class="btn">Разгледайте нашите модели</a>
            </div>
        <?php endif; ?>
    </div>

    <footer>
        <p>&copy; 2025 FORCE BG. Всички права запазени.</p>
    </footer>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>