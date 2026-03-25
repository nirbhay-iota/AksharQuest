<?php
session_start();
header('Content-Type: application/json');

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'User not authenticated']);
    exit();
}

$dsn = 'mysql:host=localhost;dbname=crossword;charset=utf8mb4';
$db_user = 'root';
$db_password = 'Mysql@1234';
$options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];

// Get data from the frontend
$data = json_decode(file_get_contents('php://input'), true);
$level = $data['level'] ?? null;
$progress = $data['progress'] ?? null;

// Validate the input
if (!in_array($level, ['easy', 'intermediate', 'hard']) || !is_numeric($progress)) {
    echo json_encode(['success' => false, 'error' => 'Invalid data provided']);
    exit();
}

try {
    $pdo = new PDO($dsn, $db_user, $db_password, $options);
    
    // Use a dynamic column name, but ensure it's from our safe list
    $progressColumn = "progress_" . $level;

    $stmt = $pdo->prepare("UPDATE users SET {$progressColumn} = :progress WHERE id = :user_id");
    $stmt->execute([
        ':progress' => $progress,
        ':user_id' => $_SESSION['user_id']
    ]);

    // Also update the session variable so it's in sync
    $_SESSION[$progressColumn] = $progress;

    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>