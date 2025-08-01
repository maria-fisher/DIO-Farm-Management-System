<?php
// Debug: Check database connection and table content
try {
    // Check if finance table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'finance'");
    $tableExists = $stmt->rowCount() > 0;
    
    if (!$tableExists) {
        error_log("Finance table does not exist in the database");
    } else {
        // Check if there are any records in finance table
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM finance");
        $result = $stmt->fetch();
        error_log("Finance table has " . $result['count'] . " records");
        
        // Get first few records for debugging
        $stmt = $pdo->query("SELECT * FROM finance ORDER BY transaction_date DESC LIMIT 3");
        $sampleRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);
        error_log("Sample finance records: " . print_r($sampleRecords, true));
    }
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
}

// Get all financial transactions with error handling
try {
    $stmt = $pdo->query("SELECT * FROM finance ORDER BY transaction_date DESC");
    $finance = $stmt->fetchAll();
    error_log("Retrieved " . count($finance) . " finance records");
    
    // Debug: Log first few records
    if (count($finance) > 0) {
        error_log("First finance record: " . print_r($finance[0], true));
    }
} catch (PDOException $e) {
    error_log("Error fetching finance records: " . $e->getMessage());
    $finance = [];
}

// Initialize chart data arrays with default values
$income_categories = [];
$expense_categories = [];
$cash_flow_by_month = [];
$months = [];
$income_by_month = [];
$expense_by_month = [];

// Debug: Log initial state
error_log("Initial finance data count: " . count($finance));

// Get finance record for editing if edit parameter is set
$edit_finance = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM finance WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_finance = $stmt->fetch();
}

// Get date range filter - ensure proper date format
$date_from = !empty($_GET['date_from']) ? date('Y-m-d', strtotime($_GET['date_from'])) : date('Y-01-01');
$date_to = !empty($_GET['date_to']) ? date('Y-m-d', strtotime($_GET['date_to'])) : date('Y-m-d');
$category_filter = $_GET['category'] ?? '';

// Apply filters to finance data with proper date comparison
$filtered_finance = array_filter($finance, function($transaction) use ($date_from, $date_to, $category_filter) {
    $transaction_date = date('Y-m-d', strtotime($transaction['transaction_date']));
    $date_match = $transaction_date >= $date_from && $transaction_date <= $date_to;
    
    $category_match = true;
    if (!empty($category_filter)) {
        $category_match = isset($transaction['category']) && 
                         strtolower(trim($transaction['category'])) === strtolower(trim($category_filter));
    }
    
    return $date_match && $category_match;
});

// Reset array keys to ensure proper JSON encoding
$filtered_finance = array_values($filtered_finance);

// Calculate totals from filtered data
$filtered_income = array_sum(array_map(function($f) { 
    return $f['transaction_type'] === 'income' ? (float)$f['amount'] : 0; 
}, $filtered_finance));

$filtered_expenses = array_sum(array_map(function($f) { 
    return $f['transaction_type'] === 'expense' ? (float)$f['amount'] : 0; 
}, $filtered_finance));

// Calculate totals from all data
$total_income = array_sum(array_map(function($f) { 
    return $f['transaction_type'] === 'income' ? (float)$f['amount'] : 0; 
}, $finance));

$total_expenses = array_sum(array_map(function($f) { 
    return $f['transaction_type'] === 'expense' ? (float)$f['amount'] : 0; 
}, $finance));

$net_profit = $total_income - $total_expenses;

// Get unique categories for dropdown
$all_categories = array_unique(array_map(function($f) { 
    return $f['category']; 
}, $finance));
sort($all_categories);

