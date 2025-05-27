class AuthUtils {
  static API_BASE_URL = 'http://localhost:3000/api';

  // Get stored authentication token
  static getToken() {
    return localStorage.getItem('authToken');
  }

  static isAuthPage() {
    return window.location.pathname.includes('/auth/login.html') ||
      window.location.pathname.includes('/auth/register.html');
  }

  // Get stored user data
  static getUser() {
    const userData = localStorage.getItem('userData');
    return userData ? JSON.parse(userData) : null;
  }

  // Check if user is authenticated with token validation
  static async isAuthenticated() {
    const token = this.getToken();
    const user = this.getUser();
    
    if (!token || !user) {
      return false;
    }

    // Validate token with server
    try {
      const response = await fetch(`${this.API_BASE_URL}/auth/profile`, {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json'
        }
      });

      if (response.status === 401 || response.status === 403) {
        // Token is invalid, clear auth data
        this.clearAuth();
        return false;
      }

      return response.ok;
    } catch (error) {
      console.error('Auth validation error:', error);
      return false;
    }
  }

  // Synchronous version for quick checks (doesn't validate with server)
  static isAuthenticatedSync() {
    const token = this.getToken();
    const user = this.getUser();
    return !!(token && user);
  }

  // Clear authentication data
  static clearAuth() {
    localStorage.removeItem('authToken');
    localStorage.removeItem('userData');
  }

  // Logout user
  static logout() {
    this.clearAuth();
    window.location.href = '/frontend-darasa/auth/login.html';
  }

  // Make authenticated API request
  static async makeAuthenticatedRequest(url, options = {}) {
    const token = this.getToken();

    if (!token) {
      throw new Error('No authentication token found');
    }

    const defaultOptions = {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        ...options.headers
      }
    };

    const response = await fetch(`${this.API_BASE_URL}${url}`, {
      ...options,
      ...defaultOptions
    });

    // If token is invalid, logout user
    if (response.status === 401 || response.status === 403) {
      this.logout();
      throw new Error('Authentication failed');
    }

    return response;
  }

  // Get user profile from server
  static async getUserProfile() {
    try {
      const response = await this.makeAuthenticatedRequest('/auth/profile');
      const data = await response.json();

      if (data.success) {
        // Update stored user data
        localStorage.setItem('userData', JSON.stringify(data.data.user));
        return data.data.user;
      } else {
        throw new Error(data.message || 'Failed to fetch user profile');
      }
    } catch (error) {
      console.error('Error fetching user profile:', error);
      throw error;
    }
  }

  // Protect routes - redirect to login if not authenticated
  static async requireAuth() {
    if (this.isAuthPage()) {
      // If on auth page and already authenticated, redirect to dashboard
      const isAuth = await this.isAuthenticated();
      if (isAuth) {
        // Make sure dashboard exists before redirecting
        window.location.href = '/frontend-darasa/dashboard.html';
        return false;
      }
      return true;
    }
    
    // For non-auth pages, redirect to login if not authenticated
    const isAuth = await this.isAuthenticated();
    if (!isAuth) {
      window.location.href = '/frontend-darasa/auth/login.html';
      return false;
    }
    return true;
  }

  // Synchronous version for immediate checks
  static requireAuthSync() {
    if (this.isAuthPage()) {
      // If on auth page and already authenticated, redirect to dashboard
      if (this.isAuthenticatedSync()) {
        window.location.href = '/frontend-darasa/dashboard.html';
        return false;
      }
      return true;
    }
    
    // For non-auth pages, redirect to login if not authenticated
    if (!this.isAuthenticatedSync()) {
      window.location.href = '/frontend-darasa/auth/login.html';
      return false;
    }
    return true;
  }

  // Check user role
  static hasRole(requiredRole) {
    const user = this.getUser();
    return user && user.role === requiredRole;
  }

  // Format user display name
  static getDisplayName() {
    const user = this.getUser();
    return user ? user.full_name : 'User';
  }

  // Get user role with capitalization
  static getUserRole() {
    const user = this.getUser();
    if (!user || !user.role) return 'User';

    return user.role.charAt(0).toUpperCase() + user.role.slice(1);
  }
}

// Export for use in other files
window.AuthUtils = AuthUtils;