<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Resultados del análisis</title>
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Resultados del análisis</h2>
        <a href="<?= base_url('upload') ?>" class="btn btn-outline-primary btn-sm">
            Subir otro CSV
        </a>
    </div>

    <!-- Resumen general -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="p-3 bg-light rounded text-center">
                <div class="text-muted small">Total de filas</div>
                <div class="fs-4 fw-500"><?= $analysis->total_rows ?></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="p-3 bg-light rounded text-center">
                <div class="text-muted small">Total de columnas</div>
                <div class="fs-4 fw-500"><?= $analysis->total_columns ?></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="p-3 bg-light rounded text-center">
                <div class="text-muted small">Columnas numéricas</div>
                <div class="fs-4 fw-500">
                    <?= count(explode(',', $analysis->numeric_columns)) ?>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="p-3 bg-light rounded text-center">
                <div class="text-muted small">Columnas de texto</div>
                <div class="fs-4 fw-500">
                    <?= count(explode(',', $analysis->text_columns)) ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de estadísticas -->
    <div class="card">
        <div class="card-header">Estadísticas por columna numérica</div>
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
                        <td><strong><?= $col['column'] ?></strong></td>
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
</body>
</html>