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
    // Obtener los datos actualizados del paciente desde el formulario
    $update_name = mysqli_real_escape_string($conn, $_POST['update_name']);
    $update_apellidos = mysqli_real_escape_string($conn, $_POST['update_apellidos']);
    $update_direccion = mysqli_real_escape_string($conn, $_POST['update_direccion']);
    $update_telefono = mysqli_real_escape_string($conn, $_POST['update_telefono']);
    $update_fecha_nacimiento = mysqli_real_escape_string($conn, $_POST['update_fecha_nacimiento']);
    $update_edad = mysqli_real_escape_string($conn, $_POST['update_edad']);

    // Actualizar los datos del paciente en la tabla 'paciente'
    mysqli_query($conn, "UPDATE `paciente` SET 
        Nombre_Pac = '$update_name', 
        Apellidos_Pac = '$update_apellidos', 
        Direccion_Pac = '$update_direccion', 
        Telefono_Pac = '$update_telefono',
        FechaNac_Pac = '$update_fecha_nacimiento', 
        Edad_Pac = '$update_edad'
    WHERE Id_Usu_Pac_FK = '$user_id'") or die('query failed');

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

    // Actualizar la imagen del paciente si se proporcionó una nueva
    $update_image = $_FILES['update_image']['name'];
    $update_image_size = $_FILES['update_image']['size'];
    $update_image_tmp_name = $_FILES['update_image']['tmp_name'];
    $update_image_folder = 'uploaded_img/' . $update_image;

    if (!empty($update_image)) {
        if ($update_image_size > 2000000) {
            echo 'La imagen es demasiado grande';
            $toastMessage = 'La imagen es demasiado grande';
            $toastType = 'error';
        } else {
            // Actualizar la imagen en la tabla 'usuario'
            $image_update_query = mysqli_query($conn, "UPDATE `usuario` SET Img_Usu = '$update_image' WHERE Id_Usu = '$user_id'") or die('query failed');
            if ($image_update_query) {
                move_uploaded_file($update_image_tmp_name, $update_image_folder);
            }
            $toastMessage = 'Imagen actualizada correctamente!';
            $toastType = 'exito';
        }
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    agregarToast({ tipo: '$toastType', titulo: 'Mensaje', descripcion: '$toastMessage', autoCierre: true });
                });
            </script>";
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
    header('Location: desgin/VerCitas.php');

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
    <title>Actualizar Perfil</title>
    <link rel="stylesheet" href="css/style2.css">
    <link rel="icon" href="../citas/assets/images/logo.png" type="image/png">
    <link rel="stylesheet" href="assets/css/noti.css">
    <style>
        .profile-image-container {
            text-align: center;
            margin-bottom: 20px;
        }
        .profile-image {
            width: 200px;
            height: 200px;
            border-radius: 50%;
            object-fit: cover;
            background-color: #ccc; /* Color gris como marcador de posición */
            display: inline-block;
        }
    </style>
</head>
<body>
<div class="update-profile">
    <form action="" method="post" enctype="multipart/form-data">
        <div class="profile-image-container">
            <img src="<?php echo !empty($profile_image) ? 'uploaded_img/' . $profile_image : 'img/default_profile.png'; ?>" alt="Profile Image" class="profile-image">
        </div>

        <p style="text-align: center; font-size: 22px; font-weight: bold;">Datos Generales</p>
        <div class="flex">
            <div class="inputBox">
                <span>Nombre :</span>
                <input type="text" name="update_name" value="<?php echo $fetch_paciente['Nombre_Pac']; ?>" class="box">
                <span>Apellidos :</span>
                <input type="text" name="update_apellidos" value="<?php echo $fetch_paciente['Apellidos_Pac']; ?>" class="box">
                <span>Direccion :</span>
                <input type="text" name="update_direccion" value="<?php echo $fetch_paciente['Direccion_Pac']; ?>" class="box">
                <span>Telefono :</span>
                <input type="text" name="update_telefono" value="<?php echo $fetch_paciente['Telefono_Pac']; ?>" class="box">
            </div>
            <div class="inputBox">
                <span>Fecha de nacimiento :</span>
                <input type="text" name="update_fecha_nacimiento" value="<?php echo $fetch_paciente['FechaNac_Pac']; ?>" class="box">
                <span>Edad :</span>
                <input type="text" name="update_edad" value="<?php echo $fetch_paciente['Edad_Pac']; ?>" class="box">
                <span>Sube una imagen :</span>
                <input type="file" name="update_image" accept="image/jpg, image/jpeg, image/png" class="box">
            </div>
        </div>

        <div style="margin-top: 40px;"></div>
        <p style="text-align: center; font-size: 22px; font-weight: bold;">Historial Medico</p>
        <div class="flex">
            <div class="inputBox">
               <span>Lugar de Nacimiento :</span>
               <input type="text" name="update_lugar_nacimiento" value="<?php echo $fetch_historial['LugarNaciem_HisMed']; ?>" class="box">
               <span>Tipo de sangre :</span>
               <input type="text" name="update_tipo_sangre" value="<?php echo $fetch_historial['TipSangre_His']; ?>" class="box">
               <span>Hábitos :</span>
               <input type="text" name="update_habitos" value="<?php echo $fetch_historial['Habito_Salud_HisMed']; ?>" class="box">
            </div>
            <div class="inputBox">
               <span>Enfermedades :</span>
               <input type="text" name="update_enfermedades" value="<?php echo $fetch_historial['Enfermedad_His']; ?>" class="box">
               <span>Alergias :</span>
               <input type="text" name="update_alergias" value="<?php echo $fetch_historial['Alergias_His']; ?>" class="box">
            </div>
        </div>

        <input type="submit" value="Actualizar Perfil" name="update_profile" class="btn">
    </form>
</div>

        <!-- ====================================================================== -->

            <div class="contenedor">
			<div class="contenedor-toast" id="contenedor-toast">
            </div>
		</div>

        <script src="assets/js/script_Noti.js"></script>
</body>
</html>
