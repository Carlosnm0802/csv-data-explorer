<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial de análisis</title>
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5 mb-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0">Historial de análisis</h2>
            <p class="text-muted mb-0" style="font-size:13px;">
                <?= count($uploads) ?> archivo(s) procesado(s)
            </p>
        </div>
        <a href="<?= base_url('upload') ?>" class="btn btn-primary btn-sm">
            Subir nuevo CSV
        </a>
    </div>

    <?php if (empty($uploads)): ?>
        <div class="alert alert-info">
            No hay archivos analizados todavía.
            <a href="<?= base_url('upload') ?>">Sube tu primer CSV</a>
        </div>
    <?php else: ?>
        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Archivo</th>
                            <th>Tamaño</th>
                            <th>Filas</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($uploads as $upload): ?>
                        <tr>
                            <td class="text-muted"><?= $upload->id ?></td>
                            <td>
                                <strong><?= htmlspecialchars($upload->original_name) ?></strong>
                            </td>
                            <td class="text-muted">
                                <?= round($upload->file_size / 1024, 1) ?> KB
                            </td>
                            <td><?= number_format($upload->total_rows) ?></td>
                            <td>
                                <?php if ($upload->status === 'analyzed'): ?>
                                    <span class="badge bg-success">Analizado</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">
                                        <?= $upload->status ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-muted" style="font-size:13px;">
                                <?= date('d/m/Y H:i', strtotime($upload->created_at)) ?>
                            </td>
                            <td>
                                <?php if ($upload->analysis_id): ?>
                                    <a href="<?= base_url('upload/results/' . $upload->id) ?>"
                                       class="btn btn-outline-primary btn-sm">
                                        Ver
                                    </a>
                                    <a href="<?= base_url('upload/export_csv/' . $upload->id) ?>"
                                       class="btn btn-outline-success btn-sm">
                                        CSV
                                    </a>
                                    <a href="<?= base_url('upload/export_pdf/' . $upload->id) ?>"
                                       class="btn btn-outline-danger btn-sm">
                                        PDF
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted" style="font-size:12px;">
                                        Sin análisis
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

</div>
</body>
</html>