// API Configuration - Backend server URL
const API_BASE_URL = 'http://localhost:3000/api';

// Debug: Log the API URL to make sure it's correct
console.log('API Base URL:', API_BASE_URL);

// DOM Elements
const loginForm = document.querySelector('form');
const emailInput = document.getElementById('email');
const passwordInput = document.getElementById('password');
const submitButton = document.querySelector('.btn');

// Add event listener when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
  // Handle form submission
  loginForm.addEventListener('submit', handleLogin);
});

async function handleLogin(e) {
  e.preventDefault();
  
  // Get form data
  const email = emailInput.value.trim();
  const password = passwordInput.value.trim();
  
  // Basic validation
  if (!email || !password) {
    showMessage('Please fill in all fields', 'error');
    return;
  }
  
  // Disable submit button and show loading state
  setLoadingState(true);
  
  try {
    console.log('Making request to:', `${API_BASE_URL}/auth/login`);
    
    const response = await fetch(`${API_BASE_URL}/auth/login`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      credentials: 'include', // Include cookies for session management
      body: JSON.stringify({
        email: email,
        password: password
      })
    });
    
     const data = await response.json();

    if (data.success) {
      // Store token and user data
      localStorage.setItem('authToken', data.data.token);
      localStorage.setItem('userData', JSON.stringify(data.data.user));

      showMessage('Login successful! Redirecting...', 'success');
      setTimeout(() => {
        // Redirect to appropriate dashboard based on user role
        const user = data.data.user;
        const dashboardPath = user.role === 'teacher' 
          ? '../dashboard/teacher.html' 
          : '../dashboard/student.html';
        window.location.href = dashboardPath;
      }, 2000);
    } else {
      throw new Error(data.message || 'Login failed');
    }

  } catch (error) {
    console.error('Login error details:', error);
    showMessage(error.message || 'Login failed. Please try again.', 'error');
  } finally {
    setLoadingState(false);
  }
}

function setLoadingState(isLoading) {
  const submitBtn = document.querySelector('.btn');
  const originalText = 'Login';
  
  if (isLoading) {
    submitBtn.disabled = true;
    submitBtn.innerHTML = `
      <span>Logging in...</span>
      <i class="fas fa-spinner fa-spin"></i>
    `;
  } else {
    submitBtn.disabled = false;
    submitBtn.innerHTML = `
      ${originalText}
      <i class="fas fa-arrow-right arrow-icon"></i>
    `;
  }
}

function showMessage(message, type = 'info') {
  // Remove existing message if any
  const existingMessage = document.querySelector('.message');
  if (existingMessage) {
    existingMessage.remove();
  }
  
  // Create message element
  const messageDiv = document.createElement('div');
  messageDiv.className = `message message-${type}`;
  messageDiv.textContent = message;
  
  // Add styles
  messageDiv.style.cssText = `
    padding: 12px 16px;
    margin: 16px 0;
    border-radius: 4px;
    font-size: 14px;
    font-weight: 500;
    ${type === 'success' ? 'background: #d4edda; color: #155724; border: 1px solid #c3e6cb;' : ''}
    ${type === 'error' ? 'background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;' : ''}
    ${type === 'info' ? 'background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb;' : ''}
  `;
  
  // Insert message before the form
  loginForm.parentNode.insertBefore(messageDiv, loginForm);
  
  // Auto-remove message after 5 seconds
  setTimeout(() => {
    if (messageDiv.parentNode) {
      messageDiv.remove();
    }
  }, 5000);
}

// Check if user is already logged in
function checkAuthStatus() {
  const token = localStorage.getItem('authToken');
  const userData = localStorage.getItem('userData');
  
  if (token && userData) {
    // User is already logged in, redirect to dashboard
    window.location.href = '../dashboard.html';
  }
}

// Check auth status when page loads
document.addEventListener('DOMContentLoaded', checkAuthStatus);