// Prepare data for charts
if (!empty($finance)) {
    // Get income categories data - ensure we're using the filtered data
    $income_categories = $pdo->query("
        SELECT category, SUM(amount) as total 
        FROM finance 
        WHERE transaction_type = 'income' 
        AND transaction_date BETWEEN '$date_from' AND '$date_to'
        " . (!empty($category_filter) ? " AND category = " . $pdo->quote($category_filter) : "") . "
        GROUP BY category 
        ORDER BY total DESC
        LIMIT 8
    ")->fetchAll();

    // Get expense categories data - ensure we're using the filtered data
    $expense_categories = $pdo->query("
        SELECT category, SUM(amount) as total 
        FROM finance 
        WHERE transaction_type = 'expense' 
        AND transaction_date BETWEEN '$date_from' AND '$date_to'
        " . (!empty($category_filter) ? " AND category = " . $pdo->quote($category_filter) : "") . "
        GROUP BY category 
        ORDER BY total DESC
        LIMIT 8
    ")->fetchAll();

    // Get last 6 months for trends
    for ($i = 5; $i >= 0; $i--) {
        $month = date('Y-m', strtotime("-$i months"));
        $months[] = $month;
        $income_by_month[$month] = 0;
        $expense_by_month[$month] = 0;
    }

    // Get monthly data with proper date filtering
    $monthly_data = $pdo->query("
        SELECT 
            DATE_FORMAT(transaction_date, '%Y-%m') as month,
            transaction_type,
            SUM(amount) as total
        FROM finance 
        WHERE transaction_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
        AND transaction_date BETWEEN '$date_from' AND '$date_to'
        " . (!empty($category_filter) ? " AND category = " . $pdo->quote($category_filter) : "") . "
        GROUP BY month, transaction_type 
        ORDER BY month
    ")->fetchAll();

    foreach ($monthly_data as $data) {
        if (in_array($data['month'], $months)) {
            if ($data['transaction_type'] === 'income') {
                $income_by_month[$data['month']] = (float)$data['total'];
            } else {
                $expense_by_month[$data['month']] = (float)$data['total'];
            }
        }
    }

    // Get cash flow data with proper date filtering
    $cash_flow_data = $pdo->query("
        SELECT 
            DATE_FORMAT(transaction_date, '%Y-%m') as month,
            SUM(CASE WHEN transaction_type = 'income' THEN amount ELSE -amount END) as net_flow
        FROM finance 
        WHERE transaction_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
        AND transaction_date BETWEEN '$date_from' AND '$date_to'
        " . (!empty($category_filter) ? " AND category = " . $pdo->quote($category_filter) : "") . "
        GROUP BY month
        ORDER BY month
    ")->fetchAll(PDO::FETCH_ASSOC);

    // Initialize cash flow data for all months
    foreach ($months as $month) {
        $cash_flow_by_month[$month] = 0;
    }
    
    // Fill with actual data
    foreach ($cash_flow_data as $row) {
        if (isset($cash_flow_by_month[$row['month']])) {
            $cash_flow_by_month[$row['month']] = (float)$row['net_flow'];
        }
    }
}
?>

<div class="row mb-3">
    <!-- Filter Controls -->
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h6><i class="fas fa-filter"></i> Filters & Date Range</h6>
            </div>
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <input type="hidden" name="tab" value="finance">
                    <div class="col-md-3">
                        <label class="form-label">From Date</label>
                        <input type="date" class="form-control" name="date_from" value="<?= $date_from ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">To Date</label>
                        <input type="date" class="form-control" name="date_to" value="<?= $date_to ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Category Filter</label>
                        <input type="text" class="form-control" name="category" value="<?= $category_filter ?>" placeholder="Search categories...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Apply Filters
                            </button>
                            <a href="index.php?tab=finance" class="btn btn-secondary">Clear</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Enhanced Stats Cards -->
    <div class="col-md-2">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <i class="fas fa-arrow-up fa-2x mb-2"></i>
                <h5>$<?= number_format($total_income, 0) ?></h5>
                <p class="mb-1">Total Income</p>
                <small>All Time</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-danger text-white">
            <div class="card-body text-center">
                <i class="fas fa-arrow-down fa-2x mb-2"></i>
                <h5>$<?= number_format($total_expenses, 0) ?></h5>
                <p class="mb-1">Total Expenses</p>
                <small>All Time</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card <?= $net_profit >= 0 ? 'bg-primary' : 'bg-warning' ?> text-white">
            <div class="card-body text-center">
                <i class="fas fa-chart-line fa-2x mb-2"></i>
                <h5>$<?= number_format($net_profit, 0) ?></h5>
                <p class="mb-1">Net Profit</p>
                <small>All Time</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-info text-white">
            <div class="card-body text-center">
                <i class="fas fa-calendar-month fa-2x mb-2"></i>
                <h5>$<?= number_format($filtered_income, 0) ?></h5>
                <p class="mb-1">This Period</p>
                <small>Income</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-secondary text-white">
            <div class="card-body text-center">
                <i class="fas fa-calendar-minus fa-2x mb-2"></i>
                <h5>$<?= number_format($filtered_expenses, 0) ?></h5>
                <p class="mb-1">This Period</p>
                <small>Expenses</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card bg-dark text-white">
            <div class="card-body text-center">
                <i class="fas fa-receipt fa-2x mb-2"></i>
                <h5><?= count($filtered_finance) ?></h5>
                <p class="mb-1">Transactions</p>
                <small>Filtered</small>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <!-- Enhanced Form -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-plus"></i> <?= $edit_finance ? 'Edit' : 'Add New' ?> Transaction</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="<?= $edit_finance ? 'update' : 'create' ?>">
                    <input type="hidden" name="table" value="finance">
                    <?php if ($edit_finance): ?>
                        <input type="hidden" name="id" value="<?= $edit_finance['id'] ?>">
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label class="form-label">Transaction Type *</label>
                        <select class="form-control" name="transaction_type" required onchange="updateCategoryOptions(this.value)">
                            <option value="">Select Type</option>
                            <option value="income" <?= ($edit_finance['transaction_type'] ?? '') === 'income' ? 'selected' : '' ?>>
                                <i class="fas fa-arrow-up"></i> Income
                            </option>
                            <option value="expense" <?= ($edit_finance['transaction_type'] ?? '') === 'expense' ? 'selected' : '' ?>>
                                <i class="fas fa-arrow-down"></i> Expense
                            </option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Category *</label>
                        <select class="form-control" name="category" id="categorySelect" required>
                            <option value="">Select Category</option>
                        </select>
                        <input type="text" class="form-control mt-2" name="custom_category" id="customCategory" 
                               placeholder="Or enter custom category" style="display: none;">
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" id="useCustomCategory" onchange="toggleCustomCategory()">
                            <label class="form-check-label" for="useCustomCategory">
                                Use custom category
                            </label>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="2" 
                                  placeholder="Enter transaction details..."><?= $edit_finance['description'] ?? '' ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Amount ($) *</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" step="0.01" class="form-control" name="amount" 
                                   value="<?= $edit_finance['amount'] ?? '' ?>" required min="0">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Transaction Date *</label>
                        <input type="date" class="form-control" name="transaction_date" 
                               value="<?= $edit_finance['transaction_date'] ?? date('Y-m-d') ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Payment Method</label>
                        <select class="form-control" name="payment_method">
                            <option value="">Select Method</option>
                            <option value="Cash" <?= ($edit_finance['payment_method'] ?? '') === 'Cash' ? 'selected' : '' ?>>
                                <i class="fas fa-money-bills"></i> Cash
                            </option>
                            <option value="Check" <?= ($edit_finance['payment_method'] ?? '') === 'Check' ? 'selected' : '' ?>>
                                <i class="fas fa-money-check"></i> Check
                            </option>
                            <option value="Credit Card" <?= ($edit_finance['payment_method'] ?? '') === 'Credit Card' ? 'selected' : '' ?>>
                                <i class="fas fa-credit-card"></i> Credit Card
                            </option>
                            <option value="Debit Card" <?= ($edit_finance['payment_method'] ?? '') === 'Debit Card' ? 'selected' : '' ?>>
                                <i class="fas fa-credit-card"></i> Debit Card
                            </option>
                            <option value="Bank Transfer" <?= ($edit_finance['payment_method'] ?? '') === 'Bank Transfer' ? 'selected' : '' ?>>
                                <i class="fas fa-university"></i> Bank Transfer
                            </option>
                            <option value="Online Payment" <?= ($edit_finance['payment_method'] ?? '') === 'Online Payment' ? 'selected' : '' ?>>
                                <i class="fas fa-globe"></i> Online Payment
                            </option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Reference Number</label>
                        <input type="text" class="form-control" name="reference_number" 
                               value="<?= $edit_finance['reference_number'] ?? '' ?>"
                               placeholder="Invoice #, Check #, Receipt #, etc.">
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> <?= $edit_finance ? 'Update' : 'Add' ?> Transaction
                        </button>
                        <?php if ($edit_finance): ?>
                            <a href="index.php?tab=finance" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Enhanced Charts -->
    <div class="col-md-8">
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6><i class="fas fa-chart-bar"></i> Income vs Expenses</h6>
                    </div>
                    <div class="card-body">
                        <canvas id="incomeExpenseChart" height="200"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6><i class="fas fa-chart-line"></i> Monthly Trends (Last 6 Months)</h6>
                    </div>
                    <div class="card-body">
                        <canvas id="monthlyTrendChart" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-3">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6><i class="fas fa-chart-pie"></i> Income Categories</h6>
                    </div>
                    <div class="card-body">
                        <canvas id="incomeCategoryChart" height="200"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6><i class="fas fa-chart-doughnut"></i> Expense Categories</h6>
                    </div>
                    <div class="card-body">
                        <canvas id="expenseCategoryChart" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h6><i class="fas fa-chart-area"></i> Cash Flow Analysis</h6>
                    </div>
                    <div class="card-body">
                        <canvas id="cashFlowChart" height="150"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5><i class="fas fa-list"></i> finance Transactions</h5>
                <div>
                    <span class="badge bg-success">Income: $<?= number_format(array_sum(array_map(function($f) { return $f['transaction_type'] === 'income' ? $f['amount'] : 0; }, $filtered_finance)), 2) ?></span>
                    <span class="badge bg-danger">Expenses: $<?= number_format(array_sum(array_map(function($f) { return $f['transaction_type'] === 'expense' ? $f['amount'] : 0; }, $filtered_finance)), 2) ?></span>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive table-container">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Category</th>
                                <th>Description</th>
                                <th>Amount</th>
                                <th>Payment Method</th>
                                <th>Reference</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($filtered_finance)): ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">
                                        <i class="fas fa-search fa-2x mb-2"></i><br>
                                        No transactions found matching your filters.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($filtered_finance as $transaction): ?>
                                <tr>
                                    <td>
                                        <strong><?= date('M d, Y', strtotime($transaction['transaction_date'])) ?></strong><br>
                                        <small class="text-muted"><?= date('l', strtotime($transaction['transaction_date'])) ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $transaction['transaction_type'] === 'income' ? 'success' : 'danger' ?> fs-6">
                                            <i class="fas fa-arrow-<?= $transaction['transaction_type'] === 'income' ? 'up' : 'down' ?>"></i>
                                            <?= ucfirst($transaction['transaction_type']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($transaction['category']) ?></strong>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($transaction['description']) ?>
                                    </td>
                                    <td>
                                        <span class="fw-bold <?= $transaction['transaction_type'] === 'income' ? 'text-success' : 'text-danger' ?>">
                                            <?= $transaction['transaction_type'] === 'income' ? '+' : '-' ?>$<?= number_format($transaction['amount'], 2) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($transaction['payment_method']): ?>
                                            <span class="badge bg-secondary">
                                                <?= htmlspecialchars($transaction['payment_method']) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($transaction['reference_number']): ?>
                                            <code><?= htmlspecialchars($transaction['reference_number']) ?></code>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="index.php?tab=finance&edit=<?= $transaction['id'] ?>" 
                                               class="btn btn-sm btn-outline-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="index.php?tab=finance&delete&table=finance&id=<?= $transaction['id'] ?>" 
                                               class="btn btn-sm btn-outline-danger" title="Delete"
                                               onclick="return confirm('Are you sure you want to delete this transaction?\n\nType: <?= ucfirst($transaction['transaction_type']) ?>\nAmount: $<?= number_format($transaction['amount'], 2) ?>\nCategory: <?= htmlspecialchars($transaction['category']) ?>')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Debug: Log chart data to console
console.log('Chart data:', {
    filtered_income: <?= json_encode($filtered_income) ?>,
    filtered_expenses: <?= json_encode($filtered_expenses) ?>,
    months: <?= json_encode($months) ?>,
    income_by_month: <?= json_encode($income_by_month) ?>,
    expense_by_month: <?= json_encode($expense_by_month) ?>,
    income_categories: <?= json_encode($income_categories) ?>,
    expense_categories: <?= json_encode($expense_categories) ?>,
    cash_flow_by_month: <?= json_encode($cash_flow_by_month) ?>
});

// Wait for the DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Category options based on transaction type
    const categoryOptions = {
        income: [
            'Crop Sales', 'Livestock Sales', 'Equipment Sales', 'Land Rental', 'Government Subsidies',
            'Insurance Claims', 'Investment Income', 'Consulting', 'Contract Services', 'Other Income'
        ],
        expense: [
            'Seeds & Plants', 'Feed', 'Fertilizer', 'Pesticides', 'Equipment', 'Fuel', 'Utilities',
            'Insurance', 'Veterinary', 'Labor', 'Equipment Maintenance', 'Transportation', 'Marketing',
            'Professional Services', 'Taxes', 'Interest', 'Other Expenses'
        ]
    };

    function updateCategoryOptions(transactionType) {
        const categorySelect = document.getElementById('categorySelect');
        const currentValue = '<?= addslashes($edit_finance['category'] ?? '') ?>';
        
        // Clear existing options
        if (categorySelect) {
            categorySelect.innerHTML = '<option value="">Select Category</option>';
            
            if (transactionType && categoryOptions[transactionType]) {
                categoryOptions[transactionType].forEach(category => {
                    const option = document.createElement('option');
                    option.value = category;
                    option.textContent = category;
                    if (category === currentValue) {
                        option.selected = true;
                    }
                    categorySelect.appendChild(option);
                });
            }
        }
    }

    function toggleCustomCategory() {
        const checkbox = document.getElementById('useCustomCategory');
        const categorySelect = document.getElementById('categorySelect');
        const customCategory = document.getElementById('customCategory');
        
        if (checkbox && categorySelect && customCategory) {
            if (checkbox.checked) {
                categorySelect.style.display = 'none';
                categorySelect.required = false;
                customCategory.style.display = 'block';
                customCategory.required = true;
                customCategory.name = 'category';
                categorySelect.name = 'category_unused';
            } else {
                categorySelect.style.display = 'block';
                categorySelect.required = true;
                customCategory.style.display = 'none';
                customCategory.required = false;
                categorySelect.name = 'category';
                customCategory.name = 'custom_category';
            }
        }
    }

    // Initialize category options if editing
    <?php if (isset($edit_finance) && $edit_finance): ?>
    updateCategoryOptions('<?= addslashes($edit_finance['transaction_type'] ?? '') ?>');
    <?php endif; ?>

    // Initialize charts
    try {
        // Income vs Expenses Chart
        const incomeExpenseCtx = document.getElementById('incomeExpenseChart');
        console.log('Income vs Expenses Chart Container:', incomeExpenseCtx);
        if (incomeExpenseCtx) {
            const incomeExpenseData = {
                labels: ['Income', 'Expenses'],
                datasets: [{
                    data: [
                        <?= (float)($filtered_income ?? 0) ?>, 
                        <?= (float)($filtered_expenses ?? 0) ?>
                    ],
                    backgroundColor: ['#28A745', '#DC3545'],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            };
            console.log('Income vs Expenses Data:', incomeExpenseData);

            try {
                new Chart(incomeExpenseCtx, {
                    type: 'bar',
                    data: incomeExpenseData,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: { 
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return '$' + value.toLocaleString();
                                    }
                                }
                            }
                        },
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return context.label + ': $' + context.raw.toLocaleString();
                                    }
                                }
                            }
                        }
                    }
                });
                console.log('Income vs Expenses Chart initialized successfully');
            } catch (chartError) {
                console.error('Error initializing Income vs Expenses Chart:', chartError);
            }
        } else {
            console.warn('Income vs Expenses chart container not found');
        }

        // Monthly Trend Chart
        const monthlyTrendCtx = document.getElementById('monthlyTrendChart');
        if (monthlyTrendCtx) {
            const monthlyTrendData = {
                labels: [<?php 
                    if (isset($months)) {
                        foreach($months as $m) { 
                            echo "'" . date('M Y', strtotime($m . '-01')) . "',"; 
                        } 
                    }
                ?>],
                datasets: [
                    {
                        label: 'Income',
                        data: [<?php 
                            if (isset($months) && isset($income_by_month)) {
                                foreach($months as $m) { 
                                    echo ($income_by_month[$m] ?? 0) . ','; 
                                } 
                            }
                        ?>],
                        borderColor: '#28A745',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: 'Expenses',
                        data: [<?php 
                            if (isset($months) && isset($expense_by_month)) {
                                foreach($months as $m) { 
                                    echo ($expense_by_month[$m] ?? 0) . ','; 
                                } 
                            }
                        ?>],
                        borderColor: '#DC3545',
                        backgroundColor: 'rgba(220, 53, 69, 0.1)',
                        fill: true,
                        tension: 0.4
                    }
                ]
            };

            new Chart(monthlyTrendCtx, {
                type: 'line',
                data: monthlyTrendData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { 
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '$' + value.toLocaleString();
                                }
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': $' + context.raw.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        } else {
            console.warn('Monthly Trend chart container not found');
        }

        // Income Categories Chart
        const incomeCategoryCtx = document.getElementById('incomeCategoryChart');
        if (incomeCategoryCtx) {
            const incomeCategoryData = {
                labels: [<?php 
                    if (isset($income_categories)) {
                        foreach($income_categories as $cat) { 
                            echo "'" . addslashes($cat['category'] ?? '') . "',"; 
                        } 
                    }
                ?>],
                datasets: [{
                    data: [<?php 
                        if (isset($income_categories)) {
                            foreach($income_categories as $cat) { 
                                echo ($cat['total'] ?? 0) . ','; 
                            } 
                        }
                    ?>],
                    backgroundColor: [
                        '#28A745', '#20C997', '#17A2B8', '#6F42C1', 
                        '#E83E8C', '#FD7E14', '#FFC107', '#6C757D'
                    ]
                }]
            };

            new Chart(incomeCategoryCtx, {
                type: 'pie',
                data: incomeCategoryData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = ((context.raw / total) * 100).toFixed(1);
                                    return context.label + ': $' + context.raw.toLocaleString() + ' (' + percentage + '%)';
                                }
                            }
                        }
                    }
                }
            });
        } else {
            console.warn('Income Categories chart container not found');
        }

        // Expense Categories Chart
        const expenseCategoryCtx = document.getElementById('expenseCategoryChart');
        if (expenseCategoryCtx) {
            const expenseCategoryData = {
                labels: [<?php 
                    if (isset($expense_categories)) {
                        foreach($expense_categories as $cat) { 
                            echo "'" . addslashes($cat['category'] ?? '') . "',"; 
                        } 
                    }
                ?>],
                datasets: [{
                    data: [<?php 
                        if (isset($expense_categories)) {
                            foreach($expense_categories as $cat) { 
                                echo ($cat['total'] ?? 0) . ','; 
                            } 
                        }
                    ?>],
                    backgroundColor: [
                        '#DC3545', '#E74C3C', '#C0392B', '#A93226',
                        '#922B21', '#7B241C', '#641E16', '#4A1C1C'
                    ]
                }]
            };

            new Chart(expenseCategoryCtx, {
                type: 'doughnut',
                data: expenseCategoryData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = ((context.raw / total) * 100).toFixed(1);
                                    return context.label + ': $' + context.raw.toLocaleString() + ' (' + percentage + '%)';
                                }
                            }
                        }
                    }
                }
            });
        } else {
            console.warn('Expense Categories chart container not found');
        }

        // Cash Flow Analysis Chart
        const cashFlowCtx = document.getElementById('cashFlowChart');
        if (cashFlowCtx) {
            const cashFlowData = {
                labels: [<?php 
                    if (isset($cash_flow_by_month)) {
                        foreach ($cash_flow_by_month as $month => $value) { 
                            echo "'" . date('M Y', strtotime($month . '-01')) . "',"; 
                        } 
                    }
                ?>],
                datasets: [{
                    label: 'Net Cash Flow',
                    data: [<?php 
                        if (isset($cash_flow_by_month)) {
                            foreach ($cash_flow_by_month as $value) { 
                                echo ($value ?? 0) . ','; 
                            } 
                        }
                    ?>],
                    borderColor: '#007BFF',
                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            };

            new Chart(cashFlowCtx, {
                type: 'line',
                data: cashFlowData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: false,
                            ticks: {
                                callback: function(value) {
                                    return '$' + value.toLocaleString();
                                }
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': $' + context.raw.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        } else {
            console.warn('Cash Flow Analysis chart container not found');
        }
        
    } catch (error) {
        console.error('Error initializing charts:', error);
    }
});
</script>
