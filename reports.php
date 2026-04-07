<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header("Location: index.html");
  exit();
}

include 'config.php';

$user_id = $_SESSION['user_id'];

// Handle deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_ids'])) {
  $deleteData = $_POST['delete_ids']; // array of "id:type" like "12:income"
  $incomeIds = [];
  $expenseIds = [];

  foreach ($deleteData as $item) {
    list($id, $type) = explode(':', $item);
    if ($type === 'income') $incomeIds[] = (int)$id;
    else if ($type === 'expenses') $expenseIds[] = (int)$id;
  }

  if (!empty($incomeIds)) {
    $placeholders = implode(',', array_fill(0, count($incomeIds), '?'));
    $types = str_repeat('i', count($incomeIds)) . 'i';
    $incomeIds[] = $user_id;
    $stmt = $conn->prepare("DELETE FROM income WHERE id IN ($placeholders) AND user_id = ?");
    $stmt->bind_param($types, ...$incomeIds);
    $stmt->execute();
    $stmt->close();
  }

  if (!empty($expenseIds)) {
    $placeholders = implode(',', array_fill(0, count($expenseIds), '?'));
    $types = str_repeat('i', count($expenseIds)) . 'i';
    $expenseIds[] = $user_id;
    $stmt = $conn->prepare("DELETE FROM expenses WHERE id IN ($placeholders) AND user_id = ?");
    $stmt->bind_param($types, ...$expenseIds);
    $stmt->execute();
    $stmt->close();
  }

  header("Location: reports.php");
  exit();
}

// Fetch incomes
$stmt = $conn->prepare("SELECT * FROM income WHERE user_id = ? ORDER BY date DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$incomes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch expenses
$stmt = $conn->prepare("SELECT * FROM expenses WHERE user_id = ? ORDER BY date DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$expenses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reports - BudgetHabit</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: "Poppins", sans-serif;
    }

    body {
      background: #f4f4f4;
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

    h2 {
      color: #5a67d8;
      margin-bottom: 20px;
      text-align: center;
    }

    .filters {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 20px;
      flex-wrap: wrap;
      margin-bottom: 20px;
    }

    .filter-inputs {
      display: flex;
      gap: 15px;
      flex-wrap: wrap;
      align-items: center;
    }

    .filters input,
    .filters select {
      padding: 6px;
      border-radius: 5px;
      border: 1px solid #ccc;
    }

    #editBtn {
      background: #5a67d8;
      color: white;
      padding: 8px 14px;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-weight: 600;
    }

    #deleteSelected {
      background: #e53e3e;
      color: white;
      padding: 8px 14px;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-weight: 600;
      display: none;
    }

    /* Totals */
    .totals {
      display: flex;
      justify-content: space-between;
      font-weight: bold;
      margin-bottom: 20px;
    }

    /* Table */
    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 30px;
      background: white;
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    }

    table th,
    table td {
      border: 1px solid #ccc;
      padding: 10px;
      text-align: left;
    }

    table th {
      background-color: #5a67d8;
      color: white;
    }

    .checkbox-col {
      display: none;
      text-align: center;
    }
  </style>
</head>

