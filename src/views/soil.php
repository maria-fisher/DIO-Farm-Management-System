<?php
$stmt = $pdo->query("SELECT * FROM soil ORDER BY test_date DESC");
$soil_records = $stmt->fetchAll();

// Get soil record for editing if edit parameter is set
$edit_soil = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM soil WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_soil = $stmt->fetch();
}

// Calculate averages
$avg_ph = count($soil_records) > 0 ? array_sum(array_column($soil_records, 'ph_level')) / count($soil_records) : 0;
$avg_nitrogen = count($soil_records) > 0 ? array_sum(array_column($soil_records, 'nitrogen_level')) / count($soil_records) : 0;
$avg_phosphorus = count($soil_records) > 0 ? array_sum(array_column($soil_records, 'phosphorus_level')) / count($soil_records) : 0;
$avg_potassium = count($soil_records) > 0 ? array_sum(array_column($soil_records, 'potassium_level')) / count($soil_records) : 0;
?>

<div class="row">
    <!-- Stats Cards -->
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <i class="fas fa-vial fa-2x mb-2"></i>
                <h4><?= number_format($avg_ph, 2) ?></h4>
                <p>Average pH Level</p>
                <small><?= $avg_ph >= 6.0 && $avg_ph <= 7.5 ? 'Optimal' : ($avg_ph < 6.0 ? 'Acidic' : 'Alkaline') ?></small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <i class="fas fa-leaf fa-2x mb-2"></i>
                <h4><?= number_format($avg_nitrogen, 1) ?></h4>
                <p>Avg Nitrogen (ppm)</p>
                <small><?= $avg_nitrogen >= 20 ? 'Good' : 'Low' ?></small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body text-center">
                <i class="fas fa-seedling fa-2x mb-2"></i>
                <h4><?= number_format($avg_phosphorus, 1) ?></h4>
                <p>Avg Phosphorus (ppm)</p>
                <small><?= $avg_phosphorus >= 30 ? 'Good' : 'Low' ?></small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body text-center">
                <i class="fas fa-mountain fa-2x mb-2"></i>
                <h4><?= number_format($avg_potassium, 1) ?></h4>
                <p>Avg Potassium (ppm)</p>
                <small><?= $avg_potassium >= 150 ? 'Good' : 'Low' ?></small>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <!-- Form -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-plus"></i> <?= $edit_soil ? 'Edit' : 'Add New' ?> Soil Test</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="<?= $edit_soil ? 'update' : 'create' ?>">
                    <input type="hidden" name="table" value="soil">
                    <?php if ($edit_soil): ?>
                        <input type="hidden" name="id" value="<?= $edit_soil['id'] ?>">
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label class="form-label">Field Name *</label>
                        <input type="text" class="form-control" name="field_name" 
                               value="<?= $edit_soil['field_name'] ?? '' ?>" required
                               placeholder="e.g., North Field, Section A">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Location</label>
                        <input type="text" class="form-control" name="location" 
                               value="<?= $edit_soil['location'] ?? '' ?>"
                               placeholder="GPS coordinates or description">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">pH Level</label>
                        <input type="number" step="0.01" min="0" max="14" class="form-control" name="ph_level" 
                               value="<?= $edit_soil['ph_level'] ?? '' ?>">
                        <small class="form-text text-muted">Optimal range: 6.0 - 7.5</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Nitrogen Level (ppm)</label>
                        <input type="number" step="0.1" class="form-control" name="nitrogen_level" 
                               value="<?= $edit_soil['nitrogen_level'] ?? '' ?>">
                        <small class="form-text text-muted">Good level: 20+ ppm</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Phosphorus Level (ppm)</label>
                        <input type="number" step="0.1" class="form-control" name="phosphorus_level" 
                               value="<?= $edit_soil['phosphorus_level'] ?? '' ?>">
                        <small class="form-text text-muted">Good level: 30+ ppm</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Potassium Level (ppm)</label>
                        <input type="number" step="0.1" class="form-control" name="potassium_level" 
                               value="<?= $edit_soil['potassium_level'] ?? '' ?>">
                        <small class="form-text text-muted">Good level: 150+ ppm</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Organic Matter (%)</label>
                        <input type="number" step="0.1" class="form-control" name="organic_matter" 
                               value="<?= $edit_soil['organic_matter'] ?? '' ?>">
                        <small class="form-text text-muted">Good level: 3%+</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Test Date</label>
                        <input type="date" class="form-control" name="test_date" 
                               value="<?= $edit_soil['test_date'] ?? date('Y-m-d') ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Recommendations</label>
                        <textarea class="form-control" name="recommendations" rows="3"><?= $edit_soil['recommendations'] ?? '' ?></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> <?= $edit_soil ? 'Update' : 'Add' ?> Soil Test
                    </button>
                    <?php if ($edit_soil): ?>
                        <a href="index.php?tab=soil" class="btn btn-secondary">Cancel</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Charts -->
    <div class="col-md-8">
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6>pH Levels by Field</h6>
                    </div>
                    <div class="card-body">
                        <canvas id="phChart" height="200"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6>Nutrient Levels Comparison</h6>
                    </div>
                    <div class="card-body">
                        <canvas id="nutrientChart" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-3">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h6>Soil Health Overview</h6>
                    </div>
                    <div class="card-body">
                        <canvas id="soilHealthChart" height="150"></canvas>
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
                <h5><i class="fas fa-list"></i> Soil Test Records</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive table-container">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Field Name</th>
                                <th>Location</th>
                                <th>Test Date</th>
                                <th>pH Level</th>
                                <th>Nitrogen (ppm)</th>
                                <th>Phosphorus (ppm)</th>
                                <th>Potassium (ppm)</th>
                                <th>Organic Matter (%)</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($soil_records as $soil): ?>
                            <tr>
                                <td><?= htmlspecialchars($soil['field_name']) ?></td>
                                <td><?= htmlspecialchars($soil['location']) ?></td>
                                <td><?= $soil['test_date'] ? date('M d, Y', strtotime($soil['test_date'])) : '-' ?></td>
                                <td>
                                    <span class="badge bg-<?= 
                                        $soil['ph_level'] >= 6.0 && $soil['ph_level'] <= 7.5 ? 'success' : 
                                        ($soil['ph_level'] < 6.0 ? 'warning' : 'info') 
                                    ?>">
                                        <?= number_format($soil['ph_level'], 2) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $soil['nitrogen_level'] >= 20 ? 'success' : 'warning' ?>">
                                        <?= number_format($soil['nitrogen_level'], 1) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $soil['phosphorus_level'] >= 30 ? 'success' : 'warning' ?>">
                                        <?= number_format($soil['phosphorus_level'], 1) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $soil['potassium_level'] >= 150 ? 'success' : 'warning' ?>">
                                        <?= number_format($soil['potassium_level'], 1) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $soil['organic_matter'] >= 3 ? 'success' : 'warning' ?>">
                                        <?= number_format($soil['organic_matter'], 1) ?>%
                                    </span>
                                </td>
                                <td>
                                    <a href="index.php?tab=soil&edit=<?= $soil['id'] ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="index.php?tab=soil&delete&table=soil&id=<?= $soil['id'] ?>" 
                                       class="btn btn-sm btn-outline-danger"
                                       onclick="return confirm('Are you sure you want to delete this soil test record?')">
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
// pH Levels Chart
const phData = {
    labels: [<?php foreach($soil_records as $soil): ?>'<?= addslashes($soil['field_name']) ?>',<?php endforeach; ?>],
    datasets: [{
        label: 'pH Level',
        data: [<?php foreach($soil_records as $soil): ?><?= $soil['ph_level'] ?>,<?php endforeach; ?>],
        backgroundColor: [<?php foreach($soil_records as $soil): ?>
            '<?= $soil['ph_level'] >= 6.0 && $soil['ph_level'] <= 7.5 ? '#28A745' : ($soil['ph_level'] < 6.0 ? '#FFC107' : '#17A2B8') ?>',
        <?php endforeach; ?>],
        borderWidth: 1
    }]
};

