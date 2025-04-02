class SpreadsheetManager {
    constructor() {
        this.initializeDropdowns();
        this.bindEvents();
    }

    initializeDropdowns() {
        this.dropdowns = {
            sex: ['Male', 'Female', 'Others'],
            status: ['Temporary', 'Permanent'],
            vacated_due_to: ['Promotion', 'Retirement', 'Resignation', 'Transfer'],
            sg: Array.from({length: 100}, (_, i) => i + 1),
            step: Array.from({length: 10}, (_, i) => i + 1)
        };
    }

    bindEvents() {
        // Make cells editable on click
        $('#spreadsheet-table').on('click', 'td[data-editable="true"]', (e) => {
            const cell = $(e.target);
            const type = cell.data('type');
            this.showEditor(cell, type);
        });
    }

    showEditor(cell, type) {
        switch(type) {
            case 'sex':
            case 'status':
            case 'vacated_due_to':
                this.showDropdown(cell, this.dropdowns[type]);
                break;
            case 'date':
                this.showDatePicker(cell);
                break;
            case 'currency':
                this.showCurrencyEditor(cell);
                break;
            // Add other editor types
        }
    }

    // Add editor methods
}

class SpreadsheetEditor {
    constructor(containerId) {
        this.container = document.getElementById(containerId);
        this.initializeGrid();
        this.setupEventListeners();
    }

    initializeGrid() {
        // Create Excel-like interface
    }

    setupDropdowns() {
        // Setup column-specific dropdowns
        const dropdowns = {
            'sex': ['Male', 'Female', 'Others'],
            'appointment_status': ['Temporary', 'Permanent'],
            'sg': Array.from({length: 100}, (_, i) => i + 1),
            'step': Array.from({length: 10}, (_, i) => i + 1),
            'vacated_due_to': ['Promotion', 'Retirement', 'Resignation', 'Transfer']
        };
        // ... implementation
    }
}

class PlantillaSpreadsheet {
    constructor(containerId) {
        this.container = document.getElementById(containerId);
        this.API_URL = 'http://localhost/HRIS/api';
        this.init();
    }

    async init() {
        try {
            await this.loadDivisions();
            this.setupGrid();
            this.bindEvents();
        } catch (error) {
            console.error('Initialization error:', error);
            this.handleError(error);
        }
    }

    async loadDivisions() {
        try {
            const response = await fetch(`${API_URL}/divisions.php`);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const data = await response.json();
            
            if (!data.success) {
                throw new Error(data.message || 'Failed to load divisions');
            }
            
            this.divisions = data.data;
            return data.data;
        } catch (error) {
            console.error('Spreadsheet error:', {
                message: error.message,
                stack: error.stack,
                type: error.name
            });
            
            // Show user-friendly error message
            this.showError('Unable to load division data. Please check your database connection.');
            throw error;
        }
    }

    showError(message) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.textContent = message;
        
        // Remove any existing error messages
        const existingError = this.container.querySelector('.error-message');
        if (existingError) {
            existingError.remove();
        }
        
        this.container.prepend(errorDiv);
    }

    handleError(error) {
        // Display user-friendly error message
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.textContent = 'Failed to load data. Please check your connection and try again.';
        this.container.appendChild(errorDiv);
        
        // Log detailed error for debugging
        console.error('Spreadsheet error:', {
            message: error.message,
            stack: error.stack,
            type: error.name
        });
    }

    getDivisionList() {
        return this.divisions?.map(d => d.name) || [];
    }

    setupGrid() {
        this.grid = new Handsontable(this.container, {
            data: this.data,
            rowHeaders: true,
            colHeaders: true,
            // Remove invalid ARIA attributes
            autoRowSize: true,
            autoColSize: true,
            multiSelect: true,
            // Use valid ARIA roles
            cells: function(row, col) {
                const cellProperties = {};
                cellProperties.role = 'gridcell';
                if (row === 0) {
                    cellProperties.role = 'columnheader';
                }
                return cellProperties;
            },
            beforeRenderer: function(TD, row, col) {
                if (row === 0) {
                    TD.setAttribute('role', 'columnheader');
                } else {
                    TD.setAttribute('role', 'gridcell');
                }
            },
            // Add proper table structure
            beforeRender: function() {
                const container = this.rootElement;
                const table = container.querySelector('.htCore');
                if (table) {
                    table.setAttribute('role', 'grid');
                    table.setAttribute('aria-label', 'Plantilla Data');
                }
            }
        });
    }

    setupDropdowns() {
        // Setup dropdown values for various columns
        this.dropdowns = {
            sex: ['Male', 'Female', 'Others'],
            status: ['Temporary', 'Permanent'],
            vacatedDueTo: ['Promotion', 'Retirement', 'Resignation', 'Transfer']
        };
    }

    bindEvents() {
        // Event handlers for grid interactions
    }

    async filterByDivision(division) {
        try {
            const response = await fetch(`/api/division-records.php?division=${division}`);
            const records = await response.json();
            this.grid.loadData(records);
        } catch (error) {
            console.error('Error filtering division:', error);
        }
    }

    async handleDataChange(changes) {
        if (!changes) return;
        
        const updatedData = changes.map(([row, prop, oldValue, newValue]) => ({
            row,
            prop,
            oldValue,
            newValue,
            record_id: this.data[row].id
        }));

        try {
            await fetch('/api/update-records.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(updatedData)
            });
        } catch (error) {
            console.error('Error updating records:', error);
        }
    }
}

// Initialize only if container exists
document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('spreadsheet-container');
    if (container) {
        new PlantillaSpreadsheet('spreadsheet-container');
    }
});
