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

// Función para verificar si la nueva fecha y hora de la cita se empalman con otras citas existentes
function verificarEmpalmeCitas($conn, $id_cita, $fecha_nueva, $hora_nueva) {
    $query = "SELECT COUNT(*) AS num_citas FROM `cita` WHERE Id_Cita != '$id_cita' AND Fecha_Cita = '$fecha_nueva' AND Hora_Cita = '$hora_nueva'";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    return $row['num_citas'] > 0;
}

// Procesar la modificación de la cita
if (isset($_POST['modificar_cita'])) {
    $cita_id = $_POST['cita_id'];
    $fecha_nueva = $_POST['fecha_nueva'];
    $hora_nueva = $_POST['hora_nueva'];

    // Verificar si hay empalme de citas
    if (verificarEmpalmeCitas($conn, $cita_id, $fecha_nueva, $hora_nueva)) {
        $response = ['status' => 'error', 'message' => 'La cita se empalma con otra cita existente. Por favor, elige una fecha y hora diferente.'];
        echo json_encode($response);
        exit;
    }

    // Continuar con la modificación de la cita si no hay empalme
    // Aquí debes realizar la lógica para modificar la cita en la base de datos
    // ...
}

// Función para generar la tabla de citas con filtros
function generarTablaCitas($conn, $doctor, $nombre_paciente = '', $fecha_cita = '') {
    $query = "SELECT c.*, p.Nombre_Pac, p.Apellidos_Pac, t.Nombre_Trat FROM `cita` c
              JOIN `paciente` p ON c.Id_Paciente_FK = p.Id_Paciente
              JOIN `tratamiento` t ON c.Id_Trat_FK = t.Id_Trat
              WHERE c.Id_Doc_FK = '{$doctor['Id_Doc']}'";

    if (!empty($nombre_paciente)) {
        $query .= " AND (p.Nombre_Pac LIKE '%$nombre_paciente%' OR p.Apellidos_Pac LIKE '%$nombre_paciente%')";
    }

    if (!empty($fecha_cita)) {
        $query .= " AND c.Fecha_Cita = '$fecha_cita'";
    }

    $query .= " ORDER BY c.Fecha_Cita DESC, c.Hora_Cita DESC"; // Ordenar por fecha de manera descendente

    $select_citas = mysqli_query($conn, $query) or die('query failed');
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
            <td class='texto-grande'>{$registro['Nombre_Pac']}</td>
            <td class='texto-grande'>{$registro['Apellidos_Pac']}</td>
            <td class='texto-grande'>{$registro['Nombre_Trat']}</td>
            <td class='texto-grande'>{$registro['Fecha_Cita']}</td>
            <td class='texto-grande'>{$registro['Hora_Cita']}</td>
            <td class='texto-grande'>{$registro['Mensaje_Cita']}</td>
            <td class='texto-grande'><b class='$clase_color'>{$estado}</b></td>
            <td class='texto-grande'>
                ";
                if ($estado == 'pendiente') {
                    echo "
                    <form method='post' class='cancelar-cita-form' style='display:inline-block;'>
                        <input type='hidden' name='cita_id' value='{$registro['Id_Cita']}'>
                        <button type='button' class='button btn-sm btn-danger cancelar-cita'>Cancelar</button>
                    </form>
                    <a href='ActualizarRegistro.php?id={$registro['Id_Cita']}' class='button btn-sm btn-primary' style='margin-left: 10px;'>Editar</a>
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
        .label-tamano {
            font-size: 16px; /* Ajusta el tamaño de la fuente según tus necesidades */
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
            border-radius:3px;
        }
        .btn-danger {
            background-color: #e74c3c;
            color: white;
        }
        .btn-danger:hover {
            background-color: #c0392b;
        }
        .btn-primary {
            background-color: #3498db;
            color: white;
        }
        .btn-primary:hover {
            background-color: #2980b9;
        }
        .button-container {
            display: flex;
            gap: 10px;
        }
        /* Estilos para separar el botón del campo de entrada */
        .filtros-form {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .filter-fields {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .filter-button {
            align-self: flex-start;
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
            <a href="ListaRegistro.php"><i class="fas fa-list"></i><span>Historial Citas</span></a>
        </nav>
    </div>

    <section class="pills-section">
        <div class="container">
            <h1 class="heading">Historial de Citas</h1>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Apellido</th>
                            <th>Tratamiento</th>
                            <th>Fecha</th>
                            <th>Hora</th>
                            <th>Mensaje</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php echo generarTablaCitas($conn, $doctor); ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <script>
        $(document).ready(function () {
            $(document).on('click', '.cancelar-cita', function () {
                var form = $(this).closest('.cancelar-cita-form');
                var citaId = form.find('input[name="cita_id"]').val();
                if (confirm('¿Estás seguro de que deseas cancelar esta cita?')) {
                    $.ajax({
                        type: 'POST',
                        url: 'ListaRegistro.php',
                        data: {
                            cancelar_cita: true,
                            cita_id: citaId
                        },
                        success: function (response) {
                            var data = JSON.parse(response);
                            if (data.status === 'success') {
                                alert(data.message);
                                location.reload();
                            } else {
                                alert(data.message);
                            }
                        }
                    });
                }
            });
        });
    </script>

    <script src="js/script.js"></script>
</body>

</html>