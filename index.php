<?php
// index.php

// --- Procesamiento de la subida de archivos ---
$uploadSuccess = '';
$uploadError = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['mp3file'])) {
    if ($_FILES['mp3file']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        // Crear la carpeta si no existe
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $promptId = isset($_POST['promptId']) ? intval($_POST['promptId']) : 0;
        $originalName = basename($_FILES['mp3file']['name']);
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        if (strtolower($extension) !== 'mp3') {
            $uploadError = "Solo se permiten archivos .mp3";
        } else {
            // Se crea un nombre único: prompt{ID}_{timestamp}_{nombreOriginal}.mp3
            $timestamp = time();
            $newFileName = $uploadDir . "prompt{$promptId}_{$timestamp}_" . $originalName;
            if (move_uploaded_file($_FILES['mp3file']['tmp_name'], $newFileName)) {
                $uploadSuccess = "Archivo subido correctamente: " . htmlspecialchars($newFileName);
            } else {
                $uploadError = "Error al mover el archivo subido.";
            }
        }
    } else {
        $uploadError = "Error en la carga del archivo.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Generador de Prompts para Ableton</title>
  <!-- Usamos la fuente Roboto para una estética moderna -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:400,700">
  <style>
    /* Estilos generales inspirados en Ableton */
    body {
      background-color: #1c1c1c;
      color: #f1f1f1;
      font-family: 'Roboto', sans-serif;
      margin: 0;
      padding: 0;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }
    .container {
      background-color: #2c2c2c;
      padding: 2em;
      border-radius: 8px;
      box-shadow: 0 0 20px rgba(0,0,0,0.5);
      text-align: center;
      width: 90%;
      max-width: 600px;
    }
    h1 {
      margin-bottom: 0.5em;
      font-size: 2em;
      letter-spacing: 1px;
    }
    .prompt, .instructions {
      font-size: 1.1em;
      margin: 1em 0;
      padding: 0.5em;
      border: 1px solid #444;
      border-radius: 4px;
      background-color: #1e1e1e;
    }
    button {
      background-color: #ff6600;
      border: none;
      padding: 0.8em 1.2em;
      color: #fff;
      font-size: 1em;
      border-radius: 4px;
      cursor: pointer;
      transition: background-color 0.3s ease;
      margin: 0.5em;
    }
    button:hover {
      background-color: #e65c00;
    }
    #uploadFormContainer {
      display: none;
      margin-top: 1em;
      padding: 1em;
      border: 1px solid #444;
      border-radius: 4px;
      background-color: #1e1e1e;
    }
    .message {
      margin: 1em 0;
    }
    a {
      color: #ff6600;
      text-decoration: none;
    }
    a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <div class="container">
    <h1>Generador de Prompts para Ableton</h1>
    <!-- Mensajes de resultado de subida -->
    <?php if ($uploadSuccess): ?>
      <div class="message" style="color: lightgreen;"><?php echo $uploadSuccess; ?></div>
    <?php endif; ?>
    <?php if ($uploadError): ?>
      <div class="message" style="color: red;"><?php echo $uploadError; ?></div>
    <?php endif; ?>

    <!-- Área para mostrar el prompt y las instrucciones -->
    <div class="prompt" id="promptDisplay">
      Presiona el botón para generar un prompt creativo.
    </div>
    <div class="instructions" id="instructionDisplay">
      Las instrucciones aparecerán aquí.
    </div>

    <!-- Botones: Generar prompt y marcar como hecho/subir MP3 -->
    <button onclick="generatePrompt()">Generar Prompt e Instrucciones</button>
    <button onclick="toggleUploadForm()">Marcar como Hecho / Subir MP3</button>

    <!-- Formulario de subida (se muestra al marcar como hecho) -->
    <div id="uploadFormContainer">
      <form action="index.php" method="post" enctype="multipart/form-data">
        <!-- Se enviará el número de prompt seleccionado -->
        <input type="hidden" name="promptId" id="promptIdField" value="">
        <label for="mp3file">Selecciona tu archivo MP3:</label><br>
        <input type="file" name="mp3file" id="mp3file" accept=".mp3" required><br><br>
        <button type="submit">Subir MP3</button>
      </form>
    </div>
    
    <br>
    <!-- Enlace a la galería de MP3 subidos -->
    <a href="gallery.php" target="_blank">Ver MP3 subidos</a>
  </div>

  <script>
    // Variable global para guardar el ID del prompt actual
    let currentPromptId = 0;
    // Array de prompts con un número (id), prompt y sus instrucciones
    const promptData = [
      { id: 1, prompt: "Diseña una pista que evolucione a partir de un solo sample.", instructions: "Importa un sample a Ableton y colócalo en la vista Arrangement. Aplica efectos de granular synthesis y automatiza parámetros como pitch y reverb para crear variaciones progresivas." },
      { id: 2, prompt: "Crea una melodía hipnótica usando escalas menores.", instructions: "Utiliza un sintetizador para programar una secuencia MIDI en una escala menor. Automatiza parámetros como cutoff y resonancia para darle un efecto hipnótico a la melodía." },
      { id: 3, prompt: "Experimenta con loops polirrítmicos en la vista Session.", instructions: "Crea varios clips MIDI con patrones rítmicos distintos y dispártalos en la vista Session. Ajusta la cuantización para mantener la sincronía mientras exploras combinaciones polirrítmicas." },
      { id: 4, prompt: "Construye un sonido orgánico combinando instrumentos virtuales y grabaciones en vivo.", instructions: "Graba sonidos en vivo (por ejemplo, percusión o voz) y combínalos con sintetizadores virtuales. Aplica efectos de compresión, ecualización y reverb para integrar los sonidos." },
      { id: 5, prompt: "Desarrolla un remix utilizando clips de audio y MIDI.", instructions: "Importa elementos de una pista original, divídelos en clips y reorganízalos en la vista Session. Experimenta con efectos y automatizaciones para crear un remix único." },
      { id: 6, prompt: "Genera una pista de ambient utilizando modulación de efectos en tiempo real.", instructions: "Crea una base con pads y texturas. Utiliza LFOs y envolventes para modular efectos como delay y reverb, creando una atmósfera que evoluciona en tiempo real." },
      { id: 7, prompt: "Diseña un groove funk con líneas de bajo sincopadas.", instructions: "Programa un patrón de bajo con acentos sincopados en una pista MIDI. Añade guitarras o teclados funky y utiliza efectos de compresión y saturación para reforzar el groove." },
      { id: 8, prompt: "Crea una pista de dubstep utilizando técnicas de Bass modulation.", instructions: "Elige un sintetizador para generar líneas de bajo 'wobbly'. Programa un patrón rítmico y automatiza el LFO para conseguir el característico efecto dubstep." },
      { id: 9, prompt: "Desarrolla un tema de piano minimalista con un toque nostálgico.", instructions: "Graba o programa una línea de piano en una pista MIDI. Aplica efectos de reverb y delay, y automatiza el volumen para resaltar momentos de silencio y reflexión." },
      { id: 10, prompt: "Crea una composición experimental utilizando granular synthesis.", instructions: "Importa un sample a Ableton y aplica un plugin de granular synthesis. Experimenta con la densidad, tamaño y posición de las partículas para transformar el sonido original." },
      { id: 11, prompt: "Construye una pista electrónica inspirada en el techno industrial.", instructions: "Crea un beat repetitivo y añade elementos rítmicos oscuros. Experimenta con efectos de distorsión, compresión y ecualización para conseguir un sonido mecánico y contundente." },
      { id: 12, prompt: "Genera una capa de ambiente con field recordings y efectos atmosféricos.", instructions: "Graba sonidos ambientales y cárgalos en una pista de audio. Aplica reverb, delay y filtros, y automatiza parámetros para transformar el ambiente durante la pista." },
      { id: 13, prompt: "Diseña una pista de deep house con líneas de bajo envolventes.", instructions: "Programa un patrón de bajo profundo y añade acordes suaves en un sintetizador. Usa ecualización y reverb para lograr una sensación de profundidad y groove característico." },
      { id: 14, prompt: "Crea una composición inspirada en el jazz fusion utilizando samples y improvisación.", instructions: "Graba improvisaciones en vivo o utiliza samples de instrumentos jazzísticos. Mezcla ambos elementos, aplica delay y chorus para fusionarlos de manera creativa." },
      { id: 15, prompt: "Experimenta con la técnica del sidechain en un ritmo chill-out.", instructions: "Aplica compresión sidechain en una pista de pads o sintetizadores usando el kick como disparador. Ajusta el compresor para que el sonido 'respire' con el beat." },
      { id: 16, prompt: "Construye una pista con cambios de clave y escalas a mitad de composición.", instructions: "Crea dos secciones con diferentes escalas o modos musicales. Utiliza la vista Arrangement para hacer transiciones suaves y automatiza efectos para resaltar el cambio." },
      { id: 17, prompt: "Crea una composición ambiental con texturas rítmicas orgánicas.", instructions: "Utiliza grabaciones de sonidos naturales en una pista de audio, aplica reverb y delay, y ajusta el timing con Warp para generar patrones rítmicos orgánicos." },
      { id: 18, prompt: "Diseña un remix experimental de un tema clásico.", instructions: "Importa elementos de una canción conocida, divídelos en clips y reorganízalos en la vista Session. Aplica efectos como distorsión, granular synthesis y automatizaciones creativas." },
      { id: 19, prompt: "Crea un loop de sintetizador que evolucione progresivamente.", instructions: "Programa un loop MIDI en un sintetizador y automatiza parámetros como cutoff, resonancia y modulación. Experimenta con distintos presets y guarda las variaciones que te gusten." },
      { id: 20, prompt: "Experimenta con la función 'Session Record' para capturar ideas espontáneas.", instructions: "Activa la grabación en la vista Session y toca libremente. Una vez grabado, organiza los clips en la vista Arrangement y edítalos para refinar tu idea inicial." },
      { id: 21, prompt: "Crea un tema ambient con capas superpuestas y automatizaciones sutiles.", instructions: "Programa o graba varios pads y texturas. Utiliza efectos de delay y reverb, y automatiza volúmenes para que las capas se fundan gradualmente durante la pista." },
      { id: 22, prompt: "Diseña una pista de trance utilizando arpegios y secuencias pulsantes.", instructions: "Utiliza un sintetizador con arpegiador, programa un patrón repetitivo y añade efectos como delay y reverb. Automatiza la intensidad del arpegio para generar un movimiento constante." },
      { id: 23, prompt: "Crea una pista inspirada en el future bass combinando sintetizadores y samples vocales.", instructions: "Programa acordes y líneas melódicas en un sintetizador, integra samples vocales y utiliza efectos como reverb, delay y sidechain compression para un sonido moderno y dinámico." },
      { id: 24, prompt: "Desarrolla una pista con un tema melancólico utilizando acordes suspendidos.", instructions: "Crea progresiones de acordes que incluyan acordes suspendidos y menores. Añade una línea de bajo sutil y aplica reverb y delay para enfatizar la atmósfera melancólica." },
      { id: 25, prompt: "Crea una pista experimental que juegue con la alternancia de géneros.", instructions: "Divide la pista en secciones y asigna a cada una un estilo distinto (por ejemplo, electrónica, rock, ambient). Usa transiciones abruptas y automatiza efectos para unir las secciones de forma creativa." },
      { id: 26, prompt: "Construye una pista minimalista centrada en el ritmo y la percusión.", instructions: "Programa un patrón de percusión sencillo en la vista Session. Deja espacios vacíos y utiliza efectos de delay y reverb para resaltar la simplicidad del ritmo." },
      { id: 27, prompt: "Crea un tema inspirador utilizando sonidos de la naturaleza y sintetizadores etéreos.", instructions: "Graba sonidos naturales y combínalos con pads de sintetizador. Aplica reverb y ecualización para lograr una atmósfera inspiradora y orgánica." },
      { id: 28, prompt: "Diseña una pista que combine influencias del funk y la electrónica.", instructions: "Programa líneas de bajo funky, añade acordes en un sintetizador y utiliza efectos como filtro y phaser. Edita la pista en Arrangement para crear transiciones fluidas." },
      { id: 29, prompt: "Genera una pista de synthwave con estética retro y sonidos analógicos.", instructions: "Utiliza sintetizadores que emulen sonidos vintage, crea un beat retro y añade efectos de chorus y reverb. Automatiza parámetros para lograr un ambiente nostálgico y futurista." },
      { id: 30, prompt: "Experimenta con la función 'Consolidate' para crear loops perfectos.", instructions: "Graba una secuencia en la vista Session, utiliza 'Consolidate' para convertirla en un clip y ajusta los marcadores Warp para perfeccionar la sincronía del loop." },
      { id: 31, prompt: "Crea una pista ambient con sonidos modulados por CV.", instructions: "Si tienes un sintetizador compatible con control CV, graba una interpretación y utiliza el control CV para modular parámetros. Integra estos sonidos en tu proyecto para obtener texturas únicas." },
      { id: 32, prompt: "Diseña un tema experimental utilizando dispositivos de Max for Live.", instructions: "Explora dispositivos de Max for Live para generar efectos inesperados. Automatiza parámetros y combina varios dispositivos para crear una composición experimental." },
      { id: 33, prompt: "Construye una pista con influencias del EDM y el pop, enfocándote en un 'drop' poderoso.", instructions: "Programa secciones de build-up y drop. Automatiza efectos y volúmenes para incrementar la tensión, y en el drop, combina percusión intensa con sintetizadores brillantes." },
      { id: 34, prompt: "Crea un ambiente sonoro utilizando la técnica del 'stutter'.", instructions: "Selecciona un sample o clip, corta el audio en pequeñas secciones y repítelas rápidamente. Automatiza la repetición para conseguir un efecto rítmico y experimental." },
      { id: 35, prompt: "Desarrolla una pista con un tema de esperanza y optimismo mediante acordes mayores.", instructions: "Programa progresiones de acordes mayores, añade una línea melódica optimista y utiliza efectos sutiles de delay y reverb para resaltar la energía positiva." },
      { id: 36, prompt: "Crea una composición utilizando exclusivamente sonidos generados en tiempo real.", instructions: "Utiliza instrumentos virtuales y la función 'Session Record' para capturar improvisaciones en vivo. Selecciona los mejores momentos y organízalos en Arrangement para formar una composición coherente." },
      { id: 37, prompt: "Experimenta con la edición en Arrangement para crear una narrativa musical.", instructions: "Graba diferentes secciones en la vista Session, arrástralas a Arrangement y edítalas para contar una historia musical, utilizando automatizaciones para mejorar la fluidez." },
      { id: 38, prompt: "Crea una pista con un enfoque en la percusión electrónica y ritmo sincopado.", instructions: "Programa patrones de percusión en una pista MIDI, ajusta la cuantización y añade swing. Incorpora capas adicionales de percusión para enriquecer el groove." },
      { id: 39, prompt: "Diseña un tema que combine improvisación en vivo con loops pregrabados.", instructions: "Graba improvisaciones en vivo y crea loops en la vista Session. Combina ambos elementos en Arrangement y utiliza automatización para lograr cohesión entre las secciones." },
      { id: 40, prompt: "Crea una pista experimental utilizando efectos de modulación en cadena.", instructions: "Configura una cadena de efectos en una pista y automatiza cada dispositivo. Experimenta con la modulación para transformar el sonido base en algo novedoso y creativo." }
    ];

    // Función para generar un prompt aleatorio
    function generatePrompt() {
      const randomIndex = Math.floor(Math.random() * promptData.length);
      const selected = promptData[randomIndex];
      currentPromptId = selected.id;
      // Muestra en pantalla el número del prompt junto con el texto
      document.getElementById('promptDisplay').innerText = "Prompt #" + selected.id + ": " + selected.prompt;
      document.getElementById('instructionDisplay').innerText = selected.instructions;
      // Actualiza el valor del input oculto para la subida
      document.getElementById('promptIdField').value = selected.id;
      // Oculta el formulario de subida (en caso de que estuviera visible de una sesión anterior)
      document.getElementById('uploadFormContainer').style.display = 'none';
    }

    // Función para mostrar u ocultar el formulario de subida
    function toggleUploadForm() {
      const uploadDiv = document.getElementById('uploadFormContainer');
      if (uploadDiv.style.display === 'none' || uploadDiv.style.display === '') {
        uploadDiv.style.display = 'block';
      } else {
        uploadDiv.style.display = 'none';
      }
    }
  </script>
</body>
</html>
