<?php
$stmt = $pdo->query("SELECT * FROM weather ORDER BY record_date DESC");
$weather_records = $stmt->fetchAll();

// Get weather record for editing if edit parameter is set
$edit_weather = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM weather WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_weather = $stmt->fetch();
}

// Calculate averages for last 30 days
$stmt = $pdo->query("
    SELECT 
        AVG(temperature_high_c) as avg_high,
        AVG(temperature_low_c) as avg_low,
        AVG(humidity_percent) as avg_humidity,
        SUM(rainfall_mm) as total_rainfall,
        AVG(wind_speed_kph) as avg_wind
    FROM weather 
    WHERE record_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
");
$weather_stats = $stmt->fetch();
?>

<div class="row">
    <!-- Stats Cards -->
    <div class="col-md-3">
        <div class="card bg-danger text-white">
            <div class="card-body text-center">
                <i class="fas fa-thermometer-full fa-2x mb-2"></i>
                <h4><?= number_format($weather_stats['avg_high'] ?? 0, 1) ?>°C</h4>
                <p>Avg High Temp</p>
                <small>Last 30 days</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <i class="fas fa-thermometer-empty fa-2x mb-2"></i>
                <h4><?= number_format($weather_stats['avg_low'] ?? 0, 1) ?>°C</h4>
                <p>Avg Low Temp</p>
                <small>Last 30 days</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body text-center">
                <i class="fas fa-tint fa-2x mb-2"></i>
                <h4><?= number_format($weather_stats['total_rainfall'] ?? 0, 2) ?> mm</h4>
                <p>Total Rainfall</p>
                <small>Last 30 days</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <i class="fas fa-water fa-2x mb-2"></i>
                <h4><?= number_format($weather_stats['avg_humidity'] ?? 0, 1) ?>%</h4>
                <p>Avg Humidity</p>
                <small>Last 30 days</small>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <!-- Form -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-plus"></i> <?= $edit_weather ? 'Edit' : 'Add New' ?> Weather Record</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="<?= $edit_weather ? 'update' : 'create' ?>">
                    <input type="hidden" name="table" value="weather">
                    <?php if ($edit_weather): ?>
                        <input type="hidden" name="id" value="<?= $edit_weather['id'] ?>">
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label class="form-label">Record Date *</label>
                        <input type="date" class="form-control" name="record_date" 
                               value="<?= $edit_weather['record_date'] ?? date('Y-m-d') ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">High Temperature (°C)</label>
                        <input type="number" step="0.1" class="form-control" name="temperature_high_c" 
                               value="<?= $edit_weather['temperature_high_c'] ?? '' ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Low Temperature (°C)</label>
                        <input type="number" step="0.1" class="form-control" name="temperature_low_c" 
                               value="<?= $edit_weather['temperature_low_c'] ?? '' ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Humidity (%)</label>
                        <input type="number" step="0.1" min="0" max="100" class="form-control" name="humidity_percent" 
                               value="<?= $edit_weather['humidity_percent'] ?? '' ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Rainfall (mm)</label>
                        <input type="number" step="0.01" min="0" class="form-control" name="rainfall_mm" 
                               value="<?= $edit_weather['rainfall_mm'] ?? '' ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Wind Speed (km/h)</label>
                        <input type="number" step="0.1" min="0" class="form-control" name="wind_speed_kph" 
                               value="<?= $edit_weather['wind_speed_kph'] ?? '' ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Conditions</label>
                        <select class="form-control" name="conditions">
                            <option value="">Select Conditions</option>
                            <option value="Clear" <?= ($edit_weather['conditions'] ?? '') === 'Clear' ? 'selected' : '' ?>>Clear</option>
                            <option value="Sunny" <?= ($edit_weather['conditions'] ?? '') === 'Sunny' ? 'selected' : '' ?>>Sunny</option>
                            <option value="Partly Cloudy" <?= ($edit_weather['conditions'] ?? '') === 'Partly Cloudy' ? 'selected' : '' ?>>Partly Cloudy</option>
                            <option value="Cloudy" <?= ($edit_weather['conditions'] ?? '') === 'Cloudy' ? 'selected' : '' ?>>Cloudy</option>
                            <option value="Overcast" <?= ($edit_weather['conditions'] ?? '') === 'Overcast' ? 'selected' : '' ?>>Overcast</option>
                            <option value="Rainy" <?= ($edit_weather['conditions'] ?? '') === 'Rainy' ? 'selected' : '' ?>>Rainy</option>
                            <option value="Stormy" <?= ($edit_weather['conditions'] ?? '') === 'Stormy' ? 'selected' : '' ?>>Stormy</option>
                            <option value="Foggy" <?= ($edit_weather['conditions'] ?? '') === 'Foggy' ? 'selected' : '' ?>>Foggy</option>
                            <option value="Snow" <?= ($edit_weather['conditions'] ?? '') === 'Snow' ? 'selected' : '' ?>>Snow</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" name="notes" rows="3"><?= $edit_weather['notes'] ?? '' ?></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> <?= $edit_weather ? 'Update' : 'Add' ?> Weather Record
                    </button>
                    <?php if ($edit_weather): ?>
                        <a href="index.php?tab=weather" class="btn btn-secondary">Cancel</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Charts -->
    <div class="col-md-8">
        <div class="row">
            <div class="col-md-12">
                <div class="card mb-3">
                    <div class="card-header">
                        <h6>Temperature Trends (Last 14 Days)</h6>
                    </div>
                    <div class="card-body">
                        <canvas id="temperatureTrendChart" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6>Weather Conditions</h6>
                    </div>
                    <div class="card-body">
                        <canvas id="conditionsChart" height="200"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6>Rainfall Distribution</h6>
                    </div>
                    <div class="card-body">
                        <canvas id="rainfallChart" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-list"></i> Weather Records</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive table-container">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>High Temp</th>
                                <th>Low Temp</th>
                                <th>Humidity</th>
                                <th>Rainfall</th>
                                <th>Wind Speed</th>
                                <th>Conditions</th>
                                <th>Notes</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $stmt = $pdo->query("SELECT id, record_date, temperature_high_c as temperature_high, temperature_low_c as temperature_low, humidity_percent as humidity, rainfall_mm as rainfall, wind_speed_kph as wind_speed, conditions, notes FROM weather ORDER BY record_date DESC");
                            $weather_records = $stmt->fetchAll();
                            foreach ($weather_records as $record): ?>
                            <tr>
                                <td><?= date('M d, Y', strtotime($record['record_date'])) ?></td>
                                <td>
                                    <span class="text-danger">
                                        <i class="fas fa-thermometer-full"></i>
                                        <?= number_format($record['temperature_high'], 1) ?>°C
                                    </span>
                                </td>
                                <td>
                                    <span class="text-primary">
                                        <i class="fas fa-thermometer-empty"></i>
                                        <?= number_format($record['temperature_low'], 1) ?>°C
                                    </span>
                                </td>
                                <td>
                                    <span class="text-info">
                                        <i class="fas fa-water"></i>
                                        <?= number_format($record['humidity'], 1) ?>%
                                    </span>
                                </td>
                                <td>
                                    <?php if ($record['rainfall'] > 0): ?>
                                        <span class="text-primary">
                                            <i class="fas fa-tint"></i>
                                            <?= number_format($record['rainfall'], 2) ?>"
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">0"</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="text-secondary">
                                        <i class="fas fa-wind"></i>
                                        <?= number_format($record['wind_speed'], 1) ?> km/h
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?= 
                                        in_array($record['conditions'], ['Clear', 'Sunny']) ? 'warning' : 
                                        (in_array($record['conditions'], ['Partly Cloudy', 'Cloudy']) ? 'secondary' : 
                                        (in_array($record['conditions'], ['Rainy', 'Stormy']) ? 'primary' : 'info')) 
                                    ?>">
                                        <?= htmlspecialchars($record['conditions']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php 
                                    $notes = $record['notes'] ?? '';
                                    echo htmlspecialchars($notes ? (strlen($notes) > 50 ? substr($notes, 0, 50) . '...' : $notes) : '');
                                    ?>
                                </td>
                                <td>
                                    <a href="index.php?tab=weather&edit=<?= $record['id'] ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="index.php?tab=weather&delete&table=weather&id=<?= $record['id'] ?>" 
                                       class="btn btn-sm btn-outline-danger"
                                       onclick="return confirm('Are you sure you want to delete this weather record?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Temperature Trend Chart
<?php
$stmt = $pdo->query("SELECT record_date, temperature_high_c as temperature_high, temperature_low_c as temperature_low, rainfall_mm as rainfall, wind_speed_kph as wind_speed, humidity_percent as humidity, conditions FROM weather ORDER BY record_date DESC LIMIT 7");
$recent_weather = $stmt->fetchAll();
?>

const temperatureTrendData = {
    labels: [<?php foreach($recent_weather as $w): ?>'<?= date('M d', strtotime($w['record_date'])) ?>',<?php endforeach; ?>],
    datasets: [
        {
            label: 'High Temp (°C)',
            data: [<?php foreach($recent_weather as $w): ?><?= $w['temperature_high'] ?>,<?php endforeach; ?>],
            borderColor: '#DC3545',
            backgroundColor: 'rgba(220, 53, 69, 0.1)',
            fill: '+1'
        },
        {
            label: 'Low Temp (°C)',
            data: [<?php foreach($recent_weather as $w): ?><?= $w['temperature_low'] ?>,<?php endforeach; ?>],
            borderColor: '#007BFF',
            backgroundColor: 'rgba(0, 123, 255, 0.1)',
            fill: 'origin'
        },
        {
            label: 'Rainfall (mm)',
            data: [<?php foreach($recent_weather as $w): ?><?= $w['rainfall'] ?>,<?php endforeach; ?>],
            type: 'bar',
            backgroundColor: 'rgba(23, 162, 184, 0.6)',
            yAxisID: 'y1'
        }
    ]
};

new Chart(document.getElementById('temperatureTrendChart'), {
    type: 'line',
    data: temperatureTrendData,
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                type: 'linear',
                display: true,
                position: 'left',
                title: { display: true, text: 'Temperature (°C)' }
            },
            y1: {
                type: 'linear',
                display: true,
                position: 'right',
                title: { display: true, text: 'Rainfall (mm)' },
                grid: { drawOnChartArea: false }
            }
        }
    }
});

