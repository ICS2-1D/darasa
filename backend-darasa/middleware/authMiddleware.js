const jwt = require('jsonwebtoken');
const config = require('../middleware/config');
const User = require('../models/User');

const authenticateToken = async (req, res, next) => {
  try {
    const authHeader = req.headers['authorization'];
    const token = authHeader && authHeader.split(' ')[1];

    if (!token) {
      return res.status(401).json({
        success: false,
        message: 'Access token required'
      });
    }

    const decoded = jwt.verify(token, config.jwtSecret);
    
    // Optional: Verify user still exists in database
    const user = await User.findById(decoded.id);
    if (!user) {
      return res.status(401).json({
        success: false,
        message: 'User no longer exists'
      });
    }

    req.user = decoded;
    next();
  } catch (error) {
    return res.status(403).json({
      success: false,
      message: 'Invalid or expired token'
    });
  }
};

// const requireRole = (roles) => {
//   return (req, res, next) => {
//     if (!req.user) {
//       return res.status(401).json({
//         success: false,
//         message: 'Authentication required'
//       });
//     }

//     const userRole = req.user.role;
//     const allowedRoles = Array.isArray(roles) ? roles : [roles];

//     if (!allowedRoles.includes(userRole)) {
//       return res.status(403).json({
//         success: false,
//         message: 'Insufficient permissions'
//       });
//     }

//     next();
//   };
// };

module.exports = {
  authenticateToken,
  // requireRole
};