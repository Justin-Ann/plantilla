class ApplicantRecords {
    constructor() {
        this.API_URL = 'http://localhost/HRIS/api';
        this.grid = null;
        this.initializeGrid();
        this.bindEvents();
        this.loadFilters();
    }

    initializeGrid() {
        const container = document.getElementById('applicants-grid');
        this.grid = new Handsontable(container, {
            data: [],
            rowHeaders: true,
            colHeaders: true,
            autoRowSize: true,
            autoColSize: true,
            multiSelect: true,
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
            beforeRender: function() {
                const container = this.rootElement;
                const table = container.querySelector('.htCore');
                if (table) {
                    table.setAttribute('role', 'grid');
                    table.setAttribute('aria-label', 'Applicant Records');
                }
            },
            columns: this.getColumnDefinitions(),
            filters: true,
            dropdownMenu: true,
            contextMenu: true,
            licenseKey: 'non-commercial-and-evaluation',
            
            // Disable problematic features
            customBorders: true,
            allowInvalid: false
        });
    }

    getColumnDefinitions() {
        return [
            {data: 'id_no'},
            {data: 'full_name'},
            {data: 'last_name'},
            {data: 'first_name'},
            {data: 'middle_name'},
            {data: 'ext_name'},
            {data: 'mi'},
            {data: 'sex', type: 'dropdown', source: ['Male', 'Female', 'Others']},
            {data: 'position_title'},
            {data: 'item_number'},
            {data: 'tech_code'},
            {data: 'level'},
            {data: 'appointment_status', type: 'dropdown', source: ['Temporary', 'Permanent']},
            {data: 'sg', type: 'numeric', min: 1, max: 100},
            {data: 'step', type: 'numeric', min: 1, max: 10},
            {data: 'monthly_salary', type: 'numeric', numericFormat: {pattern: '₱0,0.00'}},
            {data: 'date_of_birth', type: 'date'},
            {data: 'date_orig_appt', type: 'date'},
            {data: 'date_govt_srvc', type: 'date'},
            {data: 'date_last_promotion', type: 'date'},
            {data: 'date_last_increment', type: 'date'},
            {data: 'date_of_longevity', type: 'date'}
        ];
    }

    async loadFilters() {
        try {
            // Load months
            const months = Array.from({length: 12}, (_, i) => ({
                value: i + 1,
                text: new Date(2000, i, 1).toLocaleString('default', {month: 'long'})
            }));

            const monthFilter = document.getElementById('month-filter');
            months.forEach(month => {
                const option = document.createElement('option');
                option.value = month.value;
                option.textContent = month.text;
                monthFilter.appendChild(option);
            });

            // Load divisions with proper error handling
            const response = await fetch(`${this.API_URL}/divisions.php`);
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            
            const result = await response.json();
            if (!result.success) {
                throw new Error(result.message || 'Failed to load divisions');
            }

            const divisions = result.data;
            const divisionFilter = document.getElementById('division-filter');
            
            if (divisionFilter) {
                divisionFilter.innerHTML = '<option value="">All Divisions</option>';
                divisions.forEach(division => {
                    const option = document.createElement('option');
                    option.value = division.id;
                    option.textContent = division.name;
                    divisionFilter.appendChild(option);
                });
            }

        } catch (error) {
            console.error('Error loading filters:', error);
            this.showError('Failed to load division data. Please check server connection.');
        }
    }

    showError(message) {
        const container = document.querySelector('.filters');
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.textContent = message;
        container.prepend(errorDiv);
        
        // Remove error after 5 seconds
        setTimeout(() => errorDiv.remove(), 5000);
    }

    bindEvents() {
        const monthFilter = document.getElementById('month-filter');
        const divisionFilter = document.getElementById('division-filter-records');
        const searchApplicant = document.getElementById('search-applicant');

        if (monthFilter) monthFilter.addEventListener('change', () => this.applyFilters());
        if (divisionFilter) divisionFilter.addEventListener('change', () => this.applyFilters());
        if (searchApplicant) searchApplicant.addEventListener('input', debounce(() => this.searchApplicants(), 300));
    }

    async applyFilters() {
        const month = document.getElementById('month-filter').value;
        const division = document.getElementById('division-filter-records').value;
        const url = `/api/applicant-records.php?action=list&month=${month}&division=${division}`;
        
        try {
            const response = await fetch(url);
            const data = await response.json();
            this.grid.loadData(data);
        } catch (error) {
            console.error('Error loading filtered data:', error);
        }
    }

    async searchApplicants() {
        const searchTerm = document.getElementById('search-applicant').value;
        if (searchTerm.length < 2) return;

        try {
            const response = await fetch(`/api/applicant-records.php?action=search&term=${searchTerm}`);
            const data = await response.json();
            this.grid.loadData(data);
        } catch (error) {
            console.error('Error searching applicants:', error);
        }
    }
}

// Initialize when document is ready
document.addEventListener('DOMContentLoaded', () => {
    new ApplicantRecords();
});

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}
