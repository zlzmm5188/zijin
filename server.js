const express = require('express');
const path = require('path');

const app = express();
const PORT = process.env.PORT || 1337;

// Serve static files from providence directory (frontend)
app.use('/', express.static(path.join(__dirname, 'providence')));

// Serve admin panel
app.use('/admin', express.static(path.join(__dirname, 'providence-admin', 'admin')));

// Health check endpoint
app.get('/health', (req, res) => {
  res.json({ status: 'ok', timestamp: new Date().toISOString() });
});

// Start server
app.listen(PORT, '0.0.0.0', () => {
  console.log(`Server running on port ${PORT}`);
});
