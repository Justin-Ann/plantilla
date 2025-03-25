const express = require('express');
const multer = require('multer');
const cors = require('cors');
const app = express();

// Configure CORS
app.use(cors());

// Configure multer for file upload
const storage = multer.diskStorage({
    destination: function (req, file, cb) {
        cb(null, 'uploads/');
    },
    filename: function (req, file, cb) {
        cb(null, Date.now() + '-' + file.originalname);
    }
});

const upload = multer({ storage: storage });

// File upload endpoint
app.post('/api/upload', upload.single('file'), (req, res) => {
    try {
        if (!req.file) {
            return res.status(400).json({
                success: false,
                message: 'No file uploaded'
            });
        }
        
        res.json({
            success: true,
            message: 'File uploaded successfully',
            file: req.file
        });
    } catch (error) {
        res.status(500).json({
            success: false,
            message: 'Error uploading file'
        });
    }
});

const PORT = 5000;
app.listen(PORT, () => {
    console.log(`Server running on port ${PORT}`);
});
