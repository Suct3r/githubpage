<?php
include 'configBD.php';
session_start();

// Función para manejar el registro
function registrarUsuario($conn) {
    if(isset($_POST['submit'])){
        $Nombre = mysqli_real_escape_string($conn, $_POST['Nombre']);
        $Apellidos = mysqli_real_escape_string($conn, $_POST['Apellidos']);
        $Fecha = mysqli_real_escape_string($conn, $_POST['Fecha']);
        $email = mysqli_real_escape_string($conn, $_POST['EmailR']);
        $ContraseñaR = mysqli_real_escape_string($conn, md5($_POST['ContraseñaR']));
    
        // Verificar si el usuario ya existe
        $selectUser = mysqli_query($conn, "SELECT * FROM `usuario` WHERE Email_Usu = '$email'") or die('query failed');
        // Verificar si el paciente ya existe
        $selectPaciente = mysqli_query($conn, "SELECT * FROM `paciente` WHERE Nombre_Pac = '$Nombre' AND Apellidos_Pac = '$Apellidos'") or die('query failed');
    
        $toastMessage = "";
        $toastType = "";
    
        if(mysqli_num_rows($selectUser) > 0){
            $toastMessage = 'El usuario ya existe';
            $toastType = 'error';
        } elseif(mysqli_num_rows($selectPaciente) > 0){
            $toastMessage = 'El paciente ya existe';
            $toastType = 'error';
        } else {
            // Iniciar transacción
            mysqli_begin_transaction($conn);
            
            try {
                // Insertar datos en la tabla 'usuario'
                $insertUser = mysqli_query($conn, "INSERT INTO `usuario` (Email_Usu, Contraseña_Usu) VALUES ('$email', '$ContraseñaR')");
                if(!$insertUser) {
                    throw new Exception('Error al insertar en usuario');
                }
    
                // Obtener el ID del usuario recién insertado
                $idUsu = mysqli_insert_id($conn);
    
                // Insertar datos en la tabla 'paciente' utilizando el ID del usuario
                $insertPaciente = mysqli_query($conn, "INSERT INTO `paciente` (Nombre_Pac, Apellidos_Pac, FechaNac_Pac, Id_Usu_Pac_FK) VALUES ('$Nombre', '$Apellidos', '$Fecha', '$idUsu')");
                if(!$insertPaciente) {
                    throw new Exception('Error al insertar en paciente');
                }
    
                // Confirmar la transacción
                mysqli_commit($conn);
    
                $toastMessage = 'Registrado correctamente, por favor Inicie Sesión';
                $toastType = 'exito';
                $_SESSION['user_id'] = $idUsu;  // Guardar el user_id en la sesión
                $_SESSION['user_role'] = 'paciente';  // Si deseas también guardar el rol
                header('location:perfil.php');
            } catch (Exception $e) {
                // Revertir la transacción en caso de error
                mysqli_rollback($conn);
                $toastMessage = 'Fallo el registro: ' . $e->getMessage();
                $toastType = 'error';
            }
        }
    
        echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            agregarToast({ tipo: '$toastType', titulo: 'Mensaje', descripcion: '$toastMessage', autoCierre: true });
        });
      </script>";
    }
}

// Función para manejar el inicio de sesión
function iniciarSesion($conn) {
    if(isset($_POST['submit_login'])){
        $email2 = mysqli_real_escape_string($conn, $_POST['EmailL']);
        $password = mysqli_real_escape_string($conn, $_POST['ContraseñaL']);
    
        $select = mysqli_query($conn, "SELECT * FROM `usuario` WHERE Email_Usu = '$email2'");
    
        // Definir variables para el mensaje de tostada fuera de la verificación del resultado de la consulta
        $toastMessage = "";
        $toastType = "";
    
        if (mysqli_num_rows($select) > 0) {
            $user = mysqli_fetch_assoc($select);

            if (md5($password) === $user['Contraseña_Usu']) {
                $_SESSION['user_id'] = $user['Id_Usu'];

                // Verificar el rol del usuario
                $idUsu = $user['Id_Usu'];
                $role = '';

                // Verificar si es paciente
                $queryPaciente = "SELECT * FROM `paciente` WHERE Id_Usu_Pac_FK = '$idUsu'";
                $resultPaciente = mysqli_query($conn, $queryPaciente);
                if (mysqli_num_rows($resultPaciente) > 0) {
                    $role = 'paciente';
                }

                // Verificar si es doctor
                $queryDoctor = "SELECT * FROM `doctor` WHERE Id_Usu_Doc_FK = '$idUsu'";
                $resultDoctor = mysqli_query($conn, $queryDoctor);
                if (mysqli_num_rows($resultDoctor) > 0) {
                    $role = 'doctor';
                }

                // Verificar si es administrador
                $queryAdmin = "SELECT * FROM `administrador` WHERE Id_Usu_Admin_FK = '$idUsu'";
                $resultAdmin = mysqli_query($conn, $queryAdmin);
                if (mysqli_num_rows($resultAdmin) > 0) {
                    $role = 'administrador';
                }

                // Guardar el rol del usuario en la sesión
                $_SESSION['user_role'] = $role;

                // Redirigir según el rol del usuario
                if ($role === 'paciente') {
                    header('Location: ../citas/desgin/VerCitas.php');
                    exit; // Es importante salir del script después de la redirección
                } elseif ($role === 'doctor') {
                    header('Location: ../citas/desginDoctor/VerCitas.php');
                    exit;
                } elseif ($role === 'administrador') {
                    header('Location: ../citas/home_administrador.php');
                    exit;
                } else {
                    $toastMessage = 'No se pudo determinar el rol del usuario';
                    $toastType = 'error';
                }

            } else {
                $toastMessage = 'Credenciales inválidas';
                $toastType = 'error';
            }
        } else {
            $toastMessage = 'Error en la consulta de usuario';
            $toastType = 'error';
        }

        // Mostrar el mensaje de tostada utilizando las variables definidas fuera de la verificación del resultado de la consulta
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                agregarToast({ tipo: '$toastType', titulo: 'Mensaje', descripcion: '$toastMessage', autoCierre: true });
            });
        </script>";
    }
}




