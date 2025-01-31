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
$quiz_completed = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_quiz'])) {
    if ($_SESSION['quiz_attempts'] < 6) {
        $_SESSION['quiz_attempts']++;
        $quiz_completed = true;

        // Correct answers for the quiz
        $answers = [
            'q1' => 'b',
            'q2' => 'a',
            'q3' => 'c',
        ];

        // Calculate the score
        foreach ($answers as $question => $correct_answer) {
            if (isset($_POST[$question]) && $_POST[$question] === $correct_answer) {
                $score++;
            }
        }

        // Update the best score
        if ($score > $_SESSION['best_score']) {
            $_SESSION['best_score'] = $score;

            // Insert or update the leaderboard table
            $user_id = $_SESSION['user_id'];
            $username = $_SESSION['username'];
            $quiz_name = 'geography_quiz';

            try {
                // Check if the user already has a score for this quiz
                $query = "SELECT * FROM leaderboard WHERE user_id = ? AND quiz_name = ?";
                $stmt = $conn->prepare($query);
                $stmt->execute([$user_id, $quiz_name]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($result) {
                    // Update the score if the new score is higher
                    $query = "UPDATE leaderboard SET score = ? WHERE user_id = ? AND quiz_name = ?";
                    $stmt = $conn->prepare($query);
                    $stmt->execute([$score, $user_id, $quiz_name]);
                } else {
                    // Insert a new record
                    $query = "INSERT INTO leaderboard (user_id, username, quiz_name, score) VALUES (?, ?, ?, ?)";
                    $stmt = $conn->prepare($query);
                    $stmt->execute([$user_id, $username, $quiz_name, $score]);
                }
            } catch (PDOException $e) {
                die("Database error: " . $e->getMessage());
            }
        }

        // Show the score in a dialog box
        echo "<script>alert('You scored $score out of $total_questions!');</script>";
    }
}

// Handle reattempt request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reattempt'])) {
    $quiz_completed = false;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Geography Quiz</title>
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
        h1 {
            color: #333;
        }
        .question {
            margin-bottom: 1.5rem;
        }
        .question h3 {
            font-size: 1.2rem;
            color: #555;
        }
        .options label {
            display: block;
            margin-bottom: 0.5rem;
        }
        button {
            display: inline-block;
            width: 45%;
            background-color: #007bff;
            color: white;
            padding: 0.8rem;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-top: 10px;
        }
        button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="quiz-container">
        <h1>Geography Quiz</h1>
        <div class="attempts">
            <p>Attempts: <?= $_SESSION['quiz_attempts'] ?>/6</p>
            <p>Best Score: <?= $_SESSION['best_score'] ?> out of <?= $total_questions ?></p>
        </div>

        <?php if (!$quiz_completed && $_SESSION['quiz_attempts'] < 6): ?>
            <form method="post">
                <div class="question">
                    <h3>1. What is the capital of France?</h3>
                    <div class="options">
                        <label><input type="radio" name="q1" value="a" required> Berlin</label>
                        <label><input type="radio" name="q1" value="b"> Paris</label>
                        <label><input type="radio" name="q1" value="c"> Madrid</label>
                    </div>
                </div>
                <div class="question">
                    <h3>2. Which is the largest continent?</h3>
                    <div class="options">
                        <label><input type="radio" name="q2" value="a" required> Asia</label>
                        <label><input type="radio" name="q2" value="b"> Africa</label>
                        <label><input type="radio" name="q2" value="c"> Europe</label>
                    </div>
                </div>
                <div class="question">
                    <h3>3. Which river is the longest in the world?</h3>
                    <div class="options">
                        <label><input type="radio" name="q3" value="a" required> Amazon River</label>
                        <label><input type="radio" name="q3" value="b"> Yangtze River</label>
                        <label><input type="radio" name="q3" value="c"> Nile River</label>
                    </div>
                </div>
                <button type="submit" name="submit_quiz">Submit</button>
            </form>
        <?php else: ?>
            <div class="result">
                <p>You have reached the maximum number of attempts.</p>
                <a href="dashboard.php"><button>Return to Dashboard</button></a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
