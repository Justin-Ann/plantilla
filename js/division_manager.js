class DivisionManager {
    constructor() {
        this.currentDivision = null;
        this.bindEvents();
    }

    initializeDivisions() {
        const divisions = [
            { code: 1, name: 'Office of the Administrator' },
            { code: 2, name: 'Administrative Division' },
            { code: 3, name: 'Human Resources Management and Development Section' },
            { code: 4, name: 'Records Management Section' },
            { code: 5, name: 'Procurement, Property and General Services Section' },
            { code: 6, name: 'Financial, Planning and Management Division' },
            { code: 7, name: 'Accounting Section' },
            { code: 8, name: 'Budget and Planning Section' },
            { code: 9, name: 'Management Services Section' },
            { code: 10, name: 'Engineering and Technical Services Division' },
            { code: 11, name: 'Meteorological Equipment and Telecommunications Technology Services Section' },
            { code: 12, name: 'Meteorological Guides and Standards Section' },
            { code: 13, name: 'Mechanical, Electrical and Infrastructure Engineering Section' },
            { code: 14, name: 'Weather Division' },
            { code: 15, name: 'Weather Forecasting Section' },
            { code: 16, name: 'Meteorological Data and Information Exchange Section' },
            { code: 17, name: 'Techniques Application and Meteorological Satellite Section' },
            { code: 18, name: 'Aeronautical Meteorological Satellite Section' },
            { code: 19, name: 'Marine Meteorological Services Section' },
            { code: 20, name: 'Hydro-Meteorological Division' },
            { code: 21, name: 'Hydrometeorological Data Applications Sections' },
            { code: 22, name: 'Flood Forecasting and Warning Section' },
            { code: 23, name: 'Hydrometeorological Telemetry Section' },
            { code: 24, name: 'Climatology and Agrometeorology Division' },
            { code: 25, name: 'Climate Monitoring and Prediction Section' },
            { code: 26, name: 'Farm Weather Services Section' },
            { code: 27, name: 'Impact Assessment and Applications Section' },
            { code: 28, name: 'Climate and Agrometeorology Data Section' },
            { code: 29, name: 'Research and Development and Training Division' },
            { code: 30, name: 'Astronomy and Space Sciences Section' },
            { code: 31, name: 'Climate and Agrometeorology Research and Development Section' },
            { code: 32, name: 'Hydrometeorology, Tropical Meteorology and Instrument Research and Development' },
            { code: 33, name: 'Numerical Modeling Section' },
            { code: 34, name: 'Training and Public Information Section' },
            { code: 35, name: 'Northern Luzon PAGASA Regional Services Division' },
            { code: 36, name: 'Agno Flood Forecasting and Warning System' },
            { code: 37, name: 'Pampanga Flood Forecasting and Warning System' },
            { code: 38, name: 'Southern Luzon PAGASA Regional Services Division' },
            { code: 39, name: 'Bicol Flood Forecasting and Warning System' },
            { code: 40, name: 'Visayas PAGASA Regional Services Division' },
            { code: 41, name: 'Northern Mindanao PAGASA Regional Services Division' },
            { code: 42, name: 'Southern Mindanao PAGASA Regional Services Division' },
            { code: 43, name: 'Field Stations' }
        ];

        const container = $('.division-list');
        container.empty();

        divisions.forEach(div => {
            container.append(`
                <div class="division-item" data-division-code="${div.code}">
                    <span class="division-code">${div.code}</span>
                    <span class="division-name">${div.name}</span>
                    <button class="open-files-btn">Open Files</button>
                </div>
            `);
        });
    }

    bindEvents() {
        // View Data button click
        $('.view-data-btn').on('click', (e) => {
            e.stopPropagation();
            const divisionCode = $(e.target).closest('.division-item').data('division-code');
            this.loadDivisionData(divisionCode);
            // Switch to spreadsheet tab
            $('.tab[data-tab="spreadsheet"]').click();
        });

        // Open Files button click
        $('.open-files-btn').on('click', (e) => {
            e.stopPropagation();
            const divisionCode = $(e.target).closest('.division-item').data('division-code');
            this.openDivisionFiles(divisionCode);
        });

        // Load division files button
        $('#load-division-files').on('click', () => {
            const monthYear = $('#division-month-filter').val();
            if (!monthYear) {
                alert('Please select a month first');
                return;
            }
            this.loadAllDivisionFiles(monthYear);
        });
    }

    loadDivisionData(divisionCode) {
        const monthYear = $('#division-month-filter').val();
        $.ajax({
            url: `${API_URL}/divisions/${divisionCode}/data`,
            method: 'GET',
            data: { month_year: monthYear },
            success: (response) => {
                if (response.success) {
                    this.displayDivisionData(response.data);
                }
            }
        });
    }

    openDivisionFiles(divisionCode) {
        const monthYear = $('#division-month-filter').val();
        if (!monthYear) {
            alert('Please select a month first');
            return;
        }

        $.ajax({
            url: `${API_URL}/divisions/${divisionCode}/files`,
            method: 'GET',
            data: { month_year: monthYear },
            success: (response) => {
                if (response.success) {
                    this.showFilesModal(response.files, divisionCode);
                }
            }
        });
    }

    displayDivisionData(data) {
        const table = $('#spreadsheet-table');
        table.empty();
        
        if (!data.length) {
            table.html('<tr><td colspan="100%">No data found for this division</td></tr>');
            return;
        }

        // Create headers
        const headers = Object.keys(data[0]);
        const headerRow = $('<tr>');
        headers.forEach(header => {
            headerRow.append($('<th>').text(header));
        });
        table.append($('<thead>').append(headerRow));

        // Create body
        const tbody = $('<tbody>');
        data.forEach(row => {
            const tr = $('<tr>');
            headers.forEach(header => {
                tr.append($('<td>').text(row[header] || ''));
            });
            tbody.append(tr);
        });
        table.append(tbody);
    }

    showFilesModal(files, divisionCode) {
        // Remove existing modal if any
        $('#division-files-modal').remove();

        const modal = $(`
            <div id="division-files-modal" class="modal">
                <div class="modal-content">
                    <span class="close">&times;</span>
                    <h3>Division Files</h3>
                    <div class="files-list">
                        ${files.map(file => `
                            <div class="file-item">
                                <span>${file.filename}</span>
                                <button onclick="divisionManager.openFile(${file.id})">
                                    Open
                                </button>
                            </div>
                        `).join('')}
                    </div>
                </div>
            </div>
        `);

        $('body').append(modal);
        modal.show();

        // Close modal handler
        modal.find('.close').on('click', () => modal.remove());
    }

    openFile(fileId) {
        window.location.href = `/data-management?file=${fileId}`;
    }
}

// Initialize division manager when document is ready
$(document).ready(() => {
    window.divisionManager = new DivisionManager();
});
