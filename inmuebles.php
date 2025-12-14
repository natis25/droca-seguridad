<?php include('header.php'); ?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Viviendas Disponibles</title>
  
  <!-- Bootstrap -->
  <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

  <!-- Fuentes Google -->
  <link href="https://fonts.googleapis.com/css2?family=Overlock:wght@700&family=Roboto:wght@300&display=swap" rel="stylesheet">

  <style>
    body {
      background-color: #FFFFFF;
      color: #000000;
      font-family: 'Roboto', sans-serif;
    }

    h1 {
      font-family: 'Overlock', serif;
      font-weight: 700;
      text-align: center;
      margin: 20px 0;
    }

    .grid-container {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 10px;
      padding: 10px;
    }

    .card {
      border: 1px solid #dddddd;
      padding: 10px;
      text-align: left;
      position: relative;
    }

    .card h2 {
      font-weight: bold;
      font-size: 1.2em;
    }

    .card p {
      margin: 5px 0;
    }

    .modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.5);
      justify-content: center;
      align-items: center;
      z-index: 1000;
    }

    .modal-content {
      background-color: #fff;
      padding: 20px;
      border-radius: 10px;
      width: 300px;
      position: relative;
    }

    .close-btn {
      position: absolute;
      top: 10px;
      right: 10px;
      cursor: pointer;
      font-size: 1.5em;
      font-weight: bold;
      color: #555;
    }

    .form-group {
      margin-bottom: 15px;
    }
  </style>
</head>
<body>

  <hr>

  <div class="grid-container">
  <?php
include('Logica/sql.php'); 

$viviendas = obtenerViviendasDisponibles();

if (!empty($viviendas)) {
    foreach ($viviendas as $index => $fila) {
        echo "<div class='card'>";
        echo "<h2>{$fila['Direccion']}</h2>";
        echo "<p><strong>Tipo:</strong> {$fila['TipoVivienda']}</p>";
        echo "<p><strong>Zona:</strong> {$fila['Zona']}</p>";
        echo "<p><strong>Monto:</strong> \${$fila['MontoPedido']}</p>";
        echo "<p><strong>Estado:</strong> Disponible</p>";
        echo "<p><strong>Oferta:</strong> {$fila['TipoOferta']}</p>";
        echo "<button class='btn btn-primary' onclick='openModal($index)'>Agendar Cita</button>";
        echo "</div>";

        // Modal para agendar cita
        echo "
        <div class='modal' id='modal-$index'>
          <div class='modal-content'>
            <span class='close-btn' onclick='closeModal($index)'>&times;</span>
            <h3>Agendar Cita</h3>
            <form method='POST' action='Logica/setCita.php' id='form-$index'>
              <div class='form-group'>
                <label for='nombre-$index'>Nombre:</label>
                <input type='text' class='form-control' id='nombre-$index' name='nombre' required>
              </div>
              <div class='form-group'>
                <label for='correo-$index'>Correo:</label>
                <input type='email' class='form-control' id='correo-$index' name='correo' required>
              </div>
              <div class='form-group'>
                <label for='telefono-$index'>Teléfono:</label>
                <input type='tel' class='form-control' id='telefono-$index' name='telefono' required>
              </div>
              <div class='form-group'>
                <label for='fecha-$index'>Fecha de la cita:</label>
                <input type='date' class='form-control' id='fecha-$index' name='fecha' required>
              </div>
              <script>
              document.getElementById('fecha-<?php echo $index; ?>').setAttribute('min', new Date().toISOString().split('T')[0]);
              </script>
              <div class='form-group'>
                <label for='hora_inicio-$index'>Hora de inicio:</label>
                <input type='time' class='form-control' id='hora_inicio-$index' name='hora_inicio' required>
              </div>
              <input type='hidden' name='idVivienda' value='{$fila['idVivienda']}'>
              <button type='submit' class='btn btn-success' id='submit-$index' disabled>Agendar</button>
            </form>
          </div>
        </div>";
    }
} else {
    echo "<p>No hay viviendas disponibles en este momento.</p>";
}
?>

  </div>

  <script>
    function validarFormulario(index) {
        const formulario = document.getElementById(`form-${index}`);
        const botonEnviar = document.getElementById(`submit-${index}`);
        const esValido = formulario.checkValidity();
        botonEnviar.disabled = !esValido;
    }

    document.querySelectorAll('form').forEach((form, index) => {
        form.querySelectorAll('input').forEach((input) => {
            input.addEventListener('input', () => validarFormulario(index));
        });
    });

    window.addEventListener('load', () => {
        const fechaInputs = document.querySelectorAll('input[type="date"]');
        fechaInputs.forEach((input) => {
            input.setAttribute('min', new Date().toISOString().split('T')[0]);
        });
    });

    function openModal(index) {
      document.getElementById(`modal-${index}`).style.display = 'flex';
    }

    function closeModal(index) {
      document.getElementById(`modal-${index}`).style.display = 'none';
    }
  </script>

</body>
</html>
