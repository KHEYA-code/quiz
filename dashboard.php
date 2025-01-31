<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$quiz_name = 'cartoon_quiz';
$score = isset($_POST['score']) ? (int)$_POST['score'] : 0;

try {
    // Insert or update leaderboard score
    $query = "INSERT INTO leaderboard (user_id, username, quiz_name, score) 
              VALUES (:user_id, :username, :quiz_name, :score) 
              ON DUPLICATE KEY UPDATE score = GREATEST(score, VALUES(score))";
    $stmt = $conn->prepare($query);
    $stmt->execute(['user_id' => $user_id, 'username' => $username, 'quiz_name' => $quiz_name, 'score' => $score]);

    // Fetch leaderboard data
    $query = "SELECT username, SUM(score) AS total_score 
              FROM leaderboard 
              GROUP BY username 
              ORDER BY total_score DESC 
              LIMIT 10";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $leaderboard = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaderboard - Quiz Hub</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: url('bg.png') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        .container {
            max-width: 900px;
            margin: 2rem auto;
            padding: 2rem;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 1rem;
        }
        .nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        .nav a {
            text-decoration: none;
            background-color: #007bff;
            color: white;
            padding: 0.8rem 1.5rem;
            border-radius: 5px;
            font-size: 1rem;
            transition: background-color 0.3s ease;
        }
        .nav a:hover {
            background-color: #0056b3;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 2rem 0;
        }
        table, th, td {
            border: 1px solid #ccc;
        }
        th, td {
            padding: 1rem;
            text-align: center;
        }
        th {
            background-color: #007bff;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .quiz-links {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 1.5rem;
    margin-top: 2rem;
}

.quiz-links a {
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 180px;
    text-align: center;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 10px;
    text-decoration: none;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.quiz-links a:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
}

.quiz-links img {
    width: 100%;
    height: 120px;
    object-fit: cover;
    border-radius: 8px;
}

.quiz-links span {
    margin-top: 0.5rem;
    font-size: 1.1rem;
    font-weight: bold;
    color: #333;
}

        
    </style>
</head>
<body>
    <div class="container">
        <h1>Welcome, <?= $username ?></h1>

        <div class="nav">
            <span></span>
            <a href="logout.php">Logout</a>
        </div>

        <h2>Leaderboard (All Quizzes)</h2>
        <table>
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Username</th>
                    <th>Total Score</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($leaderboard): ?>
                    <?php $rank = 1; ?>
                    <?php foreach ($leaderboard as $row): ?>
                        <tr>
                            <td><?= $rank++ ?></td>
                            <td><?= htmlspecialchars($row['username']) ?></td>
                            <td><?= $row['total_score'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3">No scores yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <h2>Quiz Categories</h2>
<div class="quiz-links">
    <a href="cartoons_quiz.php">
        <img src="images/cartoon.jpg" alt="Cartoons Quiz">
        <span>Cartoons</span>
    </a>
    <a href="gk_quiz.php">
        <img src="images/gk.jpeg" alt="General Knowledge Quiz">
        <span>General Knowledge</span>
    </a>
    <a href="science_quiz.php">
        <img src="images/science.jpeg" alt="Science Quiz">
        <span>Science</span>
    </a>
    <a href="english_quiz.php">
        <img src="images/english.jpeg" alt="English Quiz">
        <span>English</span>
    </a>
    <a href="history_quiz.php">
        <img src="images/history.jpeg" alt="History Quiz">
        <span>History</span>
    </a>
    <a href="geography_quiz.php">
        <img src="images/geography.jpeg" alt="Geography Quiz">
        <span>Geography</span>
    </a>
</div>

    </div>
</body>
</html>
