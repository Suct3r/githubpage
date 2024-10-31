<?php
    include 'configBD.php'; // Asegúrate de tener este archivo que contiene la conexión a la base de datos

    session_start();

    if (!isset($_SESSION['user_id'])) {
        header('Location: /CITAS/iniciarSe.php');
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

  // Obtener los datos del paciente
   $fetch_paciente = getPacienteData($conn, $user_id);
   $paciente_id = $fetch_paciente['Id_Paciente'];

    // Obtener información del usuario
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
            $toastMessage = '<span style="font-size: 16px;">Imagen actualizada correctamente!</span>';
            $toastType = 'exito';
         }
            echo "<script>
                  document.addEventListener('DOMContentLoaded', function() {
                     agregarToast({ tipo: '$toastType', titulo: 'Mensaje', descripcion: '$toastMessage', autoCierre: true });
                  });
            </script>";
      }

      $fetch_paciente = getPacienteData($conn, $user_id);

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
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Actualizar Perfil</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">

   <!-- custom css file link  -->
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

<section class="form-container">

   <form action="" method="post" enctype="multipart/form-data">
      <h3>Actualizar Perfil</h3>
      <span style="font-size: 16px;">Nombre :</span>
         <input type="text" name="update_name" value="<?php echo $fetch_paciente['Nombre_Pac']; ?>" class="box">
      <span style="font-size: 16px;">Apellidos :</span>
         <input type="text" name="update_apellidos" value="<?php echo $fetch_paciente['Apellidos_Pac']; ?>" class="box">
      <span style="font-size: 16px;">Direccion :</span>
         <input type="text" name="update_direccion" value="<?php echo $fetch_paciente['Direccion_Pac']; ?>" class="box">
      <span style="font-size: 16px;">Telefono :</span>
         <input type="text" name="update_telefono" value="<?php echo $fetch_paciente['Telefono_Pac']; ?>" class="box">
      <span style="font-size: 16px;">Fecha de nacimiento :</span>
         <input type="text" name="update_fecha_nacimiento" value="<?php echo $fetch_paciente['FechaNac_Pac']; ?>" class="box">
      <span style="font-size: 16px;">Edad :</span>
         <input type="text" name="update_edad" value="<?php echo $fetch_paciente['Edad_Pac']; ?>" class="box">
      <span style="font-size: 16px;">Imagen de Perfil:</span>
         <input type="file" name="update_image" accept="image/jpg, image/jpeg, image/png" class="box">
      <input type="submit" value="Actualizar" name="update_profile" class="btn">
   </form>

</section>



      <!-- custom js file link  -->
      <script src="js/script.js"></script>
      <!-- ====================================================================== -->

      <div class="contenedor">
			<div class="contenedor-toast" id="contenedor-toast">
            </div>
		</div>

      <script src="../assets/js/script_Noti.js"></script>

   
</body>
</html>