new Chart(document.getElementById('phChart'), {
    type: 'bar',
    data: phData,
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: { 
                beginAtZero: true,
                max: 14,
                title: { display: true, text: 'pH Level' }
            }
        },
        plugins: {
            legend: { display: false }
        }
    }
});

// Nutrient Levels Chart
const nutrientData = {
    labels: [<?php foreach($soil_records as $soil): ?>'<?= addslashes($soil['field_name']) ?>',<?php endforeach; ?>],
    datasets: [
        {
            label: 'Nitrogen (ppm)',
            data: [<?php foreach($soil_records as $soil): ?><?= $soil['nitrogen_level'] ?>,<?php endforeach; ?>],
            backgroundColor: 'rgba(40, 167, 69, 0.7)',
            borderColor: 'rgba(40, 167, 69, 1)',
            borderWidth: 1
        },
        {
            label: 'Phosphorus (ppm)',
            data: [<?php foreach($soil_records as $soil): ?><?= $soil['phosphorus_level'] ?>,<?php endforeach; ?>],
            backgroundColor: 'rgba(220, 53, 69, 0.7)',
            borderColor: 'rgba(220, 53, 69, 1)',
            borderWidth: 1
        },
        {
            label: 'Potassium (ppm)',
            data: [<?php foreach($soil_records as $soil): ?><?= $soil['potassium_level'] ?>,<?php endforeach; ?>],
            backgroundColor: 'rgba(23, 162, 184, 0.7)',
            borderColor: 'rgba(23, 162, 184, 1)',
            borderWidth: 1
        }
    ]
};

const nutrientCtx = document.getElementById('nutrientChart');
if (nutrientCtx) {
    new Chart(nutrientCtx, {
        type: 'bar',
        data: nutrientData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { 
                    beginAtZero: true,
                    title: { display: true, text: 'Nutrient Levels (ppm)' }
                }
            },
            plugins: {
                legend: { position: 'top' }
            }
        }
    });
}

// Soil Health Overview Chart
const soilHealthData = {
    labels: [<?php 
        $labels = [];
        foreach($soil_records as $soil) {
            $labels[] = addslashes($soil['field_name'] . ' (' . date('M Y', strtotime($soil['test_date'])) . ')');
        }
        echo '"' . implode('", "', $labels) . '"';
    ?>],
    datasets: [
        {
            label: 'Organic Matter (%)',
            data: [<?php foreach($soil_records as $soil): ?><?= $soil['organic_matter'] ?>,<?php endforeach; ?>],
            backgroundColor: 'rgba(108, 117, 125, 0.7)',
            borderColor: 'rgba(108, 117, 125, 1)',
            borderWidth: 1,
            type: 'line',
            fill: false,
            tension: 0.4
        }
    ]
};

const soilHealthCtx = document.getElementById('soilHealthChart');
if (soilHealthCtx) {
    new Chart(soilHealthCtx, {
        type: 'line',
        data: soilHealthData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { 
                    beginAtZero: true,
                    title: { display: true, text: 'Organic Matter (%)' }
                }
            },
            plugins: {
                legend: { position: 'top' },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + context.raw.toFixed(1) + '%';
                        }
                    }
                }
            }
        }
    });
}
</script>