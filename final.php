<?php
require_once 'login.php';

try {
    $pdo = new PDO($attr, $user, $pass, $opts);
} catch (PDOException $e) {
    echo "Failed to connect to database: " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
	$total_income = $_POST['total_income'];
	$expense_name = $_POST['expense_name'];
    $amount = $_POST['amount'];
    $date = $_POST['date'];
	$net_income = $_POST['net_income'];
	$total_expenses = $_POST['total_expenses'];

    $stmt = $pdo->prepare("INSERT INTO expenses (first_name, last_name, total_income, expense_name, amount, date, net_income, total_expenses) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$first_name, $last_name, $amount, $date])) {
        echo "Expense added successfully!";
    } else {
        echo "Failed to add expense.";
    }
}

$first_name = $_GET['first_name'] ?? '';
$last_name = $_GET['last_name'] ?? '';
$month = $_GET['month'] ?? '';
$year = $_GET['year'] ?? '';

$expenses = [];

if ($first_name && $last_name) {
    $query = "SELECT * FROM expenses WHERE first_name = ? AND last_name = ?";
    $params = [$first_name, $last_name];

    if ($month) {
        $query .= " AND MONTH(date) = ?";
        $params[] = $month;
    }

    if ($year) {
        $query .= " AND YEAR(date) = ?";
        $params[] = $year;
    }

    $stmt = $pdo->prepare($query);
    if ($stmt->execute($params)) {
        $expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
