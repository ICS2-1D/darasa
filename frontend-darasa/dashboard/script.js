function toggleForm() {
  const form = document.getElementById('createForm');
  form.style.display = form.style.display === 'none' ? 'block' : 'none';
  if (form.style.display === 'block') {
    form.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  }
}

function copyCode(code) {
  if (navigator.clipboard) {
    navigator.clipboard.writeText(code).then(() => {
      showToast('Class code copied!', 'success');
    }).catch(() => {
      showToast('Could not copy code', 'error');
    });
  } else {
    showToast('Copy manually: ' + code, 'info');
  }
}

function showToast(message, type) {
  const toast = document.createElement('div');
  toast.innerHTML = `<i class="fas fa-${type === 'success' ? 'check' : 'info'}-circle"></i> ${message}`;
  toast.style.cssText = `
                position: fixed; top: 20px; right: 20px; z-index: 9999;
                background: ${type === 'success' ? '#34a853' : '#1a73e8'}; color: white;
                padding: 12px 20px; border-radius: 8px; font-size: 14px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.3); animation: slideIn 0.3s ease-out;
            `;

  if (!document.querySelector('#toast-styles')) {
    const style = document.createElement('style');
    style.id = 'toast-styles';
    style.textContent = '@keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }';
    document.head.appendChild(style);
  }

  document.body.appendChild(toast);
  setTimeout(() => toast.remove(), 3000);
}