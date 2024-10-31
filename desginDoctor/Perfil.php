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

?>


<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Perfil</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">
   <link rel="icon" href="../assets/images/logo.png" type="image/png">

</head>
<body>

<header class="header">
   
   <section class="flex">

      <a href="VerCitas.html" class="logo">Coffee & Medical Care</a>
    

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

<section class="user-profile">

   <h1 class="heading">Perfil</h1>

   <div class="info">

      <div class="user">
         <img src="<?php echo !empty($profile_image) ? 'uploaded_img/' . $profile_image : 'img/default_profile.png'; ?>" alt="Profile Image" class="profile-image" style="width: 100px; height: 100px; border-radius: 50%;">
         <h3 class="name"><?php echo $doctor['Nombre_Doc'] . ' ' . $doctor['Apellidos_Doc']; ?></h3>
         <p class="role">Doctor</p>
         <a href="ActualizarPerfil.php" class="inline-btn">Actualizar Perfil</a>
      </div>
</section>
<!-- custom js file link  -->
<script src="js/script.js"></script>

   
</body>
</html>