<body>
  <div class="sidebar">
    <div class="logo">BudgetHabit</div>
    <a href="dashboard.php">Dashboard</a>
    <a href="add-expense.php">Add Expense</a>
    <a href="add-income.php">Add Income</a>
    <a href="reports.php">Reports</a>
    <a href="logout.php" id="logout-btn">Logout</a>
  </div>

  <div class="main-content">
    <h2>Reports</h2>

    <?php
    $totalIncome = array_sum(array_column($incomes, 'amount'));
    $totalExpense = array_sum(array_column($expenses, 'amount'));
    $balance = $totalIncome - $totalExpense;
    ?>
    <div class="totals">
      <div>Total Income: ₱<?= number_format($totalIncome, 2) ?></div>
      <div>Total Expenses: ₱<?= number_format($totalExpense, 2) ?></div>
      <div>Balance: ₱<?= number_format($balance, 2) ?></div>
    </div>

    <div class="filters">
      <div class="filter-inputs">
        <label>Filter by category:</label>
        <select id="categoryFilter">
          <option value="">All</option>
          <option value="food">Food</option>
          <option value="entertainment">Entertainment</option>
          <option value="utilities">Utilities</option>
          <option value="transportation">Transportation</option>
          <option value="salary">Salary</option>
          <option value="freelance">Freelance</option>
          <option value="investment">Investment</option>
          <option value="other">Other</option>
        </select>

        <label>Filter by month:</label>
        <select id="monthFilter">
          <option value="">All</option>
          <?php
          for ($m = 1; $m <= 12; $m++) {
            $monthName = date("F", mktime(0, 0, 0, $m, 1));
            echo "<option value='$m'>$monthName</option>";
          }
          ?>
        </select>
      </div>
      <div>
        <button type="button" id="editBtn">Edit</button>
        <button type="button" id="deleteSelected">Delete Selected</button>
      </div>
    </div>

    <form method="POST" id="deleteForm">
      <h3>Income</h3>
      <table id="incomeTable">
        <thead>
          <tr>
            <th class="checkbox-col">Select</th>
            <th>Date</th>
            <th>Description</th>
            <th>Category</th>
            <th>Amount (₱)</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($incomes as $inc):
            $month = date('n', strtotime($inc['date']));
            $category = strtolower($inc['category']); // normalize
          ?>
            <tr data-category="<?= htmlspecialchars($category) ?>" data-month="<?= $month ?>" data-id="<?= $inc['id'] ?>">
              <td class="checkbox-col"><input type="checkbox" class="rowCheckbox" value="<?= $inc['id'] ?>"></td>
              <td><?= htmlspecialchars($inc['date']) ?></td>
              <td><?= htmlspecialchars($inc['description']) ?></td>
              <td><?= htmlspecialchars(ucfirst($category)) ?></td>
              <td><?= number_format($inc['amount'], 2) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <h3>Expenses</h3>
      <table id="expensesTable">
        <thead>
          <tr>
            <th class="checkbox-col">Select</th>
            <th>Date</th>
            <th>Description</th>
            <th>Category</th>
            <th>Amount (₱)</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($expenses as $exp):
            $month = date('n', strtotime($exp['date']));
            $category = strtolower($exp['category']); // normalize
          ?>
            <tr data-category="<?= htmlspecialchars($category) ?>" data-month="<?= $month ?>" data-id="<?= $exp['id'] ?>">
              <td class="checkbox-col"><input type="checkbox" class="rowCheckbox" value="<?= $exp['id'] ?>"></td>
              <td><?= htmlspecialchars($exp['date']) ?></td>
              <td><?= htmlspecialchars($exp['description']) ?></td>
              <td><?= htmlspecialchars(ucfirst($category)) ?></td>
              <td><?= number_format($exp['amount'], 2) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </form>

  </div>

  <script>
    // Filter
    const categoryFilter = document.getElementById('categoryFilter');
    const monthFilter = document.getElementById('monthFilter');

    function applyFilters() {
      const cat = categoryFilter.value.toLowerCase();
      const month = monthFilter.value;

      ['incomeTable', 'expensesTable'].forEach(tableId => {
        document.querySelectorAll(`#${tableId} tbody tr`).forEach(r => {
          const rowCat = r.dataset.category.toLowerCase();
          const rowMonth = r.dataset.month;
          r.style.display = ((cat === "" || rowCat === cat) && (month === "" || rowMonth === month)) ? '' : 'none';
        });
      });
    }
    categoryFilter.addEventListener('change', applyFilters);
    monthFilter.addEventListener('change', applyFilters);

    // Edit / Delete
    const editBtn = document.getElementById('editBtn');
    const deleteBtn = document.getElementById('deleteSelected');
    let editing = false;

    editBtn.addEventListener('click', () => {
      editing = !editing;
      document.querySelectorAll('.checkbox-col').forEach(c => c.style.display = editing ? 'table-cell' : 'none');
      deleteBtn.style.display = editing ? 'inline-block' : 'none';
      editBtn.textContent = editing ? 'Cancel' : 'Edit';
    });

    deleteBtn.addEventListener('click', () => {
      const checkboxes = document.querySelectorAll('.rowCheckbox:checked');
      if (!checkboxes.length) {
        alert('Select at least one row to delete.');
        return;
      }
      if (!confirm('Are you sure you want to delete selected records?')) return;

      const form = document.getElementById('deleteForm');
      form.querySelectorAll('input[name="delete_ids[]"]').forEach(i => i.remove());

      checkboxes.forEach(cb => {
        const row = cb.closest('tr');
        const type = row.closest('#incomeTable') ? 'income' : 'expenses';
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'delete_ids[]';
        input.value = `${cb.value}:${type}`;
        form.appendChild(input);
      });

      form.submit();
    });
  </script>
</body>

</html>