<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>CSV Data Explorer</title>
    <link rel="stylesheet" 
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5" style="max-width: 600px;">
    <h2 class="mb-1">CSV Data Explorer</h2>
    <p class="text-muted mb-4">Sube un archivo CSV para analizar su contenido</p>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form action="<?= site_url('upload/process') ?>" 
                  method="post" 
                  enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label fw-500">Selecciona tu archivo CSV</label>
                    <input type="file" 
                           name="csv_file" 
                           accept=".csv" 
                           class="form-control" 
                           required>
                    <div class="form-text">Máximo 5MB. Solo archivos .csv</div>
                </div>
                <button type="submit" class="btn btn-primary w-100">
                    Subir y analizar
                </button>
            </form>
        </div>
    </div>
</div>
</body>
</html>