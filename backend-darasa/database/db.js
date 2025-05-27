const { createClient } = require('@supabase/supabase-js');
const config = require('../middleware/config');
require('dotenv').config();

// const supabaseUrl = process.env.SUPABASE_URL;
// const supabaseKey = process.env.SUPABASE_ANON_KEY;

// Validate environment variables
if (!config.supabase.url || !config.supabase.key) {
  throw new Error('Missing Supabase configuration. Please check your .env file.');
}

// Create Supabase client with proper configuration
const supabase = createClient(
  config.supabase.url,
  config.supabase.key,
  {
    auth: {
      persistSession: false,
      autoRefreshToken: false
    }
  }
);

// Test connection without exposing credentials
async function testConnection() {
  try {
    const { data, error } = await supabase
      .from('users')
      .select('count', { count: 'exact', head: true });
    
    if (error) throw error;
    console.log('Successfully connected to Supabase');
  } catch (err) {
    console.error('Supabase connection error:', err.message);
  }
}

testConnection();

module.exports = supabase;

