<!-- Booking Details Modal -->
<div class="modal fade" id="bookingDetailsModal" tabindex="-1" aria-labelledby="bookingDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
         <h5 class="modal-title" id="bookingDetailsModalLabel">Info Rezervare</h5>
         <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
         <div id="bookingDetailsContent">
           <!-- AJAX-loaded content will appear here -->
         </div>
      </div>
      <div class="modal-footer">
         <!-- This link will be set dynamically to the full booking page -->
         <a href="#" id="bookingLink" class="btn btn-primary">Detalii Complete</a>
         <button type="button" class="btn btn-danger js-cancel-booking">Anulează</button>
         <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Inchide</button>
      </div>
    </div>
  </div>
</div>
