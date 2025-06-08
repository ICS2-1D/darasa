// Simple function to toggle the create form
function toggleCreateForm() {
  const form = document.getElementById('createClassForm');
  form.classList.toggle('show');
}

// Simple function to copy class code
function copyToClipboard(text) {
  navigator.clipboard.writeText(text).then(function () {
    alert('Class code copied to clipboard!');
  }).catch(function () {
    // Fallback for older browsers
    const textArea = document.createElement('textarea');
    textArea.value = text;
    document.body.appendChild(textArea);
    textArea.select();
    document.execCommand('copy');
    document.body.removeChild(textArea);
    alert('Class code copied to clipboard!');
  });
}