// Weather Conditions Chart
<?php
$conditions_count = array_count_values(array_filter(array_column($weather_records, 'conditions')));
?>
const conditionsData = {
    labels: <?= json_encode(array_keys($conditions_count)) ?>,
    datasets: [{
        data: <?= json_encode(array_values($conditions_count)) ?>,
        backgroundColor: ['#FFC107', '#28A745', '#6C757D', '#007BFF', '#DC3545', '#17A2B8']
    }]
};

new Chart(document.getElementById('conditionsChart'), {
    type: 'doughnut',
    data: conditionsData,
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'bottom' }
        }
    }
});

// Rainfall Chart
const rainfallData = {
    labels: [<?php foreach(array_slice($recent_weather, -7) as $w): ?>'<?= date('M d', strtotime($w['record_date'])) ?>',<?php endforeach; ?>],
    datasets: [{
        label: 'Rainfall (mm)',
        data: [<?php foreach(array_slice($recent_weather, -7) as $w): ?><?= $w['rainfall'] ?>,<?php endforeach; ?>],
        backgroundColor: 'rgba(23, 162, 184, 0.8)',
        borderColor: '#17A2B8',
        borderWidth: 1
    }]
};

new Chart(document.getElementById('rainfallChart'), {
    type: 'bar',
    data: rainfallData,
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: { 
                beginAtZero: true,
                title: { display: true, text: 'Rainfall (mm)' }
            }
        },
        plugins: {
            legend: { display: false }
        }
    }
});
</script>