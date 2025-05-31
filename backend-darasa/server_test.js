
//Load the http module
const http = require('http');

//Create a server
const server = http.createServer((req, res) => {
  res.statusCode = 200;
    res.setHeader('Content-Type', 'text/plain');
    res.end('Hello, Amani! Welcome to Darasa backend.');
});

//Start the server

server.listen(3000, () => {
    console.log('Server running at http://localhost:3000/');
    
    });
// This code creates a simple HTTP server that listens on port 3000 and responds with a welcome message.