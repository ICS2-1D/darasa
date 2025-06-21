
/* frontend-darasa/materials/materials.js */

document.addEventListener('DOMContentLoaded', () => {
    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('materialFile');
    const prompt = dropZone.querySelector('.drop-zone-prompt');

    if (!dropZone || !fileInput) return;

    // Open file selector when drop zone is clicked
    dropZone.addEventListener('click', () => {
        fileInput.click();
    });

    // Handle file selection via the input
    fileInput.addEventListener('change', () => {
        if (fileInput.files.length > 0) {
            updatePrompt(fileInput.files[0]);
        }
    });

    // Drag and Drop events
    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.classList.add('drag-over');
    });

    ['dragleave', 'dragend'].forEach(type => {
        dropZone.addEventListener(type, () => {
            dropZone.classList.remove('drag-over');
        });
    });

    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.classList.remove('drag-over');

        const droppedFiles = e.dataTransfer.files;
        if (droppedFiles.length > 0) {
            // Assign the dropped file to our hidden file input
            fileInput.files = droppedFiles;
            updatePrompt(droppedFiles[0]);
        }
    });

    // Function to update the prompt text with the selected file name
    function updatePrompt(file) {
        prompt.classList.add('file-selected');
        prompt.innerHTML = `
            <i class="fas fa-check-circle"></i>
            <p>${file.name}</p>
            <small>Size: ${(file.size / 1024 / 1024).toFixed(2)} MB. Click to change.</small>
        `;
    }
});