// Manejo del formulario según la acción requerida
if(isset($_POST['submit_login'])) {
    iniciarSesion($conn);
} elseif(isset($_POST['submit'])) {
    registrarUsuario($conn);
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Portal</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
    <link rel="icon" href="../citas/assets/images/logo.png" type="image/png">
    <link rel="stylesheet" href="assets/css/estilos.css">
</head>
<body>
<header>
        <div class="logo">
            <img src="../citas/assets/images/logo2.png" alt="Logo">
        </div>
    </header>
        <main>
            <div class="contenedor__todo">
                <div class="caja__trasera">
                    <div class="caja__trasera-login">
                        <h3>¿Ya tienes una cuenta?</h3>
                        <p>Inicia sesión para entrar en la página</p>
                        <button id="btn__iniciar-sesion">Iniciar Sesión</button>
                    </div>
                    <div class="caja__trasera-register">
                        <h3>¿Aún no tienes una cuenta?</h3>
                        <p>Regístrate para que puedas iniciar sesión</p>
                        <button id="btn__registrarse">Regístrarse</button>
                    </div>
                </div>

                <!--Formulario de Login y registro-->
                <div class="contenedor__login-register">
                    <!--Login-->
                    <form action="" method="post" enctype="multipart/form-data" class="formulario__login">
                        <h2>Iniciar Sesión</h2>
                        <input type="text" name="EmailL" placeholder="Correo Electronico" required>
                        <input type="password" name="ContraseñaL" placeholder="Contraseña" required>
                        <button type="submit" name="submit_login">Entrar</button>
                        
                    </form>

                    <!--Register-->
                    <form action="" method="post" enctype="multipart/form-data" class="formulario__register">
                        <h2>Regístrarse</h2>
                        <span>Nombre:</span>
                        <input type="text" name="Nombre" placeholder="Nombre" required>
                        <span>Apellidos:</span>
                        <input type="text" name="Apellidos" placeholder="Apellidos" required>
                        <span>Fecha de Nacimiento:</span>
                        <input type="date" name="Fecha" placeholder="Fecha de Nacimiento" required>
                        <span>Email:</span>
                        <input type="text" name="EmailR" placeholder="Email" required>
                        <span>Contraseña:</span>
                        <input type="password" name="ContraseñaR" placeholder="Contraseña" required>
                        <button type="submit" name="submit">Regístrarse</button>
                    </form>
                </div>
            </div>


            <!-- ====================================================================== -->

            <div class="contenedor">
			<div class="contenedor-toast" id="contenedor-toast">
				<!-- Plantilla de toast
				<div class="toast exito" id="1">
					<div class="contenido">
						<div class="icono">
							<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
								<path
									d="M2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2zm10.03 4.97a.75.75 0 0 1 .011 1.05l-3.992 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425a.75.75 0 0 1 1.08-.022z"
								/>
							</svg>
						</div>
						<div class="texto">
							<p class="titulo">Exito!</p>
							<p class="descripcion">La operación fue exitosa.</p>
						</div>
					</div>
					<button class="btn-cerrar">
						<div class="icono">
							<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
								<path
									d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"
								/>
							</svg>
						</div>
					</button>
				</div>
				<div class="toast error" id="2">
					<div class="contenido">
						<div class="icono">
							<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
								<path
									d="M11.46.146A.5.5 0 0 0 11.107 0H4.893a.5.5 0 0 0-.353.146L.146 4.54A.5.5 0 0 0 0 4.893v6.214a.5.5 0 0 0 .146.353l4.394 4.394a.5.5 0 0 0 .353.146h6.214a.5.5 0 0 0 .353-.146l4.394-4.394a.5.5 0 0 0 .146-.353V4.893a.5.5 0 0 0-.146-.353L11.46.146zM8 4c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 4.995A.905.905 0 0 1 8 4zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"
								/>
							</svg>
						</div>
						<div class="texto">
							<p class="titulo">Error!</p>
							<p class="descripcion">Hubo un error al intentar procesar la operación.</p>
						</div>
					</div>
					<button class="btn-cerrar">
						<div class="icono">
							<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
								<path
									d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"
								/>
							</svg>
						</div>
					</button>
				</div>-->
			</div>
		</div>

        </main>

        <script src="assets/js/script.js"></script>
        
        
</body>
</html>