    <!-- Global Modal Photo Preview -->
    <div class="modal fade" id="photoPreviewModal" tabindex="-1" style="z-index: 1070;">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-transparent border-0 shadow-lg" style="backdrop-filter: blur(10px);">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close btn-close-white ms-auto bg-white rounded-circle p-2" data-bs-dismiss="modal" style="opacity: 0.8;"></button>
            </div>
            <div class="modal-body text-center pt-2 pb-4 px-4 text-white">
                <div class="position-relative d-inline-block shadow-lg rounded-4 mb-4 overflow-hidden" style="border: 4px solid rgba(255,255,255,0.2);">
                    <img id="preview-large-img" src="" class="object-fit-cover bg-white" style="width: 380px; height: 380px;">
                </div>
                <h3 class="fw-bold mb-1" id="preview-name" style="text-shadow: 1px 1px 3px rgba(0,0,0,0.5);"></h3>
                
                <!-- Optional Membership Status -->
                <div id="preview-memb-status-container" class="mt-2 d-none">
                    <span id="preview-memb-status" class="badge bg-success fs-6 px-3 py-2 rounded-pill shadow-sm"></span>
                    <div id="preview-memb-days" class="text-white-50 small mt-1"></div>
                </div>

                <div class="d-flex justify-content-center gap-3 mt-3" style="text-shadow: 1px 1px 2px rgba(0,0,0,0.5);">
                    <div class="d-flex align-items-center bg-dark bg-opacity-50 px-3 py-1 rounded-pill">
                        <i class="fas fa-id-card me-2 text-info"></i>
                        <span id="preview-id" class="fs-6"></span>
                    </div>
                    <div class="d-flex align-items-center bg-dark bg-opacity-50 px-3 py-1 rounded-pill">
                        <i class="fas fa-phone me-2 text-success"></i>
                        <span id="preview-phone" class="fs-6"></span>
                    </div>
                </div>
            </div>
        </div>
      </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function previewPhoto(url, name, idNumber, phone, membStatus = null, membColor = null, membDays = null) {
            document.getElementById('preview-large-img').src = url;
            document.getElementById('preview-name').textContent = name;
            document.getElementById('preview-id').textContent = idNumber;
            document.getElementById('preview-phone').textContent = phone;
            
            const statusContainer = document.getElementById('preview-memb-status-container');
            if (membStatus) {
                const statusBadge = document.getElementById('preview-memb-status');
                statusBadge.className = `badge bg-${membColor} fs-6 px-3 py-2 rounded-pill shadow-sm`;
                statusBadge.textContent = membStatus;
                
                document.getElementById('preview-memb-days').textContent = membDays || '';
                statusContainer.classList.remove('d-none');
            } else {
                statusContainer.classList.add('d-none');
            }
            
            let previewModalInstance = bootstrap.Modal.getInstance(document.getElementById('photoPreviewModal'));
            if (!previewModalInstance) {
                previewModalInstance = new bootstrap.Modal(document.getElementById('photoPreviewModal'));
            }
            previewModalInstance.show();
        }
    </script>
</body>
</html>
