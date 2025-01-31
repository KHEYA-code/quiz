<?php
// Database connection
require_once 'db.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}

// Initialize or retrieve attempt and score data
if (!isset($_SESSION['quiz_attempts'])) {
    $_SESSION['quiz_attempts'] = 0;
}
if (!isset($_SESSION['best_score'])) {
    $_SESSION['best_score'] = 0;
}

$score = 0;
$total_questions = 3;
$show_result = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_quiz'])) {
    if ($_SESSION['quiz_attempts'] < 6) {
        // Correct answers for the quiz
        $answers = [
            'q1' => 'a',
            'q2' => 'a',
            'q3' => 'a',
        ];

        // Calculate the score
        foreach ($answers as $question => $correct_answer) {
            if (isset($_POST[$question]) && $_POST[$question] === $correct_answer) {
                $score++;
            }
        }

        // Store score temporarily for display
        $_SESSION['last_score'] = $score;
        $show_result = true;

        // Update the best score if necessary
        if ($score > $_SESSION['best_score']) {
            $_SESSION['best_score'] = $score;

            // Insert or update the leaderboard
            $user_id = $_SESSION['user_id'];
            $username = $_SESSION['username'];
            $quiz_name = 'general_knowledge_quiz';

            try {
                $query = "SELECT * FROM leaderboard WHERE user_id = ? AND quiz_name = ?";
                $stmt = $conn->prepare($query);
                $stmt->execute([$user_id, $quiz_name]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($result) {
                    if ($score > $result['score']) {
                        $query = "UPDATE leaderboard SET score = ? WHERE user_id = ? AND quiz_name = ?";
                        $stmt = $conn->prepare($query);
                        $stmt->execute([$score, $user_id, $quiz_name]);
                    }
                } else {
                    $query = "INSERT INTO leaderboard (user_id, username, quiz_name, score) VALUES (?, ?, ?, ?)";
                    $stmt = $conn->prepare($query);
                    $stmt->execute([$user_id, $username, $quiz_name, $score]);
                }
            } catch (PDOException $e) {
                die("Database error: " . $e->getMessage());
            }
        }
    }
}

// Handle reattempt logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reattempt'])) {
    $_SESSION['quiz_attempts']++;
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Handle return to dashboard
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['return_dashboard'])) {
    header("Location: dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>General Knowledge Quiz</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .quiz-container {
            background: #fff;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            width: 100%;
            text-align: center;
        }
        h1, h2 {
            color: #333;
        }
        .question {
            margin-bottom: 1.5rem;
            text-align: left;
        }
        .options label {
            display: block;
            margin-bottom: 0.5rem;
        }
        button {
            width: 100%;
            background-color: #007bff;
            color: white;
            padding: 0.8rem;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-top: 1rem;
        }
        button:hover {
            background-color: #0056b3;
        }
        .result {
            margin-top: 2rem;
            font-size: 1.2rem;
            font-weight: bold;
            color: #007bff;
        }
        .attempts {
            margin-top: 1rem;
            font-size: 1rem;
            color: #555;
        }
    </style>
</head>
<body>
    <div class="quiz-container">
        <h1>General Knowledge Quiz</h1>
        <div class="attempts">
            <p>Attempts: <?= $_SESSION['quiz_attempts'] ?>/6</p>
            <p>Best Score: <?= $_SESSION['best_score'] ?> out of <?= $total_questions ?></p>
        </div>

        <?php if ($show_result): ?>
            <div class="result">
                <p>You scored <?= $_SESSION['last_score'] ?> out of <?= $total_questions ?>!</p>
                <form method="post">
                    <?php if ($_SESSION['quiz_attempts'] < 6): ?>
                        <button type="submit" name="reattempt">Reattempt</button>
                    <?php endif; ?>
                    <button type="submit" name="return_dashboard">Return to Dashboard</button>
                </form>
            </div>
        <?php elseif ($_SESSION['quiz_attempts'] < 6): ?>
            <form method="post">
                <div class="question">
                    <h3>1. What is the capital of France?</h3>
                    <div class="options">
                        <label><input type="radio" name="q1" value="a" required> Paris</label>
                        <label><input type="radio" name="q1" value="b"> Rome</label>
                        <label><input type="radio" name="q1" value="c"> London</label>
                    </div>
                </div>
                <div class="question">
                    <h3>2. Who wrote the play "Romeo and Juliet"?</h3>
                    <div class="options">
                        <label><input type="radio" name="q2" value="a" required> William Shakespeare</label>
                        <label><input type="radio" name="q2" value="b"> Charles Dickens</label>
                        <label><input type="radio" name="q2" value="c"> Mark Twain</label>
                    </div>
                </div>
                <div class="question">
                    <h3>3. What is the largest planet in our solar system?</h3>
                    <div class="options">
                        <label><input type="radio" name="q3" value="a" required> Jupiter</label>
                        <label><input type="radio" name="q3" value="b"> Saturn</label>
                        <label><input type="radio" name="q3" value="c"> Mars</label>
                    </div>
                </div>
                <button type="submit" name="submit_quiz">Submit</button>
            </form>
        <?php else: ?>
            <p>You have reached the maximum number of attempts.</p>
            <form method="post">
                <button type="submit" name="return_dashboard">Return to Dashboard</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
