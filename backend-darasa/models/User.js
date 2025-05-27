const supabase = require('../database/db');
const bcrypt = require('bcryptjs');
const config = require('../middleware/config');

class User {
  constructor(userData) {
    this.id = userData.id;
    this.full_name = userData.full_name;
    this.email = userData.email;
    this.password = userData.password;
    // this.role = userData.role;
    this.created_at = userData.created_at;
    // this.updated_at = userData.updated_at;
  }

  // Create new user
  static async create(userData) {
    try {
    const { full_name, email, password } = userData;
    
    // Hash password
    const hashedPassword = await bcrypt.hash(password, config.bcryptRounds);
    
    const { data, error } = await supabase
      .from('users')
      .insert({
        full_name,
        email,
        password: hashedPassword
      })
      .select('id, full_name, email,created_at')
      .single();

    if (error) {
     console.error('Supabase insert error:', error);
      throw new Error('Failed to create user');
  }
   return new User(data);
    }catch (err) {
      console.error('Error creating user:', err);
      throw new Error('Database operation failed');
    }
  }

  // Find user by email
  static async findByEmail(email) {
    try{
    const { data, error } = await supabase
      .from('users')
      .select('*')
      .eq('email', email)
      .single();

       if (error) {
        console.error('Supabase query error:', error);
        return null;
      }
    
    return data ? new User(data) : null;
    }catch (err) {
      console.error('Error finding user by email:', err);
      throw new Error('Database query failed');
    }
  }

  // Find user by ID
  static async findById(id) {
    const { data, error } = await supabase
      .from('users')
      .select('id, full_name, email, created_at')
      .eq('id', id)
      .single();

    if (error) {
      if (error.code === 'PGRST116') return null;
      throw error;
    }
    
    return new User(data);
  }

  // Verify password
  async verifyPassword(password) {
    return await bcrypt.compare(password, this.password);
  }

  // Get user without password
  toJSON() {
    const { password, ...userWithoutPassword } = this;
    return userWithoutPassword;
  }
}

module.exports = User;