class Dashboard {
    constructor() {
        this.API_URL = 'http://localhost/HRIS/api';
        this.initializeCounters();
    }

    async initializeCounters() {
        try {
            const response = await fetch(`${this.API_URL}/status-counts.php`);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            if (!data.success) {
                throw new Error(data.message || 'Failed to fetch status counts');
            }

            this.updateCounters(data.data);
        } catch (error) {
            console.error('Error fetching status counts:', error);
            this.showError('Failed to load status counts. Please refresh the page.');
        }
    }

    updateCounters(counts) {
        document.getElementById('onProcess').textContent = counts.onProcess;
        document.getElementById('onHold').textContent = counts.onHold;
        document.getElementById('notYetFilling').textContent = counts.notYetFilling;
    }

    showError(message) {
        const dashboard = document.getElementById('dashboard');
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.textContent = message;
        dashboard.prepend(errorDiv);
    }

    async init() {
        await this.loadStatusCounts();
        await this.loadMonthlyFiles();
        await this.loadRecentFiles();
    }

    destroy() {
        if (this.refreshInterval) {
            clearInterval(this.refreshInterval);
            this.refreshInterval = null;
        }
        this.initialized = false;
    }

    startAutoRefresh() {
        setInterval(() => {
            this.loadStatusCounts();
            this.loadRecentFiles();
        }, 30000); // Refresh every 30 seconds
    }

    async loadStatusCounts() {
        try {
            const response = await fetch(`${this.API_URL}/status-counts.php`);
            const data = await response.json();
            
            if (data.success) {
                document.getElementById('onProcess').textContent = data.data.onProcess || '0';
                document.getElementById('onHold').textContent = data.data.onHold || '0';
                document.getElementById('notYetFilling').textContent = data.data.notYetFilling || '0';
            }
        } catch (error) {
            console.error('Error loading status counts:', error);
        }
    }

    async loadMonthlyFiles(month = null) {
        const currentMonth = month || document.getElementById('month-picker').value;
        try {
            const response = await fetch(`${this.API_URL}/dashboard.php?action=monthly-files&month=${currentMonth}`);
            const data = await response.json();
            if (data.success) {
                this.displayMonthlyFiles(data.data);
            }
        } catch (error) {
            console.error('Error loading monthly files:', error);
        }
    }

    displayMonthlyFiles(files) {
        const container = document.getElementById('monthly-files');
        container.innerHTML = '';
        
        files.forEach(file => {
            const fileItem = document.createElement('div');
            fileItem.className = 'file-item';
            fileItem.innerHTML = `
                <div class="file-info">
                    <span class="filename">${file.original_filename}</span>
                    <span class="upload-date">Uploaded: ${new Date(file.upload_date).toLocaleDateString()}</span>
                </div>
                <div class="file-actions">
                    <button onclick="Dashboard.openFile(${file.id})" class="btn-primary">
                        <i class="material-icons">open_in_new</i> Open
                    </button>
                    <button onclick="Dashboard.deleteFile(${file.id})" class="btn-danger">
                        <i class="material-icons">delete</i>
                    </button>
                </div>
            `;
            container.appendChild(fileItem);
        });
    }

    async loadRecentFiles() {
        try {
            const response = await fetch(`${this.API_URL}/dashboard.php?action=recent-files`);
            const data = await response.json();
            if (data.success) {
                this.displayRecentFiles(data.data);
            }
        } catch (error) {
            console.error('Error loading recent files:', error);
        }
    }

    displayRecentFiles(files) {
        const container = document.getElementById('recent-files');
        container.innerHTML = '';
        
        files.forEach(file => {
            const fileItem = document.createElement('div');
            fileItem.className = 'recent-file';
            fileItem.onclick = () => this.openFile(file.id);
            fileItem.innerHTML = `
                <div class="file-name">${file.original_filename}</div>
                <div class="modified-date">Last modified: ${new Date(file.last_modified).toLocaleString()}</div>
            `;
            container.appendChild(fileItem);
        });
    }

    initializeUploadHandlers() {
        const uploadForm = document.getElementById('upload-form');
        if (!uploadForm) return;

        uploadForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(uploadForm);
            const submitButton = uploadForm.querySelector('button[type="submit"]');
            const originalText = submitButton.textContent;
            
            try {
                submitButton.disabled = true;
                submitButton.textContent = 'Uploading...';
                
                const response = await fetch(`${this.API_URL}/upload.php`, {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (!response.ok) throw new Error(result.message || 'Upload failed');
                
                if (result.success) {
                    alert('File uploaded successfully!');
                    uploadForm.reset();
                    this.loadMonthlyFiles();
                    this.loadRecentFiles();
                    
                    // Close modal if exists
                    const modal = document.getElementById('upload-modal');
                    if (modal) modal.style.display = 'none';
                } else {
                    throw new Error(result.message || 'Upload failed');
                }
                
            } catch (error) {
                console.error('Upload error:', error);
                alert(error.message || 'Error uploading file. Please try again.');
            } finally {
                submitButton.disabled = false;
                submitButton.textContent = originalText;
            }
        });
    }

    openFile(fileId) {
        // Switch to data management page and open file
        document.querySelector('.nav-menu a[data-page="data-management"]').click();
        document.querySelector('.tab[data-tab="spreadsheet"]').click();
        loadFileContent(fileId);
    }

    async deleteFile(fileId) {
        if (!confirm('Are you sure you want to delete this file?')) return;
        
        try {
            const response = await fetch(`${this.API_URL}/files.php?id=${fileId}`, {
                method: 'DELETE'
            });
            const result = await response.json();
            
            if (result.success) {
                this.loadMonthlyFiles();
                this.loadRecentFiles();
            } else {
                alert('Error deleting file: ' + result.message);
            }
        } catch (error) {
            console.error('Delete error:', error);
            alert('Error deleting file');
        }
    }
}

// Initialize dashboard
document.addEventListener('DOMContentLoaded', () => {
    if (window.Dashboard) {
        Dashboard.init();
    }
});
