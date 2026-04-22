<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Resultados del análisis</title>
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        .stat-card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1rem;
            text-align: center;
        }
        .stat-card .label {
            font-size: 12px;
            color: #6c757d;
            margin-bottom: 4px;
        }
        .stat-card .value {
            font-size: 24px;
            font-weight: 500;
            color: #212529;
        }
        .chart-wrapper {
            position: relative;
            height: 300px;
        }
    </style>
</head>
<body>
<div class="container mt-5 mb-5">

    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0">Resultados del análisis</h2>
            <p class="text-muted mb-0" style="font-size:13px;">
                ID #<?= $upload_id ?>
            </p>
        </div>
        <a href="<?= base_url('upload') ?>" class="btn btn-outline-primary btn-sm">
            Subir otro CSV
        </a>
    </div>

    <!-- Tarjetas de resumen -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="label">Total de filas</div>
                <div class="value"><?= number_format($analysis->total_rows) ?></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="label">Total de columnas</div>
                <div class="value"><?= $analysis->total_columns ?></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="label">Columnas numéricas</div>
                <div class="value">
                    <?= count(array_filter(explode(',', $analysis->numeric_columns))) ?>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="label">Columnas de texto</div>
                <div class="value">
                    <?= count(array_filter(explode(',', $analysis->text_columns))) ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficas -->
    <div class="row g-4 mb-4">

        <!-- Gráfica 1: Barras - Promedio por columna -->
        <div class="col-12 col-md-7">
            <div class="card h-100">
                <div class="card-header">
                    Promedio por columna numérica
                </div>
                <div class="card-body">
                    <div class="chart-wrapper">
                        <canvas id="barChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráfica 2: Dona - Válidos vs Nulos -->
        <div class="col-12 col-md-5">
            <div class="card h-100">
                <div class="card-header">
                    Datos válidos vs nulos (total)
                </div>
                <div class="card-body d-flex align-items-center justify-content-center">
                    <div class="chart-wrapper w-100">
                        <canvas id="donutChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Tabla de estadísticas detalladas -->
    <div class="card">
        <div class="card-header">
            Estadísticas detalladas por columna
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Columna</th>
                        <th>Mínimo</th>
                        <th>Máximo</th>
                        <th>Promedio</th>
                        <th>Mediana</th>
                        <th>Desv. estándar</th>
                        <th>Nulos</th>
                        <th>Válidos</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($stats as $col): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($col['column']) ?></strong></td>
                        <td><?= $col['min'] ?></td>
                        <td><?= $col['max'] ?></td>
                        <td><?= $col['mean'] ?></td>
                        <td><?= $col['median'] ?></td>
                        <td><?= $col['std'] ?></td>
                        <td>
                            <?php if ($col['nulls'] > 0): ?>
                                <span class="badge bg-warning text-dark">
                                    <?= $col['nulls'] ?>
                                </span>
                            <?php else: ?>
                                <span class="badge bg-success">0</span>
                            <?php endif; ?>
                        </td>
                        <td><?= $col['valid'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Chart.js desde CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
    // Datos que vienen de PHP convertidos a JavaScript
    const statsData = <?= json_encode($stats) ?>;

    // Extraer labels y valores para las gráficas
    const labels  = statsData.map(col => col.column);
    const means   = statsData.map(col => col.mean);
    const totalValid = statsData.reduce((sum, col) => sum + col.valid, 0);
    const totalNulls = statsData.reduce((sum, col) => sum + col.nulls, 0);

    // Paleta de colores consistente
    const colors = [
        '#4F8EF7', '#34C78A', '#F7C244', '#E85D5D',
        '#A78BFA', '#FB923C', '#22D3EE', '#F472B6'
    ];

    // ---- GRÁFICA 1: Barras ----
    const barCtx = document.getElementById('barChart').getContext('2d');
    new Chart(barCtx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Promedio',
                data: means,
                backgroundColor: colors.slice(0, labels.length),
                borderRadius: 6,
                borderSkipped: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,0.05)' }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });

    // ---- GRÁFICA 2: Dona ----
    const donutCtx = document.getElementById('donutChart').getContext('2d');
    new Chart(donutCtx, {
        type: 'doughnut',
        data: {
            labels: ['Válidos', 'Nulos'],
            datasets: [{
                data: [totalValid, totalNulls],
                backgroundColor: ['#34C78A', '#E85D5D'],
                borderWidth: 0,
                hoverOffset: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { padding: 16, font: { size: 13 } }
                }
            },
            cutout: '65%'
        }
    });
</script>

</body>
</html>