<?php
session_start();
require_once 'config.php'; //
  
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}


$leaderboard_sql = "
    SELECT 
        name, 
        (progress_easy + progress_intermediate + progress_hard) AS total_progress 
    FROM 
        users 
    ORDER BY 
        total_progress DESC, progress_hard DESC, progress_intermediate DESC, progress_easy DESC 
    LIMIT 10
";

$leaderboard_result = $conn->query($leaderboard_sql);

if ($leaderboard_result === false) {
    echo "Error fetching leaderboard: " . $conn->error;
    $leaderboard_data = [];
} else {
    $leaderboard_data = $leaderboard_result->fetch_all(MYSQLI_ASSOC);
}

$conn->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="img.jpg">
    <title>Dashboard - Hindi Crossword</title>
    <link rel="stylesheet" href="styleReal.css">
    </head>
<body>
    <div class="container">
        <div id="game-dashboard" class="game-container">
            <div class="game-header">
                <h1> Hindi Crossword Game</h1>
                <div class="user-info">
                    <div class="user-welcome">Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?>!</div>
                    <a href="logout.php" class="logout-btn">Logout</a>
                </div>
            </div>

            <div class="level-selection" id="levelSelection">
                <div class="level-card easy" onclick="selectLevel('easy')">
                    <div class="level-title" style="color: #2ecc71;">Easy Level</div>
                    <div class="level-progress"><?= (int)$_SESSION['progress_easy'] ?>/3 Crosswords</div>
                    <div class="level-status status-unlocked">Unlocked</div>
                </div>

                <?php $isIntermediateLocked = $_SESSION['progress_easy'] < 3; ?>
                <div class="level-card intermediate <?= $isIntermediateLocked ? 'locked' : '' ?>" onclick="<?= $isIntermediateLocked ? '' : "selectLevel('intermediate')" ?>">
                    <div class="level-title" style="color: #f39c12;">Intermediate Level</div>
                    <div class="level-progress"><?= (int)$_SESSION['progress_intermediate'] ?>/3 Crosswords</div>
                    <div class="level-status <?= $isIntermediateLocked ? 'status-locked' : 'status-unlocked' ?>"><?= $isIntermediateLocked ? 'Locked' : 'Unlocked' ?></div>
                </div>

                <?php $isHardLocked = $_SESSION['progress_intermediate'] < 3; ?>
                <div class="level-card hard <?= $isHardLocked ? 'locked' : '' ?>" onclick="<?= $isHardLocked ? '' : "selectLevel('hard')" ?>">
                    <div class="level-title" style="color: #e74c3c;">Hard Level</div>
                    <div class="level-progress"><?= (int)$_SESSION['progress_hard'] ?>/3 Crosswords</div>
                    <div class="level-status <?= $isHardLocked ? 'status-locked' : 'status-unlocked' ?>"><?= $isHardLocked ? 'Locked' : 'Unlocked' ?></div>
                </div>
            </div>

            <div class="leaderboard-section">
                <h2>🏆 Top 10 Leaderboard</h2>
                <table class="leaderboard-table">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>User Name</th>
                            <th>Crosswords Completed</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $rank = 1; 
                        foreach ($leaderboard_data as $row): 
                        ?>
                        <tr>
                            <td><?= $rank ?></td>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td><?= (int)$row['total_progress'] ?></td>
                        </tr>
                        <?php 
                        $rank++; 
                        endforeach; 
                        ?>
                        <?php if (empty($leaderboard_data)): ?>
                        <tr>
                            <td colspan="3">No users found in the leaderboard.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            </div>
    </div>
    <script>
        function selectLevel(level) {
              
            window.location.href = `game.php?level=${level}`;
        }
    </script>
</body>
</html>