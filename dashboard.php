<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header("Location: index.html");
  exit();
}

include 'config.php';

$user_id = $_SESSION['user_id'];

// Fetch incomes
$incomeQuery = $conn->prepare("SELECT * FROM income WHERE user_id = ? ORDER BY date DESC");
$incomeQuery->bind_param("i", $user_id);
$incomeQuery->execute();
$incomes = $incomeQuery->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch expenses
$expenseQuery = $conn->prepare("SELECT * FROM expenses WHERE user_id = ? ORDER BY date DESC");
$expenseQuery->bind_param("i", $user_id);
$expenseQuery->execute();
$expenses = $expenseQuery->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard - BudgetHabit</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: "Poppins", sans-serif;
    }

    body {
      background-color: #f4f4f4;
      display: flex;
      min-height: 100vh;
    }

    /* Sidebar */
    .sidebar {
      width: 220px;
      background-color: #5a67d8;
      color: white;
      display: flex;
      flex-direction: column;
      padding: 20px;
      border-radius: 0 8px 8px 0;
      height: 100vh;
      position: sticky;
      top: 0;
    }

    .sidebar .logo {
      font-size: 1.5em;
      font-weight: bold;
      margin-bottom: 30px;
    }

    .sidebar a {
      color: white;
      text-decoration: none;
      margin-bottom: 15px;
      font-weight: 500;
      padding: 10px;
      border-radius: 5px;
      transition: background 0.2s;
    }

    .sidebar a:hover {
      background-color: rgba(255, 255, 255, 0.2);
    }

    #logout-btn {
      margin-top: auto;
      background-color: #434190;
      text-align: center;
      padding: 10px;
      border-radius: 5px;
    }

    #logout-btn:hover {
      opacity: 0.9;
    }

    /* Main content */
    .main-content {
      flex: 1;
      padding: 20px;
    }

    /* Cards */
    .cards {
      display: flex;
      justify-content: space-between;
      margin-bottom: 20px;
      gap: 20px;
      flex-wrap: wrap;
    }

    .card {
      flex: 1;
      min-width: 180px;
      background-color: white;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
      text-align: center;
    }

    .card h3 {
      margin-bottom: 10px;
      color: #333;
    }

    .card p {
      font-size: 1.5em;
      font-weight: bold;
      color: #5a67d8;
    }

    /* Table */
    table {
      width: 100%;
      border-collapse: collapse;
      background-color: white;
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
      margin-bottom: 20px;
    }

    th,
    td {
      padding: 12px 15px;
      text-align: left;
      border-bottom: 1px solid #ddd;
    }

    th {
      background-color: #f4f4f4;
    }

    tr:hover {
      background-color: #f1f1f1;
    }

    /* Charts */
    .charts {
      display: flex;
      flex-wrap: wrap;
      gap: 20px;
    }

    .chart-container {
      flex: 1;
      min-width: 300px;
      background-color: white;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    }

    @media (max-width: 768px) {
      body {
        flex-direction: column;
      }

      .sidebar {
        width: 100%;
        flex-direction: row;
        flex-wrap: wrap;
        border-radius: 0 0 8px 8px;
      }

      .sidebar a {
        margin-right: 15px;
        margin-bottom: 10px;
      }

      #logout-btn {
        margin-left: auto;
      }
    }
  </style>
</head>

