class ApplicantRecords {
    constructor() {
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
            colHeaders: [
                'ID No.', 'Full Name', 'Last Name', 'First Name', 'Middle Name',
                'Ext Name', 'MI', 'Sex', 'Position Title', 'Item Number',
                'Tech Code', 'Level', 'Appointment Status', 'SG', 'Step',
                'Monthly Salary', 'Date of Birth', 'Date Orig. Appt.',
                'Date Govt Srvc', 'Date Last Promotion', 'Date Last Increment',
                'Date of Longevity'
            ],
            columns: this.getColumnDefinitions(),
            filters: true,
            dropdownMenu: true,
            contextMenu: true,
            licenseKey: 'non-commercial-and-evaluation'
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
        // Load months and divisions for filters
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

        // Load divisions
        const response = await fetch('/api/divisions.php');
        const divisions = await response.json();
        const divisionFilter = document.getElementById('division-filter-records');
        divisions.forEach(division => {
            const option = document.createElement('option');
            option.value = division.id;
            option.textContent = division.name;
            divisionFilter.appendChild(option);
        });
    }

    bindEvents() {
        document.getElementById('month-filter').addEventListener('change', () => this.applyFilters());
        document.getElementById('division-filter-records').addEventListener('change', () => this.applyFilters());
        document.getElementById('search-applicant').addEventListener('input', debounce(() => this.searchApplicants(), 300));
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
