class AuthUtils {
  static API_BASE_URL = 'http://localhost:3000/api';
  
  // Get stored authentication token
  static getToken() {
    return localStorage.getItem('authToken');
  }
  
  // Get stored user data
  static getUser() {
    const userData = localStorage.getItem('userData');
    return userData ? JSON.parse(userData) : null;
  }
  
  // Check if user is authenticated
  static isAuthenticated() {
    const token = this.getToken();
    const user = this.getUser();
    return !!(token && user);
  }
  
  // Clear authentication data
  static logout() {
    localStorage.removeItem('authToken');
    localStorage.removeItem('userData');
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
  static requireAuth() {
    if (!this.isAuthenticated()) {
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