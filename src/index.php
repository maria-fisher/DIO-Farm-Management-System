<?php
require_once 'config/database.php';

// Get active tab
$active_tab = $_GET['tab'] ?? 'dashboard';

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $table = $_POST['table'] ?? '';
    
    switch ($action) {
        case 'create':
            handleCreate($pdo, $table, $_POST);
            break;
        case 'update':
            handleUpdate($pdo, $table, $_POST);
            break;
        case 'delete':
            handleDelete($pdo, $table, $_POST['id']);
            break;
    }
    
    // Redirect to prevent form resubmission
    header("Location: index.php?tab=$table");
    exit;
}

// Handle individual delete requests
if (isset($_GET['delete']) && isset($_GET['table']) && isset($_GET['id'])) {
    handleDelete($pdo, $_GET['table'], $_GET['id']);
    header("Location: index.php?tab=" . $_GET['table']);
    exit;
}

function handleCreate($pdo, $table, $data) {
    switch ($table) {
        case 'crops':
            $stmt = $pdo->prepare("INSERT INTO crops (name, variety, planting_date, harvest_date, area_acres, expected_yield, actual_yield, status, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$data['name'], $data['variety'], $data['planting_date'], $data['harvest_date'], $data['area_acres'], $data['expected_yield'], $data['actual_yield'], $data['status'], $data['notes']]);
            break;
        case 'livestock':
            $stmt = $pdo->prepare("INSERT INTO livestock (type, breed, tag_number, birth_date, gender, weight, health_status, purchase_price, purchase_date, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$data['type'], $data['breed'], $data['tag_number'], $data['birth_date'], $data['gender'], $data['weight'], $data['health_status'], $data['purchase_price'], $data['purchase_date'], $data['notes']]);
            break;
        case 'finance':
            $stmt = $pdo->prepare("INSERT INTO finance (transaction_type, category, description, amount, transaction_date, payment_method, reference_number) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$data['transaction_type'], $data['category'], $data['description'], $data['amount'], $data['transaction_date'], $data['payment_method'], $data['reference_number']]);
            break;
        case 'soil':
            $stmt = $pdo->prepare("INSERT INTO soil (field_name, location, ph_level, nitrogen_level, phosphorus_level, potassium_level, organic_matter, test_date, recommendations) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$data['field_name'], $data['location'], $data['ph_level'], $data['nitrogen_level'], $data['phosphorus_level'], $data['potassium_level'], $data['organic_matter'], $data['test_date'], $data['recommendations']]);
            break;
        case 'weather':
            $stmt = $pdo->prepare("INSERT INTO weather (record_date, temperature_high_c, temperature_low_c, humidity_percent, rainfall_mm, wind_speed_kph, conditions, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $data['record_date'], 
                $data['temperature_high'], 
                $data['temperature_low'], 
                $data['humidity'], 
                $data['rainfall'], 
                $data['wind_speed'], 
                $data['conditions'], 
                $data['notes']
            ]);
            break;
    }
}

function handleUpdate($pdo, $table, $data) {
    $id = $data['id'];
    switch ($table) {
        case 'crops':
            $stmt = $pdo->prepare("UPDATE crops SET name=?, variety=?, planting_date=?, harvest_date=?, area_acres=?, expected_yield=?, actual_yield=?, status=?, notes=? WHERE id=?");
            $stmt->execute([$data['name'], $data['variety'], $data['planting_date'], $data['harvest_date'], $data['area_acres'], $data['expected_yield'], $data['actual_yield'], $data['status'], $data['notes'], $id]);
            break;
        case 'livestock':
            $stmt = $pdo->prepare("UPDATE livestock SET type=?, breed=?, tag_number=?, birth_date=?, gender=?, weight=?, health_status=?, purchase_price=?, purchase_date=?, notes=? WHERE id=?");
            $stmt->execute([$data['type'], $data['breed'], $data['tag_number'], $data['birth_date'], $data['gender'], $data['weight'], $data['health_status'], $data['purchase_price'], $data['purchase_date'], $data['notes'], $id]);
            break;
        case 'finance':
            $stmt = $pdo->prepare("UPDATE finance SET transaction_type=?, category=?, description=?, amount=?, transaction_date=?, payment_method=?, reference_number=? WHERE id=?");
            $stmt->execute([$data['transaction_type'], $data['category'], $data['description'], $data['amount'], $data['transaction_date'], $data['payment_method'], $data['reference_number'], $id]);
            break;
        case 'soil':
            $stmt = $pdo->prepare("UPDATE soil SET field_name=?, location=?, ph_level=?, nitrogen_level=?, phosphorus_level=?, potassium_level=?, organic_matter=?, test_date=?, recommendations=? WHERE id=?");
            $stmt->execute([$data['field_name'], $data['location'], $data['ph_level'], $data['nitrogen_level'], $data['phosphorus_level'], $data['potassium_level'], $data['organic_matter'], $data['test_date'], $data['recommendations'], $id]);
            break;
        case 'weather':
            $stmt = $pdo->prepare("UPDATE weather SET record_date=?, temperature_high_c=?, temperature_low_c=?, humidity_percent=?, rainfall_mm=?, wind_speed_kph=?, conditions=?, notes=? WHERE id=?");
            $stmt->execute([
                $data['record_date'], 
                $data['temperature_high'], 
                $data['temperature_low'], 
                $data['humidity'], 
                $data['rainfall'], 
                $data['wind_speed'], 
                $data['conditions'], 
                $data['notes'], 
                $id
            ]);
            break;
    }
}

function handleDelete($pdo, $table, $id) {
    $stmt = $pdo->prepare("DELETE FROM $table WHERE id = ?");
    $stmt->execute([$id]);
}

// Get statistics for dashboard
function getDashboardStats($pdo) {
    $stats = [];
    
    // Crops stats
    $stmt = $pdo->query("SELECT COUNT(*) as total, SUM(area_acres) as total_acres FROM crops");
    $crops = $stmt->fetch();
    $stats['crops'] = $crops;
    
    // Livestock stats
    $stmt = $pdo->query("SELECT COUNT(*) as total, type, COUNT(*) as count FROM livestock GROUP BY type");
    $livestock = $stmt->fetchAll();
    $stats['livestock'] = $livestock;
    $stats['livestock_total'] = array_sum(array_column($livestock, 'count'));
    
    // finance stats
    $stmt = $pdo->query("SELECT transaction_type, SUM(amount) as total FROM finance GROUP BY transaction_type");
    $finance = $stmt->fetchAll();
    $stats['finance'] = $finance;
    
    // Weather stats
    $stmt = $pdo->query("SELECT AVG(temperature_high_c) as avg_high, AVG(temperature_low_c) as avg_low, SUM(rainfall_mm) as total_rain FROM weather WHERE record_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
    $weather = $stmt->fetch();
    $stats['weather'] = $weather;
    
    return $stats;
}

$stats = getDashboardStats($pdo);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farm Management System</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <style>
        .navbar-brand { font-weight: bold; }
        .card { box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); margin-bottom: 20px; }
        .stat-card { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .table-container { max-height: 500px; overflow-y: auto; }
        .chart-container { position: relative; height: 300px; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-tractor"></i> Farm Management System
            </a>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <ul class="nav nav-tabs" id="farmTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $active_tab === 'dashboard' ? 'active' : '' ?>" 
                        onclick="location.href='index.php?tab=dashboard'">
                    <i class="fas fa-chart-dashboard"></i> Dashboard
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $active_tab === 'crops' ? 'active' : '' ?>" 
                        onclick="location.href='index.php?tab=crops'">
                    <i class="fas fa-seedling"></i> Crops
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $active_tab === 'livestock' ? 'active' : '' ?>" 
                        onclick="location.href='index.php?tab=livestock'">
                    <i class="fas fa-cow"></i> Livestock
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $active_tab === 'finance' ? 'active' : '' ?>" 
                        onclick="location.href='index.php?tab=finance'">
                    <i class="fas fa-dollar-sign"></i> finance
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $active_tab === 'soil' ? 'active' : '' ?>" 
                        onclick="location.href='index.php?tab=soil'">
                    <i class="fas fa-mountain"></i> Soil
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $active_tab === 'weather' ? 'active' : '' ?>" 
                        onclick="location.href='index.php?tab=weather'">
                    <i class="fas fa-cloud-sun"></i> Weather
                </button>
            </li>
        </ul>

        <div class="tab-content mt-4">
            <?php if ($active_tab === 'dashboard'): ?>
                <?php include 'views/dashboard.php'; ?>
            <?php elseif ($active_tab === 'crops'): ?>
                <?php include 'views/crops.php'; ?>
            <?php elseif ($active_tab === 'livestock'): ?>
                <?php include 'views/livestock.php'; ?>
            <?php elseif ($active_tab === 'finance'): ?>
                <?php include 'views/finance.php'; ?>
            <?php elseif ($active_tab === 'soil'): ?>
                <?php include 'views/soil.php'; ?>
            <?php elseif ($active_tab === 'weather'): ?>
                <?php include 'views/weather.php'; ?>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>