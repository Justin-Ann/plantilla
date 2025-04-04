document.addEventListener('DOMContentLoaded', () => {
    // Load initial data
    loadStatusCounts();
    loadMonthlyFiles();
    loadRecentFiles();

    // Initialize month picker
    const monthPicker = document.getElementById('month-picker');
    if (monthPicker) {
        const now = new Date();
        const currentMonth = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}`;
        monthPicker.value = currentMonth;
        
        monthPicker.addEventListener('change', () => {
            loadMonthlyFiles(monthPicker.value);
        });
    }

    // Setup modal handlers
    const uploadBtn = document.getElementById('upload-btn');
    const uploadModal = document.getElementById('upload-modal');
    const closeBtn = uploadModal?.querySelector('.close');
    const uploadForm = document.getElementById('upload-form');

    if (uploadBtn && uploadModal) {
        uploadBtn.addEventListener('click', () => {
            uploadModal.style.display = 'block';
        });
    }

    if (closeBtn) {
        closeBtn.addEventListener('click', () => {
            uploadModal.style.display = 'none';
        });
    }

    // Close modal on outside click
    window.addEventListener('click', (e) => {
        if (e.target === uploadModal) {
            uploadModal.style.display = 'none';
        }
    });

    // Setup status card click handlers
    document.querySelectorAll('.status-card').forEach(card => {
        card.addEventListener('click', function() {
            const status = this.dataset.status;
            if (status) {
                window.location.href = `data-management.php?status=${status}`;
            }
        });
    });
});

function loadStatusCounts() {
    fetch('../api/dashboard_handler.php?action=status-counts')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Object.entries(data.counts).forEach(([status, count]) => {
                    const element = document.getElementById(status.toLowerCase().replace(/\s+/g, '-'));
                    if (element) {
                        element.querySelector('.count').textContent = count;
                    }
                });
            }
        })
        .catch(error => console.error('Error loading status counts:', error));
}

function loadMonthlyFiles(month) {
    fetch(`../api/dashboard_handler.php?action=monthly-files&month=${month || ''}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const container = document.getElementById('monthly-files-list');
                container.innerHTML = data.files.map(file => `
                    <div class="file-item">
                        <span>${file.filename}</span>
                        <div class="file-actions">
                            <button onclick="openFile('${file.id}')">Open</button>
                            <button onclick="deleteFile('${file.id}')">Delete</button>
                        </div>
                    </div>
                `).join('');
            } else {
                const container = document.getElementById('monthly-files-list');
                container.innerHTML = `<div class="error-message">Failed to load monthly files. Please try again.</div>`;
            }
        })
        .catch(error => {
            console.error('Error loading monthly files:', error);
            const container = document.getElementById('monthly-files-list');
            container.innerHTML = `<div class="error-message">Failed to load monthly files. Please try again.</div>`;
        });
}

function loadRecentFiles() {
    fetch('../api/dashboard_handler.php?action=recent-files')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const recentList = document.getElementById('recent-files-list');
                recentList.innerHTML = data.files.map(file => `
                    <div class="recent-file" onclick="openFile(${file.id})">
                        <div class="file-name">${file.filename}</div>
                        <div class="modified-date">
                            Last modified: ${new Date(file.last_edited).toLocaleString()}
                            by ${file.last_editor}
                        </div>
                    </div>
                `).join('') || '<div class="no-files">No recent files found</div>';
            } else {
                const recentList = document.getElementById('recent-files-list');
                recentList.innerHTML = '<div class="error-message">Failed to load recent files. Please try again.</div>';
            }
        })
        .catch(error => {
            console.error('Error loading recent files:', error);
            const recentList = document.getElementById('recent-files-list');
            recentList.innerHTML = '<div class="error-message">Failed to load recent files. Please try again.</div>';
        });
}

function uploadFile(event) {
    event.preventDefault();
    const formData = new FormData(event.target);
    fetch('api/dashboard_handler.php?action=upload-file', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('File uploaded successfully');
            loadMonthlyFiles();
        } else {
            alert('Failed to upload file');
        }
    })
    .catch(error => {
        console.error('Error uploading file:', error);
        alert('Failed to upload file');
    });
}
