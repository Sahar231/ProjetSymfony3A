#!/usr/bin/env php
<?php
// Quick test data creation script

$dsn = 'mysql:host=127.0.0.1;dbname=symfony3a;charset=utf8mb4';
$user = 'root';
$password = '';

try {
    $pdo = new PDO($dsn, $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Add questions to quiz 4
    $sql = "INSERT INTO question (quiz_id, question, reply, score, type, choices) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);

    // Question 1
    $stmt->execute([
        4,
        'What is the capital of France?',
        'Paris',
        25.00,
        'qcm',
        json_encode(['Paris', 'London', 'Berlin', 'Madrid'])
    ]);
    echo "✅ Question 1 added\n";

    // Question 2
    $stmt->execute([
        4,
        'Which is a server-side programming language?',
        'PHP',
        25.00,
        'qcm',
        json_encode(['HTML', 'CSS', 'PHP', 'JavaScript'])
    ]);
    echo "✅ Question 2 added\n";

    // Verify the quiz setup
    $verify = $pdo->query("SELECT q.id, q.title, q.total_score, q.pass_score, COUNT(qu.id) as question_count, SUM(CAST(qu.score AS DECIMAL(10,2))) as calculated_score FROM quiz q LEFT JOIN question qu ON q.id = qu.quiz_id WHERE q.id = 4 GROUP BY q.id");
    $result = $verify->fetch(PDO::FETCH_ASSOC);
    
    echo "\n📊 Quiz Summary:\n";
    echo "Quiz ID: {$result['id']}\n";
    echo "Title: {$result['title']}\n";
    echo "Total Score: {$result['total_score']}\n";
    echo "Pass Score: {$result['pass_score']}%\n";
    echo "Question Count: {$result['question_count']}\n";
    echo "Calculated Total Score: {$result['calculated_score']}\n";

    echo "\n✅ Test quiz ready for testing!\n";
    echo "Access it at: https://127.0.0.1:8000/student/quiz/4/start\n";

} catch (PDOException $e) {
    die("❌ Error: " . $e->getMessage() . "\n");
}
