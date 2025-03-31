const Dashboard = {
    init() {
        this.loadStatusCounts();
        this.loadMonthlyFiles();
        this.loadRecentFiles();
    },

    async loadStatusCounts() {
        try {
            const response = await fetch('/api/status-counts.php');
            const data = await response.json();
            
            // Update status counts in UI
            document.getElementById('onProcess').textContent = data.onProcess;
            document.getElementById('onHold').textContent = data.onHold;
            document.getElementById('notYetFilling').textContent = data.notYetFilling;
        } catch (error) {
            console.error('Error loading status counts:', error);
        }
    },

    async loadMonthlyFiles() {
        try {
            const response = await fetch('/api/monthly-files.php');
            const files = await response.json();
            
            const container = document.getElementById('monthly-files');
            container.innerHTML = files.map(file => `
                <div class="file-item">
                    <span>${file.filename}</span>
                    <div class="file-actions">
                        <button onclick="openFile('${file.id}')">Open</button>
                        <button onclick="deleteFile('${file.id}')">Delete</button>
                    </div>
                </div>
            `).join('');
        } catch (error) {
            console.error('Error loading monthly files:', error);
        }
    },

    async loadRecentFiles() {
        try {
            const response = await fetch('/api/recent-files.php');
            const files = await response.json();
            
            const container = document.getElementById('recent-files');
            container.innerHTML = files.map(file => `
                <div class="recent-file" onclick="openFile('${file.id}')">
                    <span>${file.filename}</span>
                    <span>Last edited: ${file.last_edited}</span>
                    <span>By: ${file.editor_name}</span>
                </div>
            `).join('');
        } catch (error) {
            console.error('Error loading recent files:', error);
        }
    }
};

document.addEventListener('DOMContentLoaded', () => Dashboard.init());
