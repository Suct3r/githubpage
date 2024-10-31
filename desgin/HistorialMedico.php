<?php
include 'configBD.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: Location: /CITAS/iniciarSe.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Función para obtener los datos del paciente
function getPacienteData($conn, $user_id) {
    $select_paciente = mysqli_query($conn, "SELECT * FROM `paciente` WHERE Id_Usu_Pac_FK = '$user_id'") or die('query failed');
    if (mysqli_num_rows($select_paciente) > 0) {
        return mysqli_fetch_assoc($select_paciente);
    } else {
        die('No se encontró el paciente.');
        $toastMessage = 'No se encontró el paciente.';
        $toastType = 'error';
    }
}

// Función para obtener los datos del historial médico
function getHistorialData($conn, $paciente_id) {
    $select_historial = mysqli_query($conn, "SELECT * FROM `historial_medico` WHERE Id_Paciente_FK = '$paciente_id'") or die('query failed');
    if (mysqli_num_rows($select_historial) > 0) {
        return mysqli_fetch_assoc($select_historial);
    } else {
        return [
            'LugarNaciem_HisMed' => '',
            'TipSangre_His' => '',
            'Habito_Salud_HisMed' => '',
            'Enfermedad_His' => '',
            'Alergias_His' => ''
        ];
    }
}

// Obtener los datos del paciente
$fetch_paciente = getPacienteData($conn, $user_id);
$paciente_id = $fetch_paciente['Id_Paciente'];
$fetch_historial = getHistorialData($conn, $paciente_id);

// Obtener la imagen del usuario
$select_user = mysqli_query($conn, "SELECT Img_Usu FROM `usuario` WHERE Id_Usu = '$user_id'") or die('query failed');
$fetch_user = mysqli_fetch_assoc($select_user);
$profile_image = $fetch_user['Img_Usu'];

