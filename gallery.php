<?php
// gallery.php: Mostrar todos los archivos MP3 almacenados en la carpeta "uploads/"
$uploadDir = 'uploads/';
$files = array();
if (is_dir($uploadDir)) {
    // Se excluyen los puntos "." y ".."
    $files = array_diff(scandir($uploadDir), array('.', '..'));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Galería de MP3</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:400,700">
  <style>
    body {
      background-color: #1c1c1c;
      color: #f1f1f1;
      font-family: 'Roboto', sans-serif;
      margin: 0;
      padding: 1em;
    }
    h1 {
      text-align: center;
    }
    .file-item {
      margin: 1em 0;
      padding: 1em;
      border: 1px solid #444;
      border-radius: 4px;
      background-color: #2c2c2c;
    }
    a {
      color: #ff6600;
      text-decoration: none;
    }
    a:hover {
      text-decoration: underline;
    }
    audio {
      width: 100%;
      margin-top: 0.5em;
    }
  </style>
</head>
<body>
  <h1>Galería de MP3 Subidos</h1>
  <?php if (empty($files)): ?>
    <p>No hay archivos MP3 subidos.</p>
  <?php else: ?>
    <?php foreach ($files as $file): ?>
      <?php $filePath = $uploadDir . $file; ?>
      <div class="file-item">
        <p><?php echo htmlspecialchars($file); ?></p>
        <audio controls>
          <source src="<?php echo htmlspecialchars($filePath); ?>" type="audio/mpeg">
          Tu navegador no soporta el elemento de audio.
        </audio>
        <p><a href="<?php echo htmlspecialchars($filePath); ?>" download>Descargar MP3</a></p>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</body>
</html>
