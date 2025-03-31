class DataManagement {
    constructor() {
        this.initializeTabs();
        this.initializeDivisionHandlers();
        if (typeof PlantillaSpreadsheet !== 'undefined') {
            this.spreadsheet = new PlantillaSpreadsheet('spreadsheet-container');
        }
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

    async loadDivisionData(divisionCode) {
        await this.spreadsheet.filterByDivision(divisionCode);
    }

    switchTab(tabName) {
        document.querySelectorAll('.tab-content').forEach(content => {
            content.style.display = 'none';
        });
        document.getElementById(`${tabName}-content`).style.display = 'block';
    }
}

new DataManagement();
