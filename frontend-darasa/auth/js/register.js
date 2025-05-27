// const { set } = require("mongoose");

// API Configuration - Backend server URL
const API_BASE_URL = 'http://localhost:3000/api';

// Debug: Log the API URL to make sure it's correct
console.log('API Base URL:', API_BASE_URL);

// DOM Elements
const registerForm = document.querySelector('form');
const nameInput = document.getElementById('name');
const emailInput = document.getElementById('email');
const passwordInput = document.getElementById('password');
// const roleSelect = document.getElementById('role');
const submitButton = document.querySelector('.btn');

// Add event listener when DOM is loaded
document.addEventListener('DOMContentLoaded', function () {
  // Handle form submission
  registerForm.addEventListener('submit', handleRegister);

  // Add real-time validation
  emailInput.addEventListener('blur', validateEmail);
  passwordInput.addEventListener('input', validatePassword);
});

async function handleRegister(e) {
  e.preventDefault();

  const fullName = nameInput.value.trim();
  const email = emailInput.value.trim();
  const password = passwordInput.value.trim();

  setLoadingState(true);

  try {
    const response = await fetch(`${API_BASE_URL}/auth/register`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      credentials: 'include',
      body: JSON.stringify({
        full_name: fullName,
        email: email,
        password: password
      })
    });

   const data = await response.json();

    if (data.success) {
      showMessage('Registration successful! Redirecting to login...', 'success');
      setTimeout(() => {
        // Redirect to login page instead of dashboard
        window.location.href = 'login.html';
      }, 2000);
    } else {
      throw new Error(data.message || 'Registration failed');
    }

  } catch (error) {
    console.error('Registration error details:', error);
    showMessage(error.message || 'Registration failed. Please try again.', 'error');
  } finally {
    setLoadingState(false);
  }
}


function validateForm(fullName, email, password) {
  const errors = [];

  if (!fullName || fullName.length < 2) {
    errors.push('Full name must be at least 2 characters long');
  }

  if (!email || !isValidEmail(email)) {
    errors.push('Please enter a valid email address');
  }

  if (!password || password.length < 6) {
    errors.push('Password must be at least 6 characters long');
  }

  // if (!role || (role !== 'student' && role !== 'lecturer')) {
  //   errors.push('Please select a valid role');
  // }

  return errors;
}

function validateEmail() {
  const email = emailInput.value.trim();
  const errorElement = document.getElementById('email-error');

  // Remove existing error
  if (errorElement) {
    errorElement.remove();
  }

  if (email && !isValidEmail(email)) {
    showFieldError(emailInput, 'Please enter a valid email address');
  }
}

function validatePassword() {
  const password = passwordInput.value;
  const errorElement = document.getElementById('password-error');

  // Remove existing error
  if (errorElement) {
    errorElement.remove();
  }

  if (password && password.length < 6) {
    showFieldError(passwordInput, 'Password must be at least 6 characters long');
  }
}

function showFieldError(inputElement, message) {
  const errorDiv = document.createElement('div');
  errorDiv.id = inputElement.id + '-error';
  errorDiv.className = 'field-error';
  errorDiv.textContent = message;
  errorDiv.style.cssText = `
    color: #721c24;
    font-size: 12px;
    margin-top: 4px;
    margin-left: 8px;
  `;

  inputElement.parentNode.insertBefore(errorDiv, inputElement.nextSibling);
}

function isValidEmail(email) {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return emailRegex.test(email);
}

function setLoadingState(isLoading) {
  const submitBtn = document.querySelector('.btn');
  const originalText = 'Sign Up';

  if (isLoading) {
    submitBtn.disabled = true;
    submitBtn.innerHTML = `
      <span>Creating Account...</span>
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
  messageDiv.innerHTML = message;

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
  registerForm.parentNode.insertBefore(messageDiv, registerForm);

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