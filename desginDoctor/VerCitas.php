<?php
include '../configBD.php'; // Asegúrate de tener este archivo que contiene la conexión a la base de datos

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: /CITAS/iniciarSe.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Obtener información del doctor
$select_doctor = mysqli_query($conn, "SELECT * FROM `doctor` WHERE Id_Usu_Doc_FK = '$user_id'") or die('query failed');
$doctor = mysqli_fetch_assoc($select_doctor);

// Obtener información del usuario
$select_user = mysqli_query($conn, "SELECT Img_Usu FROM `usuario` WHERE Id_Usu = '$user_id'") or die('query failed');
$fetch_user = mysqli_fetch_assoc($select_user);
$profile_image = $fetch_user['Img_Usu'];

// Obtener citas del doctor
$select_citas = mysqli_query($conn, "
    SELECT 
        cita.Fecha_Cita, 
        cita.Hora_Cita, 
        tratamiento.Nombre_Trat, 
        paciente.Nombre_Pac, 
        paciente.Apellidos_Pac
    FROM 
        `cita`
    INNER JOIN 
        `tratamiento` ON cita.Id_Trat_FK = tratamiento.Id_Trat
    INNER JOIN 
        `paciente` ON cita.Id_Paciente_FK = paciente.Id_Paciente
    WHERE 
        cita.Id_Doc_FK = '$doctor[Id_Doc]'"
) or die('query failed');
$citas = mysqli_fetch_all($select_citas, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Citas</title>

    <!-- Font Awesome CDN link -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">

    <!-- Custom CSS file link -->
    <link rel="stylesheet" href="css/style.css">

    <!-- FullCalendar CSS -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
    <link rel="icon" href="../assets/images/logo.png" type="image/png">
</head>
<body>
    <header class="header">
        <section class="flex">
            <a href="VerCitas.php" class="logo" style="font-size: 24px; font-weight: bold;">Coffee & Medical Care</a>

            <div class="icons">
                <div id="menu-btn" class="fas fa-bars"></div>
                <div id="search-btn" class="fas fa-search"></div>
                <div id="user-btn" class="fas fa-user"></div>
                <div id="toggle-btn" class="fas fa-sun"></div>
            </div>

            <div class="profile">
                <img src="<?php echo !empty($profile_image) ? 'uploaded_img/' . $profile_image : 'img/default_profile.png'; ?>" alt="Profile Image" class="profile-image" style="width: 100px; height: 100px; border-radius: 50%;">
                <h3 class="name"><?php echo $doctor['Nombre_Doc'] . ' ' . $doctor['Apellidos_Doc']; ?></h3>
                <p class="role">Doctor</p>
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
            <h3 class="name"><?php echo $doctor['Nombre_Doc'] . ' ' . $doctor['Apellidos_Doc']; ?></h3>
            <p class="role">Doctor</p>
            <a href="Perfil.php" class="btn">Ver Perfil</a>
        </div>
        <nav class="navbar">
            <a href="VerCitas.php"><i class="fas fas fa-search"></i><span>Ver Citas</span></a>
            <a href="ListaRegistro.php"><i class="fas fa-chalkboard-user"></i><span>Lista de registros</span></a>
        </nav>
    </div>

    <div class="container">
        <h1 class="heading" style="font-size: 28px; font-weight: bold; text-align: center;">Calendario</h1>

        <!-- Contenedor del calendario -->
        <div class="card">
            <div class="card-body">
                <div id="calendar"></div>
            </div>
        </div>
    </div>

    <!-- jQuery (optional, if not already included) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- FullCalendar JS -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/es.js"></script>

    <style>
        /* Ajustes de tamaño para el contenedor del calendario */
        #calendar {
            max-width: 800px;
            /* Ajusta el ancho según tus necesidades */
            margin: 0 auto;
        }

        /* Ajustes de fuente y tamaño para elementos internos del calendario */
        .fc {
            font-size: 2em;
            /* Ajusta el tamaño de fuente según tus necesidades */
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var calendarEl = document.getElementById('calendar');

            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                locale: 'es',
                events: [
                    <?php foreach ($citas as $cita): ?>
                        {
                            title: '<?php echo $cita['Nombre_Trat']; ?>',
                            start: '<?php echo $cita['Fecha_Cita']; ?>T<?php echo $cita['Hora_Cita']; ?>',
                            paciente: '<?php echo $cita['Nombre_Pac'] . ' ' . $cita['Apellidos_Pac']; ?>'
                        },
                    <?php endforeach; ?>
                ]
            });

            calendar.render();
        });
    </script>

    <!-- custom js file link -->
    <script src="js/script.js"></script>
</body>
</html>
