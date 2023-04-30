<?php
require_once 'login.php';

try {
    $pdo = new PDO($attr, $user, $pass, $opts);
} catch (PDOException $e) {
    echo "Failed to connect to database: " . $e->getMessage();
}

$first_name = $_POST['first_name'] ?? '';
$last_name = $_POST['last_name'] ?? '';
$month = $_POST['month'] ?? '';
$year = $_POST['year'] ?? '';

$expenses = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $total_income = $_POST['total_income'];
    $expense_name = $_POST['expense_name'];
    $amount = $_POST['amount'];
    $date = $_POST['date'];

    if (isset($_POST['delete'])) {
        $id = $_POST['id'];

        // Delete the expense from the database
        $stmt = $pdo->prepare("DELETE FROM expenses WHERE id = ?");
        if ($stmt->execute([$id])) {
            echo "Expense deleted successfully!";
        } else {
            echo "Failed to delete expense.";
        }
    }

    // Calculate net income and total expenses
    $net_income = $total_income - $amount;
    $total_expenses = $amount;

    // Insert data into database
    $stmt = $pdo->prepare("INSERT INTO expenses (first_name, last_name, total_income, expense_name, amount, date, net_income, total_expenses) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    if ($stmt->execute([$first_name, $last_name, $total_income, $expense_name, $amount, $date, $net_income, $total_expenses])) {
        echo "Expense added successfully!";
    } else {
        echo "Failed to add expense.";
    }
}

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
	$sort_column = $_POST['sort_column'] ?? '';
	$sort_direction = $_POST['sort_direction'] ?? 'asc';

if (!empty($sort_column)) {
    $query .= " ORDER BY $sort_column $sort_direction";
}

    $stmt = $pdo->prepare($query);
    if ($stmt->execute($params)) {
        $expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
<head>
<link rel="stylesheet" href="finalexam.css" type="text/css">
</head>
<body>
    <h2>Results</h2>
    <!-- Add the radio buttons for sorting --> 
    <form action="final.php" method="POST">
        <label for="month">Filter by Month:</label>
        <select name="month" id="month">
            <option value="">All Months</option>
            <option value="1" <?= $month === '1' ? 'selected' : '' ?>>January</option>
            <option value="2" <?= $month === '2' ? 'selected' : '' ?>>February</option>
            <option value="3" <?= $month === '3' ? 'selected' : '' ?>>March</option>
            <option value="4" <?= $month === '4' ? 'selected' : '' ?>>April</option>
            <option value="5" <?= $month === '5' ? 'selected' : '' ?>>May</option>
            <option value="6" <?= $month === '6' ? 'selected' : '' ?>>June</option>
            <option value="7" <?= $month === '7' ? 'selected' : '' ?>>July</option>
            <option value="8" <?= $month === '8' ? 'selected' : '' ?>>August</option>
            <option value="9" <?= $month === '9' ? 'selected' : '' ?>>September</option>
            <option value="10" <?= $month === '10' ? 'selected' : '' ?>>October</option>
            <option value="11" <?= $month === '11' ? 'selected' : '' ?>>November</option>
            <option value="12" <?= $month === '12' ? 'selected' : '' ?>>December</option>
        </select>
        <label for="year">Filter by Year:</label>
        <select name="year" id="year">
            <option value="">All Years</option>
            <?php foreach ($years as $y): ?>
                <option value="<?= $y ?>" <?= $year === $y ? 'selected' : '' ?>><?= $y ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Apply Filters</button>
    </form>
	<?php if (!empty($expenses)): ?>
    <table>
        <tr>
            <th>Expense Name</th>
            <th>Amount</th>
            <th>Date</th>
            <th>Action</th>
        </tr>
        <?php foreach ($expenses as $expense): ?>
            <tr>
                <td><?= $expense['expense_name'] ?></td>
                <td>$<?= $expense['amount'] ?></td>
                <td><?= date('F j, Y', strtotime($expense['date'])) ?></td>
                <td>
                    <form action="final.php" method="POST">
                        <input type="hidden" name="id" value="<?= $expense['id'] ?>">
                        <button type="submit" name="delete">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
	<form action="final.php" method="POST">
    <label for="sort_column">Sort By:</label>
    <select name="sort_column" id="sort_column">
        <option value="expense_name">Expense Name</option>
        <option value="amount">Amount</option>
        <option value="date">Date</option>
    </select>
    <label for="sort_direction">Direction:</label>
    <input type="radio" name="sort_direction" value="asc" id="asc" checked>
    <label for="asc">Ascending</label>
    <input type="radio" name="sort_direction" value="desc" id="desc">
    <label for="desc">Descending</label>
    <button type="submit">Apply Filters</button>
	</form>
	<a href="final.html">Add Another Expense</a>
	<?php else: ?>
    <p>No expenses found.</p>
</body>
<?php endif; ?>