<body>
  <!-- Sidebar -->
  <div class="sidebar">
    <div class="logo">BudgetHabit</div>
    <a href="dashboard.php">Dashboard</a>
    <a href="add-expense.php">Add Expense</a>
    <a href="add-income.php">Add Income</a>
    <a href="reports.php">Reports</a>
    <a href="logout.php" id="logout-btn">Logout</a>
  </div>

  <!-- Main Content -->
  <div class="main-content">
    <!-- Cards -->
    <div class="cards">
      <div class="card">
        <h3>Total Income</h3>
        <p id="total-income">₱0.00</p>
      </div>
      <div class="card">
        <h3>Total Expenses</h3>
        <p id="total-expenses">₱0.00</p>
      </div>
      <div class="card">
        <h3>Balance</h3>
        <p id="balance">₱0.00</p>
      </div>
    </div>

    <!-- Transactions Table -->
    <table>
      <thead>
        <tr>
          <th>Date</th>
          <th>Description</th>
          <th>Category</th>
          <th>Amount</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $transactions = [];
        foreach ($incomes as $inc) $transactions[] = ['date' => $inc['date'], 'description' => $inc['description'], 'category' => $inc['category'], 'amount' => $inc['amount'], 'type' => 'Income'];
        foreach ($expenses as $exp) $transactions[] = ['date' => $exp['date'], 'description' => $exp['description'], 'category' => $exp['category'], 'amount' => $exp['amount'], 'type' => 'Expense'];
        usort($transactions, fn($a, $b) => strtotime($b['date']) - strtotime($a['date']));
        foreach ($transactions as $t) {
          echo "<tr>
                    <td>{$t['date']}</td>
                    <td>{$t['description']}</td>
                    <td>{$t['category']}</td>
                    <td>₱" . number_format($t['amount'], 2) . "</td>
                  </tr>";
        }
        ?>
      </tbody>
    </table>

    <!-- Charts -->
    <div class="charts">
      <div class="chart-container">
        <h3>Category Breakdown</h3>
        <canvas id="categoryChart"></canvas>
      </div>
      <div class="chart-container">
        <h3>Monthly Trends</h3>
        <canvas id="lineChart"></canvas>
      </div>
      <div class="chart-container">
        <h3>Category Totals</h3>
        <canvas id="barChart"></canvas>
      </div>
    </div>
  </div>

  <!-- JS for charts and totals -->
  <script>
    const incomes = <?php echo json_encode($incomes); ?>;
    const expenses = <?php echo json_encode($expenses); ?>;
    const transactions = [...incomes.map(inc => ({
      ...inc,
      type: "Income"
    })), ...expenses.map(exp => ({
      ...exp,
      type: "Expense"
    }))];

    // Totals
    const totalIncome = incomes.reduce((sum, inc) => sum + parseFloat(inc.amount), 0);
    const totalExpenses = expenses.reduce((sum, exp) => sum + parseFloat(exp.amount), 0);
    const balance = totalIncome - totalExpenses;

    document.getElementById("total-income").textContent = `₱${totalIncome.toFixed(2)}`;
    document.getElementById("total-expenses").textContent = `₱${totalExpenses.toFixed(2)}`;
    document.getElementById("balance").textContent = `₱${balance.toFixed(2)}`;

    // Category totals
    const categoryTotals = {};
    transactions.forEach(t => {
      categoryTotals[t.category] = (categoryTotals[t.category] || 0) + parseFloat(t.amount);
    });

    // Pie chart
    new Chart(document.getElementById("categoryChart"), {
      type: "pie",
      data: {
        labels: Object.keys(categoryTotals),
        datasets: [{
          data: Object.values(categoryTotals),
          backgroundColor: ["#4CAF50", "#FFC107", "#F44336", "#2196F3", "#9C27B0"]
        }]
      },
      options: {
        plugins: {
          tooltip: {
            callbacks: {
              label: ctx => `₱${ctx.raw.toFixed(2)}`
            }
          }
        }
      }
    });

    // Monthly trends
    const monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
    const monthlyTotals = Array(12).fill(0);
    transactions.forEach(t => {
      const month = new Date(t.date).getMonth();
      monthlyTotals[month] += t.type === "Income" ? parseFloat(t.amount) : -parseFloat(t.amount);
    });

    new Chart(document.getElementById("lineChart"), {
      type: "line",
      data: {
        labels: monthNames,
        datasets: [{
          label: "Monthly Balance",
          data: monthlyTotals,
          fill: true,
          borderColor: "#5a67d8",
          backgroundColor: "rgba(90,103,216,0.2)",
          tension: 0.3
        }]
      },
      options: {
        responsive: true
      }
    });

    // Bar chart
    new Chart(document.getElementById("barChart"), {
      type: "bar",
      data: {
        labels: Object.keys(categoryTotals),
        datasets: [{
          label: "Amount by Category",
          data: Object.values(categoryTotals),
          backgroundColor: ["#4CAF50", "#FFC107", "#F44336", "#2196F3", "#9C27B0"]
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            display: false
          }
        }
      }
    });
  </script>
</body>

</html>