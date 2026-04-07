<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header("Location: index.html");
  exit();
}

include 'config.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $user_id = $_SESSION['user_id'];
  $date = $_POST['date'];
  $description = $_POST['description'];
  $category = $_POST['category']; 
  $amount = $_POST['amount'];

  $stmt = $conn->prepare("INSERT INTO income (user_id, date, description, category, amount) VALUES (?, ?, ?, ?, ?)");
  $stmt->bind_param("isssd", $user_id, $date, $description, $category, $amount);

  if ($stmt->execute()) {
    $stmt->close();
    header("Location: dashboard.php?success=Income+added+successfully");
    exit();
  } else {
    echo "Error: " . $stmt->error;
  }
}
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add Income - BudgetHabit</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
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
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 20px;
    }

    /* Form */
    .income-form {
      background-color: white;
      padding: 40px 30px;
      border-radius: 8px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      width: 100%;
      max-width: 400px;
    }

    .income-form h2 {
      text-align: center;
      margin-bottom: 30px;
      color: #5a67d8;
    }

    .income-form label {
      display: block;
      margin-bottom: 5px;
      font-weight: 500;
      color: #333;
    }

    .income-form input,
    .income-form select {
      width: 100%;
      padding: 10px;
      margin-bottom: 20px;
      border-radius: 5px;
      border: 1px solid #ccc;
    }

    .income-form button {
      width: 100%;
      padding: 12px;
      background-color: #5a67d8;
      color: white;
      font-weight: bold;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      transition: opacity 0.2s;
    }

    .income-form button:hover {
      opacity: 0.9;
    }

    @media (max-width:768px) {
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

      .main-content {
        padding: 20px 10px;
      }
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
    <form class="income-form" method="POST" action="add-income.php">
      <h2>Add Income</h2>
      <label for="date">Date</label>
      <input type="date" name="date" id="date" required>

      <label for="description">Description</label>
      <input type="text" name="description" id="description" placeholder="Enter description" required>

      <label for="category">Category</label>
      <select name="category" id="category" required>
        <option value="">Select Category</option>
        <option value="Salary">Salary</option>
        <option value="Freelance">Freelance</option>
        <option value="Investment">Investment</option>
        <option value="Other">Other</option>
      </select>

      <label for="amount">Amount</label>
      <input type="number" name="amount" id="amount" placeholder="Enter amount" min="0" step="0.01" required>

      <button type="submit">Add Income</button>
    </form>
  </div>
</body>

</html>