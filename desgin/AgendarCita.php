<?php
require_once __DIR__ . '/../vendor/autoload.php';

include '../configBD.php'; // Asegúrate de tener este archivo que contiene la conexión a la base de datos

session_start();

// Función para mostrar mensajes de tostada con tamaño de letra
function mostrarMensaje($mensaje, $tipo, $fontSize) {
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            agregarToast({ tipo: '$tipo', titulo: 'Mensaje', descripcion: '<span style=\"font-size: $fontSize;\">$mensaje</span>', autoCierre: true });
        });
    </script>";
}

if (!isset($_SESSION['user_id'])) {
    header('Location: /CITAS/iniciarSe.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Obtener información del paciente
$select_paciente = mysqli_query($conn, "SELECT * FROM `paciente` WHERE Id_Usu_Pac_FK = '$user_id'") or die('query failed');
$paciente = mysqli_fetch_assoc($select_paciente);

// Obtener información del usuario
$select_user = mysqli_query($conn, "SELECT Img_Usu FROM `usuario` WHERE Id_Usu = '$user_id'") or die('query failed');
$fetch_user = mysqli_fetch_assoc($select_user);
$profile_image = $fetch_user['Img_Usu'];

// Procesar el formulario cuando se envíe
if (isset($_POST['submit'])) {
    $servicio = mysqli_real_escape_string($conn, $_POST['servicio']);
    $fecha = mysqli_real_escape_string($conn, $_POST['fecha']);
    $hora = mysqli_real_escape_string($conn, $_POST['hora']);

    // Verificar disponibilidad de la fecha y hora
    $check_availability = mysqli_query($conn, "SELECT * FROM `cita` WHERE Fecha_Cita = '$fecha' AND Hora_Cita = '$hora'") or die('query failed');

    if (mysqli_num_rows($check_availability) > 0) {
        $error_message = "La fecha y hora seleccionadas no están disponibles. Por favor, elija otra fecha u hora.";
        mostrarMensaje($error_message, 'error', '16px'); // Mensaje de error con tamaño de letra de 16px
    } else {
        // Obtener el tratamiento seleccionado
        $select_tratamiento = mysqli_query($conn, "SELECT Id_Trat FROM `tratamiento` WHERE Nombre_Trat = '$servicio'") or die('query failed');
        $tratamiento = mysqli_fetch_assoc($select_tratamiento);
        $tratamiento_id = $tratamiento['Id_Trat'];

        // Obtener un doctor aleatorio
        $select_doctores = mysqli_query($conn, "SELECT Id_Doc FROM `doctor`") or die('query failed');
        $doctores = [];
        while ($row = mysqli_fetch_assoc($select_doctores)) {
            $doctores[] = $row['Id_Doc'];
        }
        $doctor_id = $doctores[array_rand($doctores)];

        // Insertar la cita en la base de datos
        $insert_cita = mysqli_query($conn, "INSERT INTO `cita` (Fecha_Cita, Hora_Cita, Asistencia_Cita, Mensaje_Cita, Id_Paciente_FK, Id_Doc_FK, Id_Trat_FK) VALUES ('$fecha', '$hora', 'pendiente', '', '{$paciente['Id_Paciente']}', '$doctor_id', '$tratamiento_id')") or die('query failed');

        if ($insert_cita) {
            // Obtener el nombre y teléfono del paciente
            $telefono = $paciente['Telefono_Pac'];
            $nombre = $paciente['Nombre_Pac'];

            // Enviar mensaje de confirmación de cita
            $mensaje = "Hola $nombre, su cita para el servicio de $servicio ha sido agendada para el $fecha a las $hora.";
            enviarMensaje("52".$telefono, $mensaje);

            // Operación exitosa
            mostrarMensaje("Cita agendada exitosamente.", 'exito', '16px'); // Mensaje de éxito con tamaño de letra de 16px
        } else {
            // Error al agendar la cita
            mostrarMensaje("Hubo un error al agendar la cita. Por favor, inténtelo de nuevo.", 'error', '16px'); // Mensaje de error con tamaño de letra de 16px
        }
    }
}

function enviarMensaje($telefono, $mensaje) {
    $request = new HTTP_Request2();
    $request->setUrl('https://e1njn2.api.infobip.com/sms/2/text/advanced');
    $request->setMethod(HTTP_Request2::METHOD_POST);
    $request->setConfig(array(
        'follow_redirects' => TRUE
    ));
    $request->setHeader(array(
        'Authorization' => 'App f2ab1e85a231ab879033076da787ac82-d9c89747-1932-4e6e-8b65-6769063d55e5',
        'Content-Type' => 'application/json',
        'Accept' => 'application/json'
    ));
    $body = json_encode([
        'messages' => [
            [
                'destinations' => [
                    ['to' => $telefono]
                ],
                'from' => 'ServiceSMS',
                'text' => $mensaje
            ]
        ]
    ]);
    $request->setBody($body);

    try {
        $response = $request->send();
        if ($response->getStatus() != 200) {
            echo 'Unexpected HTTP status: ' . $response->getStatus() . ' ' .
                $response->getReasonPhrase();
        }
    } catch (HTTP_Request2_Exception $e) {
        echo 'Error: ' . $e->getMessage();
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agendar Cita</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="../assets/css/noti.css">
    <link rel="icon" href="../assets/images/logo.png" type="image/png">
</head>

<body>
    <header class="header">
        <section class="flex">
            <a href="VerCitas.php" class="logo">Coffee & Medical Care</a>
            <div class="icons">
                <div id="menu-btn" class="fas fa-bars"></div>
                <div id="search-btn" class="fas fa-search"></div>
                <div id="user-btn" class="fas fa-user"></div>
                <div id="toggle-btn" class="fas fa-sun"></div>
            </div>
            <div class="profile">
                <img src="<?php echo !empty($profile_image) ? 'uploaded_img/' . $profile_image : 'img/default_profile.png'; ?>" alt="Profile Image" class="profile-image" style="width: 100px; height: 100px; border-radius: 50%;">
                <h3 class="name"><?php echo $paciente['Nombre_Pac'] . ' ' . $paciente['Apellidos_Pac']; ?></h3>
                <p class="role">Paciente</p>
                <a href="Perfil.php" class="btn">Ver Perfil</a>
                <div class="flex-btn">
                <a href="logout.php" class="option-btn">Cerrar Sesion</a>
                </div>
            </div>
        </section>
    </header>

    <div class="side-bar">
        <div id="close-btn">
            <i class="fas fa-times"></i>
        </div>
        <div class="profile">
            <img src="<?php echo !empty($profile_image) ? 'uploaded_img/' . $profile_image : 'img/default_profile.png'; ?>" alt="Profile Image" class="profile-image" style="width: 100px; height: 100px; border-radius: 50%;">
            <h3 class="name"><?php echo $paciente['Nombre_Pac'] . ' ' . $paciente['Apellidos_Pac']; ?></h3>
            <p class="role">Paciente</p>
            <a href="Perfil.php" class="btn">Ver perfil</a>
        </div>
        <nav class="navbar">
            <a href="VerCitas.php"><i class="fas fas fa-search"></i><span>Ver Citas</span></a>
            <a href="AgendarCita.php"><i class="fas fa-sun"></i><span>Agendar Cita</span></a>
            <a href="ListaRegistro.php"><i class="fas fa-chalkboard-user"></i><span>Lista de registros</span></a>
            <a href="HistorialMedico.php"><i class="fas fa-user"></i><span>Historial Medico</span></a>
        </nav>
    </div>

    <section class="courses">
        <h1 class="heading">Agendar Cita</h1>
        <section class="form-container">
            <form action="" method="post" enctype="multipart/form-data">
                <h3>Agendar Cita Medica</h3>
                <p>Selecciona un servicio</p>
                <div class="form-group">
                    <label for="servicio"></label>
                    <select class="custom-select" id="servicio" name="servicio" required>
                        <option value="" selected>Elige...</option>
                        <option value="Limpieza dental">Limpieza dental</option>
                        <option value="Carillas">Carillas</option>
                        <option value="Odontopediatria">Odontopediatria</option>
                        <option value="Extraccion Dental">Extraccion Dental</option>
                        <option value="Endodoncia">Endodoncia</option>
                        <option value="Restauracion de resina">Restauracion de resina</option>
                        <option value="Periodoncia">Periodoncia</option>
                    </select>
                </div>
                <p>Fecha</p>
                <label for=""></label>
                <input type="date" class="form-control" id="fecha" name="fecha" required>
                <p>Hora</p>
                <label for="hora"></label>
                <select class="form-control" id="hora" name="hora" required>
                    <option value="" selected>Elige Hora</option>
                    <option value="09:00">09:00 AM</option>
                    <option value="10:00">10:00 AM</option>
                    <option value="11:00">11:00 AM</option>
                    <option value="14:00">02:00 PM</option>
                    <option value="15:00">03:00 PM</option>
                    <option value="16:00">04:00 PM</option>
                </select>
                <input type="submit" value="Agendar Cita" name="submit" class="btn">
            </form>
        </section>
    </section>

    <!-- custom js file link  -->
    <script src="js/script.js"></script>

    <div class="contenedor">
        <div class="contenedor-toast" id="contenedor-toast">
        </div>
    </div>

    <script src="../assets/js/script_Noti.js"></script>

</body>

</html>
