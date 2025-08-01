<div class="row">
    <!-- Stats Cards -->
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body text-center">
                <i class="fas fa-seedling fa-3x mb-3"></i>
                <h3><?= $stats['crops']['total'] ?></h3>
                <p>Total Crops</p>
                <small><?= number_format($stats['crops']['total_acres'], 1) ?> acres</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body text-center">
                <i class="fas fa-cow fa-3x mb-3"></i>
                <h3><?= $stats['livestock_total'] ?></h3>
                <p>Total Livestock</p>
                <small>Active animals</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body text-center">
                <i class="fas fa-dollar-sign fa-3x mb-3"></i>
                <?php 
                $income = 0; 
                $expenses = 0;
                
                // Check if finance data exists in stats
                if (isset($stats['finance']) && is_array($stats['finance'])) {
                    foreach($stats['finance'] as $f) {
                        if($f['transaction_type'] === 'income') $income = (float)$f['total'];
                        if($f['transaction_type'] === 'expense') $expenses = (float)$f['total'];
                    }
                }
                $profit = $income - $expenses;
                ?>
                <h3>$<?= number_format($profit, 0) ?></h3>
                <p>Net Profit</p>
                <small>Income: $<?= number_format($income, 0) ?></small>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body text-center">
                <i class="fas fa-thermometer-half fa-3x mb-3"></i>
                <h3><?= number_format($stats['weather']['avg_high'] ?? 0, 1) ?>°F</h3>
                <p>Avg Temperature</p>
                <small><?= number_format($stats['weather']['total_rain'] ?? 0, 2) ?>" rainfall</small>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Livestock Distribution Chart -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-chart-pie"></i> Livestock Distribution</h5>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="livestockChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Financial Overview Chart -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-chart-bar"></i> Financial Overview</h5>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="financialChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Weather Chart -->
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-chart-line"></i> Recent Weather Trends</h5>
            </div>
            <div class="card-body">
                <div class="chart-container" style="height: 400px;">
                    <canvas id="weatherChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Wait for the DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Livestock Distribution Chart
    const livestockCtx = document.getElementById('livestockChart');
    if (livestockCtx) {
        try {
            const livestockData = {
                labels: [<?php foreach($stats['livestock'] as $l): ?>'<?= addslashes($l['type']) ?>',<?php endforeach; ?>],
                datasets: [{
                    data: [<?php foreach($stats['livestock'] as $l): ?><?= $l['count'] ?>,<?php endforeach; ?>],
                    backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF']
                }]
            };

            new Chart(livestockCtx, {
                type: 'doughnut',
                data: livestockData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' },
                        title: {
                            display: true,
                            text: 'Livestock Distribution'
                        }
                    }
                }
            });
        } catch (error) {
            console.error('Error initializing livestock chart:', error);
        }
    }

    // Financial Chart
    const financialCtx = document.getElementById('financialChart');
    if (financialCtx) {
        try {
            const financialData = {
                labels: ['Income', 'Expenses'],
                datasets: [{
                    label: 'Amount ($)',
                    data: [<?= $income ?? 0 ?>, <?= $expenses ?? 0 ?>],
                    backgroundColor: ['#4CAF50', '#F44336']
                }]
            };

            new Chart(financialCtx, {
                type: 'bar',
                data: financialData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { 
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Amount ($)'
                            }
                        }
                    },
                    plugins: {
                        title: {
                            display: true,
                            text: 'Financial Overview'
                        }
                    }
                }
            });
        } catch (error) {
            console.error('Error initializing financial chart:', error);
        }
    }

    // Weather Chart
    const weatherCtx = document.getElementById('weatherChart');
    if (weatherCtx) {
        try {
            <?php
            $stmt = $pdo->query("SELECT record_date, temperature_high_c as temperature_high, temperature_low_c as temperature_low, rainfall_mm as rainfall FROM weather ORDER BY record_date DESC LIMIT 10");
            $weather_data = $stmt ? array_reverse($stmt->fetchAll()) : [];
            ?>

            const weatherData = {
                labels: [<?php foreach($weather_data as $w): ?>'<?= date('M d', strtotime($w['record_date'])) ?>',<?php endforeach; ?>],
                datasets: [
                    {
                        label: 'High Temp (°C)',
                        data: [<?php foreach($weather_data as $w): ?><?= $w['temperature_high'] ?>,<?php endforeach; ?>],
                        borderColor: '#FF6384',
                        backgroundColor: 'rgba(255, 99, 132, 0.1)',
                        yAxisID: 'y',
                        tension: 0.3
                    },
                    {
                        label: 'Low Temp (°C)',
                        data: [<?php foreach($weather_data as $w): ?><?= $w['temperature_low'] ?>,<?php endforeach; ?>],
                        borderColor: '#36A2EB',
                        backgroundColor: 'rgba(54, 162, 235, 0.1)',
                        yAxisID: 'y',
                        tension: 0.3
                    },
                    {
                        label: 'Rainfall (mm)',
                        data: [<?php foreach($weather_data as $w): ?><?= $w['rainfall'] ?>,<?php endforeach; ?>],
                        type: 'bar',
                        backgroundColor: 'rgba(75, 192, 192, 0.6)',
                        yAxisID: 'y1'
                    }
                ]
            };

            new Chart(weatherCtx, {
                type: 'line',
                data: weatherData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    scales: {
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            title: {
                                display: true,
                                text: 'Temperature (°C)'
                            }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            grid: {
                                drawOnChartArea: false,
                            },
                            title: {
                                display: true,
                                text: 'Rainfall (mm)'
                            }
                        }
                    },
                    plugins: {
                        title: {
                            display: true,
                            text: 'Recent Weather Trends'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.y !== null) {
                                        label += context.parsed.y;
                                        if (context.dataset.label.includes('Temp')) {
                                            label += '°C';
                                        } else if (context.dataset.label.includes('Rainfall')) {
                                            label += ' mm';
                                        }
                                    }
                                    return label;
                                }
                            }
                        }
                    }
                }
            });
        } catch (error) {
            console.error('Error initializing weather chart:', error);
            weatherCtx.parentNode.innerHTML = '<p class="text-muted text-center my-4">Weather data not available. Please add weather data to see the chart.</p>';
        }
    }
});
</script>