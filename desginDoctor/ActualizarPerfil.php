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

if (isset($_POST['update_profile'])) {
    // Obtener los datos actualizados del doctor desde el formulario
    $update_name = mysqli_real_escape_string($conn, $_POST['update_name']);
    $update_apellidos = mysqli_real_escape_string($conn, $_POST['update_apellidos']);
    $update_direccion = mysqli_real_escape_string($conn, $_POST['update_direccion']);
    $update_telefono = mysqli_real_escape_string($conn, $_POST['update_telefono']);

    // Actualizar los datos del doctor en la tabla 'doctor'
    mysqli_query($conn, "UPDATE `doctor` SET 
        Nombre_Doc = '$update_name', 
        Apellidos_Doc = '$update_apellidos', 
        Direccion_Doc = '$update_direccion', 
        Telefon_Doc = '$update_telefono'
    WHERE Id_Usu_Doc_FK = '$user_id'") or die('query failed');

    // Actualizar la imagen del doctor si se proporcionó una nueva
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
            $toastMessage = '<span style="font-size: 16px;">Imagen actualizada correctamente!</span>';
            $toastType = 'exito';
        }
        echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    agregarToast({ tipo: '$toastType', titulo: 'Mensaje', descripcion: '$toastMessage', autoCierre: true });
                });
            </script>";
    }

    // Obtener nuevamente la información del doctor
    $select_doctor = mysqli_query($conn, "SELECT * FROM `doctor` WHERE Id_Usu_Doc_FK = '$user_id'") or die('query failed');
    $doctor = mysqli_fetch_assoc($select_doctor);

    // Obtener nuevamente la imagen del usuario
    $select_user = mysqli_query($conn, "SELECT Img_Usu FROM `usuario` WHERE Id_Usu = '$user_id'") or die('query failed');
    $fetch_user = mysqli_fetch_assoc($select_user);
    $profile_image = $fetch_user['Img_Usu'];

    $toastMessage = '<span style="font-size: 16px;">Perfil actualizado correctamente!</span>';
    $toastType = 'exito';

    echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                agregarToast({ tipo: '$toastType', titulo: 'Mensaje', descripcion: '$toastMessage', autoCierre: true });
            });
        </script>";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualizar Perfil</title>

    <!-- Font Awesome CDN link -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">

    <!-- Custom CSS file link -->
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
        <a href="VerCitas.php"><i class="fas fa-search"></i><span>Ver Citas</span></a>
        <a href="ListaRegistro.php"><i class="fas fa-chalkboard-user"></i><span>Lista de registros</span></a>
    </nav>
</div>

<section class="form-container">
    <form action="" method="post" enctype="multipart/form-data">
        <h3>Actualizar Perfil</h3>
        <p>Nombre</p>
        <input type="text" name="update_name" value="<?php echo $doctor['Nombre_Doc']; ?>" class="box">
        <p>Apellidos</p>
        <input type="text" name="update_apellidos" value="<?php echo $doctor['Apellidos_Doc']; ?>" class="box">
        <p>Direccion</p>
        <input type="text" name="update_direccion" value="<?php echo $doctor['Direccion_Doc']; ?>" class="box">
        <p>Telefono</p>
        <input type="text" name="update_telefono" value="<?php echo $doctor['Telefon_Doc']; ?>" class="box">
        <p>Cambiar Foto de Perfil</p>
        <input type="file" name="update_image" accept="image/*" class="box">
        <input type="submit" value="Actualizar" name="update_profile" class="btn">
    </form>
</section>

<!-- custom js file link -->
<script src="js/script.js"></script>
<div class="contenedor">
    <div class="contenedor-toast" id="contenedor-toast">
    </div>
</div>
<script src="../assets/js/script_Noti.js"></script>
</body>
</html>