if (isset($_POST['update_profile'])) {

    // Obtener los datos actualizados del historial médico desde el formulario
    $update_lugar_nacimiento = mysqli_real_escape_string($conn, $_POST['update_lugar_nacimiento']);
    $update_tipo_sangre = mysqli_real_escape_string($conn, $_POST['update_tipo_sangre']);
    $update_habitos = mysqli_real_escape_string($conn, $_POST['update_habitos']);
    $update_enfermedades = mysqli_real_escape_string($conn, $_POST['update_enfermedades']);
    $update_alergias = mysqli_real_escape_string($conn, $_POST['update_alergias']);

    // Verificar si existe un registro en la tabla 'historial_medico' para este paciente
    $select_historial = mysqli_query($conn, "SELECT * FROM `historial_medico` WHERE Id_Paciente_FK = '$paciente_id'") or die('query failed');
    if (mysqli_num_rows($select_historial) > 0) {
        // Actualizar los datos del historial médico en la tabla 'historial_medico'
        mysqli_query($conn, "UPDATE `historial_medico` SET 
            LugarNaciem_HisMed = '$update_lugar_nacimiento',
            TipSangre_His = '$update_tipo_sangre',
            Habito_Salud_HisMed = '$update_habitos',
            Enfermedad_His = '$update_enfermedades',
            Alergias_His = '$update_alergias'
        WHERE Id_Paciente_FK = '$paciente_id'") or die('query failed');
    } else {
        // Insertar nuevos datos en la tabla 'historial_medico'
        mysqli_query($conn, "INSERT INTO `historial_medico` (LugarNaciem_HisMed, TipSangre_His, Habito_Salud_HisMed, Enfermedad_His, Alergias_His, Id_Paciente_FK) VALUES 
            ('$update_lugar_nacimiento', '$update_tipo_sangre', '$update_habitos', '$update_enfermedades', '$update_alergias', '$paciente_id')") or die('query failed');
    }


    // Obtener nuevamente los datos actualizados del paciente y del historial médico
    $fetch_paciente = getPacienteData($conn, $user_id);
    $fetch_historial = getHistorialData($conn, $paciente_id);

    // Obtener nuevamente la imagen del usuario
    $select_user = mysqli_query($conn, "SELECT Img_Usu FROM `usuario` WHERE Id_Usu = '$user_id'") or die('query failed');
    $fetch_user = mysqli_fetch_assoc($select_user);
    $profile_image = $fetch_user['Img_Usu'];

    $toastMessage = 'Perfil actualizado correctamente!';
    $toastType = 'exito';
    header('Location: ../desgin/HistorialMedico.php');

    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            agregarToast({ tipo: '$toastType', titulo: 'Mensaje', descripcion: '$toastMessage', autoCierre: true });
        });
    </script>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial Medico</title>
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
            <h3 class="name"><?php echo $fetch_paciente['Nombre_Pac'] . ' ' . $fetch_paciente['Apellidos_Pac']; ?></h3>
            <p class="role">Paciente</p>
            <a href="Perfil.php" class="btn">Ver Perfil</a>
            <div class="flex-btn">
               <a href="logout.php" class="option-btn">Cerrar Sesion</a>
            </div>
         </div>
      </section>
   </header>
   <section class="courses">
      <h1 class="heading">Historial Medico</h1>
      <section class="form-container">
         <form action="" method="post" enctype="multipart/form-data">
            <h3>Historial Medico</h3>
            <p>Lugar de Nacimiento</p>
            <input type="text" name="update_lugar_nacimiento" value="<?php echo $fetch_historial['LugarNaciem_HisMed']; ?>" maxlength="50" class="box">
            <p>Tipo de sangre</p>
            <input type="text" name="update_tipo_sangre" value="<?php echo $fetch_historial['TipSangre_His']; ?>" maxlength="50" class="box">
            <p>Hábitos</p>
            <input type="text" name="update_habitos" value="<?php echo $fetch_historial['Habito_Salud_HisMed']; ?>"  maxlength="50" class="box">
            <p>Enfermedades</p>
            <input type="text" name="update_enfermedades" value="<?php echo $fetch_historial['Enfermedad_His']; ?>"  maxlength="20" class="box">
            <p>Alergias</p>
            <input type="text" name="update_alergias" value="<?php echo $fetch_historial['Alergias_His']; ?>"  maxlength="20" class="box">
            <input type="submit" name="update_profile" value="Actualizar" class="inline-btn">
         </form>
      </section>
   </section>
   <div class="side-bar">
      <div id="close-btn">
         <i class="fas fa-times"></i>
      </div>
      <div class="profile">
         <img src="<?php echo !empty($profile_image) ? 'uploaded_img/' . $profile_image : 'img/default_profile.png'; ?>" alt="Profile Image" class="profile-image" style="width: 100px; height: 100px; border-radius: 50%;">
         <h3 class="name"><?php echo $fetch_paciente['Nombre_Pac'] . ' ' . $fetch_paciente['Apellidos_Pac']; ?></h3>
         <p class="role">Paciente</p>
         <a href="Perfil.php" class="btn">Ver Perfil</a>
      </div>
      <nav class="navbar">
         <a href="VerCitas.php"><i class="fas fa-search"></i><span>Ver Citas</span></a>         
         <a href="AgendarCita.php"><i class="fas fa-sun"></i><span>Agendar Cita</span></a>
         <a href="ListaRegistro.php"><i class="fas fa-chalkboard-user"></i><span>Lista de registros</span></a>
         <a href="HistorialMedico.php"><i class="fas fa-user"></i><span>Historial Medico</span></a>
      </nav>
   </div>
   <!-- custom js file link  -->
   <script src="js/script.js"></script>
   <div class="contenedor">
      <div class="contenedor-toast" id="contenedor-toast">
      </div>
   </div>
   <script src="../assets/js/script_Noti.js"></script>
</body>
</html>
