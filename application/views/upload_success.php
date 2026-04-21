<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Archivo subido</title>
    <link rel="stylesheet" 
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5" style="max-width: 600px;">
    <div class="alert alert-success">
        <strong>¡Archivo subido correctamente!</strong><br>
        ID de análisis: #<?= $upload_id ?>
    </div>
    <a href="<?= base_url('upload') ?>" class="btn btn-outline-primary">
        Subir otro archivo
    </a>
</div>
</body>
</html>