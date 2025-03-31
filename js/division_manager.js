class DivisionManager {
    constructor() {
        this.bindEvents();
        this.currentDivision = null;
    }

    bindEvents() {
        // Division click handlers
        $('.division-item').on('click', (e) => {
            const divisionCode = $(e.currentTarget).data('division-code');
            this.loadDivisionData(divisionCode);
        });

        // Month filter change handler
        $('#division-month-filter').on('change', (e) => {
            if (this.currentDivision) {
                this.loadDivisionData(this.currentDivision, $(e.target).val());
            }
        });
    }

    loadDivisionData(divisionCode, monthYear = null) {
        this.currentDivision = divisionCode;
        const params = new URLSearchParams({
            division_code: divisionCode,
            month_year: monthYear || $('#division-month-filter').val()
        });

        $.ajax({
            url: `${API_URL}/divisions/data?${params}`,
            method: 'GET',
            success: (response) => {
                if (response.success) {
                    this.displayDivisionData(response.data);
                    // Switch to spreadsheet tab
                    $('.tab[data-tab="spreadsheet"]').click();
                }
            },
            error: (xhr) => {
                console.error('Error loading division data:', xhr);
                alert('Error loading division data');
            }
        });
    }

    displayDivisionData(data) {
        const table = $('#spreadsheet-table');
        table.empty();

        // Create headers
        const headers = [
            'PLANTILLA NO.', 'DIVISION', 'SECTION', 'POSITION TITLE',
            'SG', 'STEP', 'STATUS', 'REMARKS', 'ACTIONS'
        ];

        const headerRow = $('<tr>');
        headers.forEach(header => {
            headerRow.append($('<th>').text(header));
        });
        table.append($('<thead>').append(headerRow));

        // Create body
        const tbody = $('<tbody>');
        data.forEach(row => {
            const tr = $('<tr>');
            tr.append(`
                <td>${row.plantilla_no}</td>
                <td>${row.plantilla_division}</td>
                <td>${row.plantilla_section}</td>
                <td>${row.position_title}</td>
                <td data-type="sg">${row.sg}</td>
                <td data-type="step">${row.step}</td>
                <td data-type="status">${row.status}</td>
                <td data-type="remarks">${row.remarks}</td>
                <td>
                    <button class="action-btn edit-btn" onclick="editRow(${row.id})">Edit</button>
                </td>
            `);
            tbody.append(tr);
        });
        table.append(tbody);

        // Initialize editors for editable cells
        initializeEditors($('#spreadsheet-table'));
    }
}

// Initialize division manager when document is ready
$(document).ready(() => {
    window.divisionManager = new DivisionManager();
});
