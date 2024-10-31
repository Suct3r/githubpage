<?php
    include 'configBD.php'; // Asegúrate de tener este archivo que contiene la conexión a la base de datos

    session_start();

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

    // Manejo de solicitudes AJAX para cancelar citas
    if (isset($_POST['cancelar_cita'])) {
        $cita_id = $_POST['cita_id'];
        // Eliminar la cita de la base de datos
        $delete_query = "DELETE FROM `cita` WHERE Id_Cita = '$cita_id'";
        if (mysqli_query($conn, $delete_query)) {
            $response = ['status' => 'success', 'message' => 'La cita se ha cancelado con éxito.'];
        } else {
            $response = ['status' => 'error', 'message' => 'Ha ocurrido un error al cancelar la cita. Por favor, inténtalo de nuevo más tarde.'];
        }
        echo json_encode($response);
        exit;
    }

    // Función para generar la tabla de citas
    function generarTablaCitas($conn, $paciente) {
        $select_citas = mysqli_query($conn, "SELECT c.*, CONCAT(d.Nombre_Doc, ' ', d.Apellidos_Doc) AS Doctor FROM `cita` c JOIN doctor d ON c.Id_Doc_FK = d.Id_Doc WHERE Id_Paciente_FK = '{$paciente['Id_Paciente']}'") or die('query failed');
        ob_start();
        while ($registro = mysqli_fetch_assoc($select_citas)) {
            $estado = $registro['Asistencia_Cita'];
            $clase_color = '';
            switch ($estado) {
                case 'pendiente':
                    $clase_color = 'text-warning'; // Amarillo para Pendiente
                    break;
                case 'Cancelado':
                    $clase_color = 'text-danger'; // Rojo para Cancelado
                    break;
                case 'Confirmado':
                    $clase_color = 'text-success'; // Verde para Confirmado
                    break;
                default:
                    $clase_color = ''; // Sin clase de color por defecto
                    break;
            }
            echo "
            <tr>
                <td class='texto-grande'>{$registro['Doctor']}</td>
                <td class='texto-grande'>
                ";
                $id_tratamiento = $registro['Id_Trat_FK'];
                $select_tratamiento = mysqli_query($conn, "SELECT Nombre_Trat FROM tratamiento WHERE Id_Trat = $id_tratamiento") or die('query failed');
                $tratamiento = mysqli_fetch_assoc($select_tratamiento);
                echo $tratamiento['Nombre_Trat'];
                echo "
                </td>
                <td class='texto-grande'>{$registro['Fecha_Cita']}</td>
                <td class='texto-grande'>{$registro['Hora_Cita']}</td>
                <td class='texto-grande'>{$registro['Mensaje_Cita']}</td>
                <td class='texto-grande'><b class='$clase_color'>{$estado}</b></td>
                <td class='texto-grande'>
                    ";
                    if ($estado == 'pendiente') {
                        echo "
                        <form method='post' class='cancelar-cita-form'>
                            <input type='hidden' name='cita_id' value='{$registro['Id_Cita']}'>
                            <button type='button' class='button btn-sm btn-danger cancelar-cita'>Cancelar</button>
                        </form>
                        ";
                    }
                    echo "
                </td>
            </tr>
            ";
        }
        return ob_get_clean();
    }
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Registros</title>

    <!-- font awesome cdn link  -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css">

    <!-- custom css file link  -->
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="../assets/css/noti.css">
    <link rel="icon" href="../assets/images/logo.png" type="image/png">

    <style>
        /* Estilo para hacer que el texto sea más grande */
        .texto-grande {
            font-size: 14px;
            /* Puedes ajustar este valor según lo grande que quieras que sea el texto */
        }
        .table-container {
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 18px;
            text-align: left;
        }
        table th, table td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
        }
        table th {
            background-color: #f4f4f4;
            font-weight: bold;
        }
        table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        table tr:hover {
            background-color: #f1f1f1;
        }
        .button {
            padding: 5px 10px;
            font-size: 14px;
            border: none;
            cursor: pointer;
            border-radius: 3px;
        }
        .btn-danger {
            background-color: #e74c3c;
            color: white;
        }
        .btn-danger:hover {
            background-color: #c0392b;
        }
    </style>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
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
            <a href="Perfil.php" class="btn">Ver Perfil</a>
        </div>
        <nav class="navbar">
            <a href="VerCitas.php"><i class="fas fa-search"></i><span>Ver Citas</span></a>
            <a href="AgendarCita.php"><i class="fas fa-sun"></i><span>Agendar Cita</span></a>
            <a href="ListaRegistro.php"><i class="fas fa-chalkboard-user"></i><span>Lista de registros</span></a>
            <a href="HistorialMedico.php"><i class="fas fa-user"></i><span>Historial Medico</span></a>
        </nav>
    </div>

    <section class="teachers">
        <h1 class="heading">Lista de registros</h1>
        <div class="container">
            <br><br>
            <div class="row">
                <div class="container">
                    <br><br>
                    <table class="table table-striped" id="tablaReservas">
                        <thead>
                            <tr>
                                <!-- Aplica el estilo a los encabezados -->
                                <th class="texto-grande">DOCTOR</th>
                                <th class="texto-grande">SERVICIO</th>
                                <th class="texto-grande">FECHA</th>
                                <th class="texto-grande">HORA</th>
                                <th class="texto-grande">MENSAJE</th>
                                <th class="texto-grande">ESTADO</th>
                                <th class="texto-grande">ACCIONES</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php echo generarTablaCitas($conn, $paciente); ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <!-- custom js file link  -->
    <script src="js/script.js"></script>
    <div class="contenedor">
        <div class="contenedor-toast" id="contenedor-toast"></div>
    </div>
    <script src="../assets/js/script_Noti.js"></script>

    <script>
        $(document).ready(function() {
            $('.cancelar-cita').on('click', function() {
                const form = $(this).closest('form');
                $.ajax({
                    type: 'POST',
                    url: '', // El mismo archivo PHP que maneja la lógica
                    data: form.serialize() + '&cancelar_cita=1',
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            form.closest('tr').remove();
                            agregarToast({
                                tipo: 'exito',
                                titulo: 'Mensaje',
                                descripcion: '<span style="font-size: 16px;">' + response.message + '</span>',
                                autoCierre: true
                            });
                        } else {
                            agregarToast({
                                tipo: 'error',
                                titulo: 'Mensaje',
                                descripcion: '<span style="font-size: 16px;">' + response.message + '</span>',
                                autoCierre: true
                            });
                        }
                    }
                });
            });
        });
    </script>
</body>

</html>
