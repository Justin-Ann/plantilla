class DivisionHandler {
    constructor() {
        this.bindEvents();
    }

    bindEvents() {
        // Division item click handler
        $('.division-item').on('click', (e) => {
            if (!$(e.target).hasClass('open-files-btn')) {
                const divisionCode = $(e.currentTarget).data('division-code');
                const divisionName = $(e.currentTarget).data('division-name');
                this.loadDivisionData(divisionCode, divisionName);
            }
        });

        // Open files button click handler
        $('.open-files-btn').on('click', (e) => {
            e.stopPropagation();
            const divisionItem = $(e.target).closest('.division-item');
            const divisionCode = divisionItem.data('division-code');
            const divisionName = divisionItem.data('division-name');
            this.openDivisionFiles(divisionCode, divisionName);
        });

        // Month filter change handler
        $('#division-month-filter').on('change', () => {
            const selectedDivision = $('.division-item.active');
            if (selectedDivision.length) {
                const divisionCode = selectedDivision.data('division-code');
                const divisionName = selectedDivision.data('division-name');
                this.loadDivisionData(divisionCode, divisionName);
            }
        });
    }

    loadDivisionData(divisionCode, divisionName) {
        const monthYear = $('#division-month-filter').val();
        if (!monthYear) {
            alert('Please select a month first');
            return;
        }

        // Switch to spreadsheet tab
        $('.tab[data-tab="spreadsheet"]').click();

        // Load data from PLANTILLA DIVISIONDEFINITION
        $.ajax({
            url: `${API_URL}/divisions/${divisionCode}/data`,
            method: 'GET',
            data: { 
                month_year: monthYear,
                division_name: divisionName 
            },
            success: (response) => {
                if (response.success) {
                    this.displayDivisionData(response.data);
                    $('.division-item').removeClass('active');
                    $(`.division-item[data-division-code="${divisionCode}"]`).addClass('active');
                } else {
                    alert('Error loading division data');
                }
            },
            error: () => alert('Error loading division data')
        });
    }

    openDivisionFiles(divisionCode, divisionName) {
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
                    this.showFilesModal(response.files, divisionCode, divisionName);
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
        const headers = ['PLANTILLA NO.', 'POSITION TITLE', 'SG', 'STEP', 'STATUS', 'REMARKS', 'ACTIONS'];
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
                <td>${row.plantilla_no || ''}</td>
                <td>${row.position_title || ''}</td>
                <td data-type="sg">${row.sg || ''}</td>
                <td data-type="step">${row.step || ''}</td>
                <td data-type="status">${row.status || ''}</td>
                <td data-type="remarks">${row.remarks || ''}</td>
                <td>
                    <button class="action-btn edit-btn" onclick="editRow(${row.id})">Edit</button>
                </td>
            `);
            tbody.append(tr);
        });
        table.append(tbody);
    }

    showFilesModal(files, divisionCode, divisionName) {
        // Remove existing modal if any
        $('#division-files-modal').remove();

        const modal = $(`
            <div id="division-files-modal" class="modal">
                <div class="modal-content">
                    <span class="close">&times;</span>
                    <h3>${divisionName} - Files</h3>
                    <div class="files-list">
                        ${files.length ? files.map(file => `
                            <div class="file-item">
                                <span>${file.filename}</span>
                                <button onclick="divisionHandler.openFile(${file.id}, ${divisionCode})">
                                    Open
                                </button>
                            </div>
                        `).join('') : '<p>No files found for this period</p>'}
                    </div>
                </div>
            </div>
        `);

        $('body').append(modal);
        modal.show();

        modal.find('.close').on('click', () => modal.remove());
    }

    openFile(fileId, divisionCode) {
        window.location.href = `/data-management?file=${fileId}&division=${divisionCode}`;
    }
}

// Initialize division handler
$(document).ready(() => {
    window.divisionHandler = new DivisionHandler();
});
