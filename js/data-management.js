class DataManagement {
    constructor() {
        this.initializeDivisions();
        this.bindEvents();
    }

    initializeDivisions() {
        const divisions = [
            { id: 1, name: 'Office of the Administrator' },
            { id: 2, name: 'Administrative Division' },
            // ...add all 43 divisions
        ];

        const container = document.querySelector('.divisions-list');
        divisions.forEach(div => {
            const divElement = this.createDivisionElement(div);
            container.appendChild(divElement);
        });
    }

    createDivisionElement(division) {
        const div = document.createElement('div');
        div.className = 'division-item';
        div.innerHTML = `
            <div class="division-header">
                <span class="division-code">${division.id}</span>
                <span class="division-name">${division.name}</span>
            </div>
            <div class="division-actions">
                <button class="view-data-btn">View Data</button>
                <button class="open-files-btn">Open Files</button>
            </div>
        `;

        div.querySelector('.view-data-btn').addEventListener('click', () => {
            this.loadDivisionData(division.id);
            this.switchToSpreadsheet();
        });

        return div;
    }

    bindEvents() {
        this.initializeTabs();
        this.initializeDivisionHandlers();
        
        // Delay spreadsheet initialization to ensure DOM is ready
        setTimeout(() => {
            this.initializeSpreadsheet();
        }, 100);
    }

    initializeTabs() {
        document.querySelectorAll('.tab').forEach(tab => {
            tab.addEventListener('click', () => this.switchTab(tab.dataset.tab));
        });
    }

    initializeDivisionHandlers() {
        document.querySelectorAll('.division-item').forEach(div => {
            div.addEventListener('click', () => {
                this.loadDivisionData(div.dataset.code);
                this.switchTab('spreadsheet');
            });
        });
    }

    initializeSpreadsheet() {
        const container = document.getElementById('spreadsheet-container');
        if (!container) {
            console.warn('Spreadsheet container not found, waiting for DOM...');
            return;
        }

        // Create wrapper for proper sizing
        container.innerHTML = '<div id="spreadsheet-grid" style="width: 100%; height: 100%;"></div>';
        
        if (typeof PlantillaSpreadsheet !== 'undefined') {
            this.spreadsheet = new PlantillaSpreadsheet('spreadsheet-grid');
        }
    }

    async loadDivisionData(divisionCode) {
        await this.spreadsheet.filterByDivision(divisionCode);
    }

    switchTab(tabName) {
        document.querySelectorAll('.tab-content').forEach(content => {
            content.style.display = 'none';
        });
        document.getElementById(`${tabName}-content`).style.display = 'block';
    }

    switchToSpreadsheet() {
        this.switchTab('spreadsheet');
    }
}

new DataManagement();
