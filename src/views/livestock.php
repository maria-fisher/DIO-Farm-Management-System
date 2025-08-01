<?php
$stmt = $pdo->query("SELECT * FROM livestock ORDER BY created_at DESC");
$livestock = $stmt->fetchAll();

// Get livestock for editing if edit parameter is set
$edit_livestock = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM livestock WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_livestock = $stmt->fetch();
}
?>

<div class="row">
    <!-- Stats Cards -->
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <i class="fas fa-cow fa-2x mb-2"></i>
                <h4><?= count($livestock) ?></h4>
                <p>Total Animals</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body text-center">
                <i class="fas fa-weight fa-2x mb-2"></i>
                <h4><?= number_format(array_sum(array_column($livestock, 'weight')), 0) ?></h4>
                <p>Total Weight (lbs)</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body text-center">
                <i class="fas fa-dollar-sign fa-2x mb-2"></i>
                <h4>$<?= number_format(array_sum(array_column($livestock, 'purchase_price')), 0) ?></h4>
                <p>Total Investment</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <i class="fas fa-heart fa-2x mb-2"></i>
                <h4><?= count(array_filter($livestock, function($l) { return $l['health_status'] === 'healthy'; })) ?></h4>
                <p>Healthy Animals</p>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <!-- Form -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-plus"></i> <?= $edit_livestock ? 'Edit' : 'Add New' ?> Livestock</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="<?= $edit_livestock ? 'update' : 'create' ?>">
                    <input type="hidden" name="table" value="livestock">
                    <?php if ($edit_livestock): ?>
                        <input type="hidden" name="id" value="<?= $edit_livestock['id'] ?>">
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label class="form-label">Type *</label>
                        <select class="form-control" name="type" required>
                            <option value="">Select Type</option>
                            <option value="Cattle" <?= ($edit_livestock['type'] ?? '') === 'Cattle' ? 'selected' : '' ?>>Cattle</option>
                            <option value="Pig" <?= ($edit_livestock['type'] ?? '') === 'Pig' ? 'selected' : '' ?>>Pig</option>
                            <option value="Chicken" <?= ($edit_livestock['type'] ?? '') === 'Chicken' ? 'selected' : '' ?>>Chicken</option>
                            <option value="Sheep" <?= ($edit_livestock['type'] ?? '') === 'Sheep' ? 'selected' : '' ?>>Sheep</option>
                            <option value="Goat" <?= ($edit_livestock['type'] ?? '') === 'Goat' ? 'selected' : '' ?>>Goat</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Breed</label>
                        <input type="text" class="form-control" name="breed" 
                               value="<?= $edit_livestock['breed'] ?? '' ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Tag Number</label>
                        <input type="text" class="form-control" name="tag_number" 
                               value="<?= $edit_livestock['tag_number'] ?? '' ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Birth Date</label>
                        <input type="date" class="form-control" name="birth_date" 
                               value="<?= $edit_livestock['birth_date'] ?? '' ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Gender</label>
                        <select class="form-control" name="gender">
                            <option value="">Select Gender</option>
                            <option value="male" <?= ($edit_livestock['gender'] ?? '') === 'male' ? 'selected' : '' ?>>Male</option>
                            <option value="female" <?= ($edit_livestock['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Weight (lbs)</label>
                        <input type="number" step="0.1" class="form-control" name="weight" 
                               value="<?= $edit_livestock['weight'] ?? '' ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Health Status</label>
                        <select class="form-control" name="health_status">
                            <option value="healthy" <?= ($edit_livestock['health_status'] ?? 'healthy') === 'healthy' ? 'selected' : '' ?>>Healthy</option>
                            <option value="sick" <?= ($edit_livestock['health_status'] ?? '') === 'sick' ? 'selected' : '' ?>>Sick</option>
                            <option value="injured" <?= ($edit_livestock['health_status'] ?? '') === 'injured' ? 'selected' : '' ?>>Injured</option>
                            <option value="recovering" <?= ($edit_livestock['health_status'] ?? '') === 'recovering' ? 'selected' : '' ?>>Recovering</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Purchase Price ($)</label>
                        <input type="number" step="0.01" class="form-control" name="purchase_price" 
                               value="<?= $edit_livestock['purchase_price'] ?? '' ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Purchase Date</label>
                        <input type="date" class="form-control" name="purchase_date" 
                               value="<?= $edit_livestock['purchase_date'] ?? '' ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" name="notes" rows="3"><?= $edit_livestock['notes'] ?? '' ?></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> <?= $edit_livestock ? 'Update' : 'Add' ?> Livestock
                    </button>
                    <?php if ($edit_livestock): ?>
                        <a href="index.php?tab=livestock" class="btn btn-secondary">Cancel</a>
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
                        <h6>Livestock by Type</h6>
                    </div>
                    <div class="card-body">
                        <canvas id="livestockTypeChart" height="200"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h6>Health Status Overview</h6>
                    </div>
                    <div class="card-body">
                        <canvas id="healthChart" height="200"></canvas>
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
                <h5><i class="fas fa-list"></i> Livestock List</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive table-container">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Tag #</th>
                                <th>Type</th>
                                <th>Breed</th>
                                <th>Birth Date</th>
                                <th>Gender</th>
                                <th>Weight (lbs)</th>
                                <th>Health Status</th>
                                <th>Purchase Price</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($livestock as $animal): ?>
                            <tr>
                                <td><?= htmlspecialchars($animal['tag_number']) ?></td>
                                <td>
                                    <i class="fas fa-<?= 
                                        $animal['type'] === 'Cattle' ? 'cow' : 
                                        ($animal['type'] === 'Pig' ? 'piggy-bank' : 
                                        ($animal['type'] === 'Chicken' ? 'egg' : 'paw')) 
                                    ?>"></i>
                                    <?= htmlspecialchars($animal['type']) ?>
                                </td>
                                <td><?= htmlspecialchars($animal['breed']) ?></td>
                                <td><?= $animal['birth_date'] ? date('M d, Y', strtotime($animal['birth_date'])) : '-' ?></td>
                                <td>
                                    <i class="fas fa-<?= $animal['gender'] === 'male' ? 'mars' : 'venus' ?>"></i>
                                    <?= ucfirst($animal['gender']) ?>
                                </td>
                                <td><?= number_format($animal['weight'], 1) ?></td>
                                <td>
                                    <span class="badge bg-<?= 
                                        $animal['health_status'] === 'healthy' ? 'success' : 
                                        ($animal['health_status'] === 'sick' ? 'danger' : 
                                        ($animal['health_status'] === 'injured' ? 'warning' : 'info')) 
                                    ?>">
                                        <?= ucfirst($animal['health_status']) ?>
                                    </span>
                                </td>
                                <td>$<?= number_format($animal['purchase_price'], 2) ?></td>
                                <td>
                                    <a href="index.php?tab=livestock&edit=<?= $animal['id'] ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="index.php?tab=livestock&delete&table=livestock&id=<?= $animal['id'] ?>" 
                                       class="btn btn-sm btn-outline-danger"
                                       onclick="return confirm('Are you sure you want to delete this animal record?')">
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
// Livestock Type Chart
<?php
$type_counts = array_count_values(array_column($livestock, 'type'));
?>
const livestockTypeData = {
    labels: <?= json_encode(array_keys($type_counts)) ?>,
    datasets: [{
        data: <?= json_encode(array_values($type_counts)) ?>,
        backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF']
    }]
};

new Chart(document.getElementById('livestockTypeChart'), {
    type: 'doughnut',
    data: livestockTypeData,
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'bottom' }
        }
    }
});

// Health Status Chart
<?php
$health_counts = array_count_values(array_column($livestock, 'health_status'));
?>
const healthData = {
    labels: <?= json_encode(array_keys($health_counts)) ?>,
    datasets: [{
        data: <?= json_encode(array_values($health_counts)) ?>,
        backgroundColor: ['#28A745', '#DC3545', '#FFC107', '#17A2B8']
    }]
};

new Chart(document.getElementById('healthChart'), {
    type: 'bar',
    data: healthData,
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: { beginAtZero: true }
        }
    }
});
</script>