#!/usr/bin/env node

/**
 * Simple HTTP Server for Testing PHP-compatible Form Submission
 * This creates a mock PHP backend to test locally before uploading to Hostinger
 */

const http = require('http');
const fs = require('fs');
const path = require('path');
const url = require('url');

const PORT = 8000;
const HOST = 'localhost';

// Simulated database for submissions
let submissions = [];

const server = http.createServer((req, res) => {
  // Set CORS headers
  res.setHeader('Access-Control-Allow-Origin', '*');
  res.setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
  res.setHeader('Access-Control-Allow-Headers', 'Content-Type');

  // Handle preflight requests
  if (req.method === 'OPTIONS') {
    res.writeHead(200);
    res.end();
    return;
  }

  const parsedUrl = url.parse(req.url, true);
  const pathname = parsedUrl.pathname;

  // Handle form submission
  if (pathname === '/submit-form.php' && req.method === 'POST') {
    let body = '';

    req.on('data', chunk => {
      body += chunk.toString();
    });

    req.on('end', () => {
      try {
        const formData = JSON.parse(body);

        // Validate required fields
        const required = ['name', 'email', 'phone', 'service', 'priority', 'description'];
        for (let field of required) {
          if (!formData[field]) {
            res.writeHead(400, { 'Content-Type': 'application/json' });
            res.end(JSON.stringify({ success: false, error: `Field '${field}' is required` }));
            return;
          }
        }

        // Validate email
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(formData.email)) {
          res.writeHead(400, { 'Content-Type': 'application/json' });
          res.end(JSON.stringify({ success: false, error: 'Invalid email address' }));
          return;
        }

        // Generate ticket ID
        const ticketId = `PRIVATE-${new Date().getFullYear()}-${Math.random().toString(36).substr(2, 6).toUpperCase()}`;

        // Store submission
        submissions.push({
          ticketId,
          ...formData,
          timestamp: new Date().toISOString(),
          status: 'open'
        });

        // Log to console
        console.log(`\n✅ Form Submission Received!`);
        console.log(`   Ticket ID: ${ticketId}`);
        console.log(`   From: ${formData.name} (${formData.email})`);
        console.log(`   Service: ${formData.service}`);
        console.log(`   Time: ${new Date().toLocaleTimeString()}\n`);

        // Return success response
        res.writeHead(200, { 'Content-Type': 'application/json' });
        res.end(JSON.stringify({
          success: true,
          ticketId: ticketId,
          message: 'Request submitted successfully. Check your email for confirmation.',
          clientEmailSent: true,
          adminEmailSent: true
        }));

      } catch (error) {
        console.error('Error processing form:', error);
        res.writeHead(500, { 'Content-Type': 'application/json' });
        res.end(JSON.stringify({ success: false, error: 'Server error' }));
      }
    });

    return;
  }

  // Handle API to view submissions (for testing)
  if (pathname === '/submissions' && req.method === 'GET') {
    res.writeHead(200, { 'Content-Type': 'application/json' });
    res.end(JSON.stringify(submissions, null, 2));
    return;
  }

  // Serve static files from web1 directory
  let filePath = './web1' + pathname;
  if (filePath === './web1/') {
    filePath = './web1/index.html';
  }

  fs.readFile(filePath, (err, data) => {
    if (err) {
      // Try without web1 prefix (for backward compatibility)
      let fallbackPath = '.' + pathname;
      if (fallbackPath === './') {
        fallbackPath = './index.html';
      }
      
      fs.readFile(fallbackPath, (err2, data2) => {
        if (err2) {
          res.writeHead(404, { 'Content-Type': 'text/plain' });
          res.end('404 - File not found');
          console.log(`❌ 404: ${pathname}`);
          return;
        }
        
        // Set content type for fallback
        let contentType = 'text/html';
        if (fallbackPath.endsWith('.js')) contentType = 'application/javascript';
        if (fallbackPath.endsWith('.css')) contentType = 'text/css';
        if (fallbackPath.endsWith('.json')) contentType = 'application/json';
        if (fallbackPath.endsWith('.png')) contentType = 'image/png';
        if (fallbackPath.endsWith('.jpg') || fallbackPath.endsWith('.jpeg')) contentType = 'image/jpeg';
        if (fallbackPath.endsWith('.gif')) contentType = 'image/gif';
        if (fallbackPath.endsWith('.svg')) contentType = 'image/svg+xml';
        if (fallbackPath.endsWith('.woff')) contentType = 'font/woff';
        if (fallbackPath.endsWith('.woff2')) contentType = 'font/woff2';
        if (fallbackPath.endsWith('.ttf')) contentType = 'font/ttf';
        
        res.writeHead(200, { 'Content-Type': contentType });
        res.end(data2);
      });
      return;
    }

    // Set content type
    let contentType = 'text/html';
    if (filePath.endsWith('.js')) contentType = 'application/javascript';
    if (filePath.endsWith('.css')) contentType = 'text/css';
    if (filePath.endsWith('.json')) contentType = 'application/json';
    if (filePath.endsWith('.png')) contentType = 'image/png';
    if (filePath.endsWith('.jpg') || filePath.endsWith('.jpeg')) contentType = 'image/jpeg';
    if (filePath.endsWith('.gif')) contentType = 'image/gif';
    if (filePath.endsWith('.svg')) contentType = 'image/svg+xml';
    if (filePath.endsWith('.woff')) contentType = 'font/woff';
    if (filePath.endsWith('.woff2')) contentType = 'font/woff2';
    if (filePath.endsWith('.ttf')) contentType = 'font/ttf';

    res.writeHead(200, { 'Content-Type': contentType });
    res.end(data);
  });
});

server.listen(PORT, HOST, () => {
  console.log(`\n╔════════════════════════════════════════════════╗`);
  console.log(`║     🚀 LOCAL TEST SERVER RUNNING               ║`);
  console.log(`╚════════════════════════════════════════════════╝\n`);
  console.log(`✅ Server running at: http://${HOST}:${PORT}`);
  console.log(`✅ Test form at: http://${HOST}:${PORT}/contact.html`);
  console.log(`✅ View submissions: http://${HOST}:${PORT}/submissions\n`);
  console.log(`Press Ctrl+C to stop the server\n`);
});

process.on('SIGINT', () => {
  console.log('\n\n✅ Server stopped.');
  process.exit(0);
});
