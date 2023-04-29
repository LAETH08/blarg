<?php
session_start(); // start session

require_once 'login.php';

try {
    $pdo = new PDO($attr, $user, $pass, $opts);
} catch (PDOException $e) {
    throw new PDOException($e->getMessage(), (int)$e->getCode());
}

function get_post($var) {
    return isset($_POST[$var]) ? $_POST[$var] : '';
}

$date = null; // Define $date before using it
$net_income = 0; // Define $net_income before using it
$total_expenses = 0; // Initialize $total_expenses to 0

// retrieve first and last name and total_income from session, or from form input if not set
$first_name = $_SESSION['first_name'] ?? '';
$last_name = $_SESSION['last_name'] ?? '';
$total_income = $_SESSION['total_income'] ?? '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // if the values are not set in the session, store them in the session
        if (!$first_name || !$last_name || !$total_income) {
            $first_name = $_POST["first_name"];
            $last_name = $_POST["last_name"];
            $total_income = $_POST["total_income"];

            // store first and last name and total_income in session
            $_SESSION['first_name'] = $first_name;
            $_SESSION['last_name'] = $last_name;
            $_SESSION['total_income'] = $total_income;
        }

        $expense_name = $_POST["expense_name"];
        $amount = $_POST["amount"];
        $date = $_POST["date"];

        // calculate net_income and total_expenses
        $stmt = $pdo->prepare("SELECT SUM(amount) AS total_expenses FROM expenses WHERE first_name = ? AND last_name = ?");
        $stmt->execute([$first_name, $last_name]);
        $total_expenses = $stmt->fetch(PDO::FETCH_ASSOC)['total_expenses'];
        $total_income = $_SESSION['total_income'];
        $net_income = $total_income - $total_expenses;

        // insert data into database
        $stmt = $pdo->prepare("INSERT INTO expenses (first_name, last_name, total_income, expense_name, amount, date, net_income, total_expenses) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$first_name, $last_name, $total_income, $expense_name, $amount, $date, $net_income, $total_expenses]);


        var_dump($_POST); // debug statement

        echo "Data inserted successfully";
    } catch (Exception $e) {
        // handle errors
        echo "Error: " . $e->getMessage();
    }
}


// retrieve data from the database
$sort_by = $_GET["sort-by"] ?? "date";
$order_by = "date";
if ($sort_by == "year") {
    $order_by = "YEAR(date), MONTH(date)";
} elseif ($sort_by == "month") {
    $order_by = "MONTH(date), YEAR(date)";
}
$sql = "SELECT * FROM expenses ORDER BY $order_by";
$stmt = $pdo->query($sql);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);


if (count($data) > 0) {
    $total_expenses = 0;
    foreach ($data as $row) {
        $total_expenses += $row['amount'];
    }
    $total_income = $_SESSION['total_income'];
    $net_income = $total_income - $total_expenses;
    $total_expenses = 0;
    foreach ($data as $row) {
    $total_expenses += $row['amount'];
    }
}

// close connection
$pdo = null;
?>
