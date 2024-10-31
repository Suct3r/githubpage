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



if (isset($_GET['id'])) {
    $cita_id = $_GET['id'];

    // Obtener información de la cita
    $select_cita = mysqli_query($conn, "SELECT * FROM `cita` WHERE Id_Cita = '$cita_id'") or die('query failed');
    $cita = mysqli_fetch_assoc($select_cita);

    // Obtener información del paciente
    $id_paciente = $cita['Id_Paciente_FK'];
    $select_paciente = mysqli_query($conn, "SELECT Nombre_Pac, Apellidos_Pac FROM paciente WHERE Id_Paciente = $id_paciente") or die('query failed');
    $paciente = mysqli_fetch_assoc($select_paciente);

    // Obtener información del tratamiento
    $id_tratamiento = $cita['Id_Trat_FK'];
    $select_tratamiento = mysqli_query($conn, "SELECT Nombre_Trat FROM tratamiento WHERE Id_Trat = $id_tratamiento") or die('query failed');
    $tratamiento = mysqli_fetch_assoc($select_tratamiento);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fecha = $_POST['fecha'];
    $hora = $_POST['hora'];
    $mensaje = $_POST['mensaje'];
    $estado = $_POST['estado'];

    // Actualizar datos de la cita
    $update_query = "UPDATE `cita` SET Fecha_Cita = '$fecha', Hora_Cita = '$hora', Mensaje_Cita = '$mensaje', Asistencia_Cita = '$estado' WHERE Id_Cita = '$cita_id'";
    if (mysqli_query($conn, $update_query)) {
        header('Location: ListaRegistro.php');
        exit;
    } else {
        $error_message = 'Ha ocurrido un error al actualizar la cita. Por favor, inténtalo de nuevo más tarde.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualizar Registro</title>
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
            <h3 class="name"><?php echo $doctor['Nombre_Doc'] . ' ' . $doctor['Apellidos_Doc']; ?></h3>
            <p class="role">Doctor</p>
            <a href="Perfil.php" class="btn">Ver Perfil</a>
            <div class="flex-btn">
                <a href="logout.php" class="option-btn">Cerrar Sesión</a>
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
        <a href="VerCitas.php"><i class="fas fa-search"></i><span>Ver Citas</span></a>
        <a href="ListaRegistro.php"><i class="fas fa-list"></i><span>Historial Citas</span></a>
    </nav>
</div>

<section class="form-container">
    <form action="" method="post">
        <h3>Actualizar Registro</h3>
        <?php if (isset($error_message)): ?>
            <p class="error"><?php echo $error_message; ?></p>
        <?php endif; ?>
        <div class="form-group">
            <p>Fecha</p>
            <input type="date" name="fecha" value="<?php echo $cita['Fecha_Cita']; ?>" class="box" required>
            <p>Hora</p>
            <input type="time" name="hora" value="<?php echo $cita['Hora_Cita']; ?>" class="box" required>
            <p>Mensaje Adicional</p>
            <input type="text" name="mensaje" value="<?php echo $cita['Mensaje_Cita']; ?>" maxlength="255" class="box">
            <p>Estado de la Cita</p>
            <select name="estado" class="box" required>
                <option value="pendiente" <?php if ($cita['Asistencia_Cita'] == 'pendiente') echo 'selected'; ?>>Pendiente</option>
                <option value="Confirmado" <?php if ($cita['Asistencia_Cita'] == 'Confirmado') echo 'selected'; ?>>Confirmado</option>
                <option value="Cancelado" <?php if ($cita['Asistencia_Cita'] == 'Cancelado') echo 'selected'; ?>>Cancelado</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Actualizar Registro</button>
    </form>
</section>

<script src="js/script.js"></script>

</body>
</html>
