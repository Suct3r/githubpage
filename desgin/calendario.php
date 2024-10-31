<?php
// Incluir archivo de configuración de la base de datos
include("model/conexion.php");

try {
    // Consulta SQL para obtener las citas de la tabla "Reservas"
    $sql = "SELECT ID AS id, Nombre AS title, Fecha AS start, Hora AS time FROM Reservas";
    $stmt = $db->prepare($sql);
    $stmt->execute();

    // Crea un array para almacenar las citas
    $citas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Manejo de errores de la base de datos
    echo "Error en la base de datos: " . $e->getMessage();
}

// Cierra la conexión a la base de datos
$db = null;

// Convierte las citas a formato JSON para que JavaScript las pueda utilizar
$citas_json = json_encode($citas);
?>


    <div id='calendar'></div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            events: <?php echo $citas_json; ?>, // Carga las citas desde PHP
            locale: 'es', // Cambia el idioma a español
            eventTimeFormat: {
                hour: 'numeric',
                minute: '2-digit',
                meridiem: 'short'
            },
            dayHeaderFormat: {
                weekday: 'short',
                month: 'numeric',
                day: 'numeric'
            },
            views: {
                dayGridMonth: {},
                timeGridWeek: {},
                timeGridDay: {}
            },
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            navLinks: true, // Habilitar enlaces en los eventos para navegar
            editable: false // Permitir arrastrar y soltar eventos para moverlos
        });
        calendar.render();
    });
</script>
