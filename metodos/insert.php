<?php
#prueba de envio de datos
#print_r($_POST);

# Verificar si se ha enviado el formulario
if (!isset($_POST['oculto'])) {
    exit(); // Si no se ha enviado, salir del script.
}

# Incluir el archivo de conexión a la base de datos
include '../admin/model/conexion.php';

# Obtener los datos del formulario
$nombre = $_POST['nombre'];
$apellidos = $_POST['apellidos'];
$correo = $_POST['correo'];
$servicio = $_POST['servicio'];
$fecha = $_POST['fecha'];
$hora = $_POST['hora'];
$mensaje = $_POST['mensaje'];
$estado = $_POST['estado'];

# Verificar si ya existe una cita en la misma fecha y hora
$consulta = $db->prepare("SELECT COUNT(*) FROM reservas WHERE fecha = ? AND hora = ?");
$consulta->execute([$fecha, $hora]);
$existeCita = $consulta->fetchColumn();

if ($existeCita > 0) {
    header('Location: ../error.php');
    echo 'Ya existe una cita programada para la misma fecha y hora.';
} else {
    # Insertar el nuevo registro si no hay conflicto
    $sentencia = $db->prepare("INSERT INTO reservas(nombre, apellidos, correo, servicio, fecha, hora, mensajeadicional, estado)
    VALUES(?,?,?,?,?,?,?,?)");
    
    if ($sentencia->execute([$nombre, $apellidos, $correo, $servicio, $fecha, $hora, $mensaje, $estado])) {
        header('Location: ../exito.php'); // Redirigir si la inserción fue exitosa.
    } else {
        echo 'Error al insertar datos.';
    }
}
?>