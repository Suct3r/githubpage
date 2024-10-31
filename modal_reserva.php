<!-- Button trigger modal -->

<div class="text-center">
    <button type="button" class="btn btn-success border" data-toggle="modal" data-target="#modalReserva">
      <i class="fas fa-calendar"></i> Iniciar reserva
    </button>
</div>

<!-- Modal -->
<div class="modal fade" id="modalReserva" tabindex="-1" role="dialog" aria-labelledby="modalReservaLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalReservaLabel">Ingresa los datos correspondientes</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        
      <?php include "metodos/form_insert.php";?>

      </div>
      <div class="modal-footer">
        <p class="text-center text-info"><b>Recuerda que una vez enviado el fomulario tu informacion sera procesada y no podras modificarla</b></p>
      </div>
    </div>
  </div>
</div>