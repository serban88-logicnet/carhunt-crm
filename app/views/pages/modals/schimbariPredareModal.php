<div class="modal fade" id="modalSchimbari" tabindex="-1" aria-labelledby="modalSchimbariLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalSchimbariLabel">Schimbare Date</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="schimbariForm" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="numarMasinaNoua" class="form-label">Numar Masina Noua</label>
                        <input type="text" class="form-control" id="numarMasinaNoua" name="numarMasinaNoua">
                    </div>
                    <div class="mb-3">
                        <label for="dataSosireNoua" class="form-label">Data Sosire Noua</label>
                        <input type="date" class="form-control prevent-past-date" id="dataSosireNoua" name="dataSosireNoua">
                    </div>
                    <div class="mb-3">
                        <label for="oraSosireNoua" class="form-label">Ora Sosire Noua</label>
                        <input type="text" class="form-control js-custom-timepicker prevent-past-time" id="oraSosireNoua" name="oraSosireNoua">
                    </div>
                    <input type="hidden" id="inchiriereId" name="inchiriereId">
                    <input type="hidden" id="masinaId" name="masinaId">
                    <input type="hidden" id="actionType" name="actionType">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Inchide</button>
                <button type="button" class="btn btn-primary" id="submitSchimbari">Realizeaza Schimbari</button>
            </div>
        </div>
    </div>
</div>