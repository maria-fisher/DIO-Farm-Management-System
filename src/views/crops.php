<?php
$stmt = $pdo->query("SELECT * FROM crops ORDER BY created_at DESC");
$crops = $stmt->fetchAll();

// Get crop for editing if edit parameter is set
$edit_crop = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM crops WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_crop = $stmt->fetch();
}
?>

<div class="row">
    <!-- Stats Cards -->
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <i class="fas fa-seedling fa-2x mb-2"></i>
                <h4><?= count($crops) ?></h4>
                <p>Total Crops</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body text-center">
                <i class="fas fa-chart-area fa-2x mb-2"></i>
                <h4><?= number_format(array_sum(array_column($crops, 'area_acres')), 1) ?></h4>
                <p>Total Acres</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body text-center">
                <i class="fas fa-clock fa-2x mb-2"></i>
                <h4><?= count(array_filter($crops, function($c) { return $c['status'] === 'growing'; })) ?></h4>
                <p>Growing</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <i class="fas fa-check-circle fa-2x mb-2"></i>
                <h4><?= count(array_filter($crops, function($c) { return $c['status'] === 'harvested'; })) ?></h4>
                <p>Harvested</p>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <!-- Form -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-plus"></i> <?= $edit_crop ? 'Edit' : 'Add New' ?> Crop</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="<?= $edit_crop ? 'update' : 'create' ?>">
                    <input type="hidden" name="table" value="crops">
                    <?php if ($edit_crop): ?>
                        <input type="hidden" name="id" value="<?= $edit_crop['id'] ?>">
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label class="form-label">Crop Name *</label>
                        <input type="text" class="form-control" name="name" 
                               value="<?= $edit_crop['name'] ?? '' ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Variety</label>
                        <input type="text" class="form-control" name="variety" 
                               value="<?= $edit_crop['variety'] ?? '' ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Planting Date</label>
                        <input type="date" class="form-control" name="planting_date" 
                               value="<?= $edit_crop['planting_date'] ?? '' ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Harvest Date</label>
                        <input type="date" class="form-control" name="harvest_date" 
                               value="<?= $edit_crop['harvest_date'] ?? '' ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Area (acres)</label>
                        <input type="number" step="0.1" class="form-control" name="area_acres" 
                               value="<?= $edit_crop['area_acres'] ?? '' ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Expected Yield</label>
                        <input type="number" step="0.1" class="form-control" name="expected_yield" 
                               value="<?= $edit_crop['expected_yield'] ?? '' ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Actual Yield</label>
                        <input type="number" step="0.1" class="form-control" name="actual_yield" 
                               value="<?= $edit_crop['actual_yield'] ?? '' ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-control" name="status">
                            <option value="planted" <?= ($edit_crop['status'] ?? '') === 'planted' ? 'selected' : '' ?>>Planted</option>
                            <option value="growing" <?= ($edit_crop['status'] ?? '') === 'growing' ? 'selected' : '' ?>>Growing</option>
                            <option value="harvested" <?= ($edit_crop['status'] ?? '') === 'harvested' ? 'selected' : '' ?>>Harvested</option>
                            <option value="sold" <?= ($edit_crop['status'] ?? '') === 'sold' ? 'selected' : '' ?>>Sold</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" name="notes" rows="3"><?= $edit_crop['notes'] ?? '' ?></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> <?= $edit_crop ? 'Update' : 'Add' ?> Crop
                    </button>
                    <?php if ($edit_crop): ?>
                        <a href="index.php?tab=crops" class="btn btn-secondary">Cancel</a>
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
                        <h6>Crop Status Distribution</h6>
                    </div>
                    <div class="card-body">
                        <canvas id="cropStatusChart" height="200"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6>Yield Performance</h6>
                    </div>
                    <div class="card-body">
                        <canvas id="yieldChart" height="200"></canvas>
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
                <h5><i class="fas fa-list"></i> Crops List</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive table-container">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Variety</th>
                                <th>Planting Date</th>
                                <th>Harvest Date</th>
                                <th>Area (acres)</th>
                                <th>Expected Yield</th>
                                <th>Actual Yield</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($crops as $crop): ?>
                            <tr>
                                <td><?= htmlspecialchars($crop['name']) ?></td>
                                <td><?= htmlspecialchars($crop['variety']) ?></td>
                                <td><?= $crop['planting_date'] ? date('M d, Y', strtotime($crop['planting_date'])) : '-' ?></td>
                                <td><?= $crop['harvest_date'] ? date('M d, Y', strtotime($crop['harvest_date'])) : '-' ?></td>
                                <td><?= number_format($crop['area_acres'], 1) ?></td>
                                <td><?= number_format($crop['expected_yield'], 1) ?></td>
                                <td><?= $crop['actual_yield'] ? number_format($crop['actual_yield'], 1) : '-' ?></td>
                                <td>
                                    <span class="badge bg-<?= 
                                        $crop['status'] === 'planted' ? 'warning' : 
                                        ($crop['status'] === 'growing' ? 'info' : 
                                        ($crop['status'] === 'harvested' ? 'success' : 'primary')) 
                                    ?>">
                                        <?= ucfirst($crop['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="index.php?tab=crops&edit=<?= $crop['id'] ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="index.php?tab=crops&delete&table=crops&id=<?= $crop['id'] ?>" 
                                       class="btn btn-sm btn-outline-danger"
                                       onclick="return confirm('Are you sure you want to delete this crop?')">
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
// Crop Status Chart
<?php
$status_counts = array_count_values(array_column($crops, 'status'));
?>
const cropStatusData = {
    labels: <?= json_encode(array_keys($status_counts)) ?>,
    datasets: [{
        data: <?= json_encode(array_values($status_counts)) ?>,
        backgroundColor: ['#FFC107', '#17A2B8', '#28A745', '#007BFF']
    }]
};

new Chart(document.getElementById('cropStatusChart'), {
    type: 'pie',
    data: cropStatusData,
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'bottom' }
        }
    }
});

// Yield Performance Chart
const yieldData = {
    labels: [<?php foreach($crops as $crop): ?>'<?= addslashes($crop['name']) ?>',<?php endforeach; ?>],
    datasets: [
        {
            label: 'Expected Yield',
            data: [<?php foreach($crops as $crop): ?><?= $crop['expected_yield'] ?? 0 ?>,<?php endforeach; ?>],
            backgroundColor: 'rgba(54, 162, 235, 0.5)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
        },
        {
            label: 'Actual Yield',
            data: [<?php foreach($crops as $crop): ?><?= $crop['actual_yield'] ?? 0 ?>,<?php endforeach; ?>],
            backgroundColor: 'rgba(75, 192, 192, 0.5)',
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 1
        }
    ]
};

new Chart(document.getElementById('yieldChart'), {
    type: 'bar',
    data: yieldData,
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: { beginAtZero: true }
        }
    }
});
</script>