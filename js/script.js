// Update API URL to match your server configuration
const API_URL = 'http://localhost:5000/api';

// Add server connectivity check
function checkServerConnectivity() {
    $.ajax({
        url: `${API_URL}/health-check`,
        type: 'GET',
        timeout: 5000,
        success: function(response) {
            console.log('Server is running');
        },
        error: function() {
            alert('Server is not running. Please ensure the server is started.');
        }
    });
}

$(document).ready(function() {
    // Check server connectivity on page load
    checkServerConnectivity();

    // Navigation
    $('.nav-menu a').on('click', function(e) {
        e.preventDefault();
        const page = $(this).data('page');
        
        $('.nav-menu a').removeClass('active');
        $(this).addClass('active');
        
        $('.page').removeClass('active');
        $('#' + page).addClass('active');
    });
    
    // Tab navigation
    $('.tab').on('click', function() {
        const tab = $(this).data('tab');
        
        $('.tab').removeClass('active');
        $(this).addClass('active');
        
        $('.tab-content').removeClass('active');
        $('#' + tab + '-content').addClass('active');
    });
    
    // Load initial data
    loadDashboardCounts();
    loadRawData();
    loadCleanData();
    loadApplicants();
    loadPlantillaOptions();
    
    // Update upload form submission with better error handling
    $('#upload-form').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const monthYear = $('#month-picker').val();
        formData.append('month_year', monthYear);
        
        // Show loading state
        const submitButton = $(this).find('button[type="submit"]');
        const originalText = submitButton.text();
        submitButton.prop('disabled', true).text('Uploading...');
        
        $.ajax({
            url: `${API_URL}/upload-with-month`,  // Updated endpoint
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    alert('File uploaded successfully!');
                    loadUploadedFiles(monthYear);
                    loadRawData();
                    loadCleanData();
                    loadDashboardCounts();
                    $('#upload-form')[0].reset();
                } else {
                    alert('Upload failed: ' + (response.message || response.error || 'Unknown error occurred'));
                    console.error('Upload error details:', response);
                }
            },
            error: function(xhr, status, error) {
                let errorMessage = '';
                try {
                    const response = JSON.parse(xhr.responseText);
                    errorMessage = response.message || response.error || error;
                } catch(e) {
                    if (xhr.status === 0) {
                        errorMessage = 'Cannot connect to server. Please check if the server is running.';
                    } else if (xhr.status === 405) {
                        errorMessage = 'Upload method not allowed. Please check API endpoint configuration.';
                    } else {
                        errorMessage = 'Server error occurred: ' + error;
                    }
                }
                alert(errorMessage);
                console.error('Upload error:', {
                    status: xhr.status,
                    error: error,
                    response: xhr.responseText,
                    details: xhr.getAllResponseHeaders()
                });
            },
            complete: function() {
                submitButton.prop('disabled', false).text(originalText);
            }
        });
    });
    
    // Status filter
    $('#status-filter').on('change', function() {
        loadCleanData($(this).val());
    });
    
    // Export button
    $('#export-btn').on('click', function() {
        window.location.href = `${API_URL}/api/export-clean-data`;
    });
    
    // Applicant search
    $('#applicant-search').on('keyup', function() {
        const searchTerm = $(this).val();
        searchApplicants(searchTerm);
    });
    
    // Add applicant button
    $('#add-applicant-btn').on('click', function() {
        $('#applicant-modal').css('display', 'block');
    });
    
    // Close modals
    $('.close').on('click', function() {
        $('.modal').css('display', 'none');
    });
    
    // Edit form submission
    $('#edit-form').on('submit', function(e) {
        e.preventDefault();
        
        const id = $('#edit-id').val();
        const data = {
            remarks: $('#edit-remarks').val(),
            date_published: $('#edit-date-published').val() || null,
            status: $('#edit-status').val()
        };
        
        $.ajax({
            url: `${API_URL}/clean-data/${id}`,
            type: 'PUT',
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: function(response) {
                if (response.success) {
                    alert('Record updated successfully!');
                    $('#edit-modal').css('display', 'none');
                    loadCleanData();
                    loadDashboardCounts();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                alert('Server error occurred: ' + error);
                console.error('Error details:', xhr.responseText);
            }
        });
    });
    
    // Applicant form submission
    $('#applicant-form').on('submit', function(e) {
        e.preventDefault();
        
        const data = {
            fullname: $('#fullname').val(),
            sex: $('#sex').val(),
            position_title: $('#position-title').val(),
            techcode: $('#techcode').val(),
            date_of_birth: $('#date-of-birth').val() || null,
            date_last_promotion: $('#date-last-promotion').val() || null,
            date_last_increment: $('#date-last-increment').val() || null,
            date_of_longevity: $('#date-of-longevity').val() || null,
            appointment_status: $('#appointment-status').val(),
            plantilla_no: $('#plantilla-no').val() || null
        };
        
        const id = $(this).data('edit-id');
        const isEdit = !!id;
        
        $.ajax({
            url: `${API_URL}/applicants${isEdit ? `/${id}` : ''}`,
            type: isEdit ? 'PUT' : 'POST',
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: function(response) {
                if (response.success) {
                    alert(isEdit ? 'Applicant updated successfully!' : 'Applicant added successfully!');
                    $('#applicant-modal').css('display', 'none');
                    $('#applicant-form')[0].reset();
                    $('#applicant-form').removeData('edit-id');
                    $('#applicant-form button[type="submit"]').text('Save Applicant');
                    loadApplicants();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                alert('Server error occurred: ' + error);
                console.error('Error details:', xhr.responseText);
            }
        });
    });
    
    // Close modal when clicking outside
    $(window).on('click', function(e) {
        if ($(e.target).hasClass('modal')) {
            $('.modal').css('display', 'none');
        }
    });
});

// Update all other API calls to use the new API_URL
// Load dashboard counts
function loadDashboardCounts() {
    $.ajax({
        url: `${API_URL}/clean-data`,
        type: 'GET',
        success: function(response) {
            if (response.success) {
                const onProcessCount = response.data.filter(item => item.status === 'On-process').length;
                const onHoldCount = response.data.filter(item => item.status === 'On-hold').length;
                const notYetCount = response.data.filter(item => item.status === 'Not yet for filling').length;
                
                $('#on-process .count').text(onProcessCount);
                $('#on-hold .count').text(onHoldCount);
                $('#not-yet-filling .count').text(notYetCount);
            }
        }
    });
}

// Load raw data
function loadRawData() {
    $.ajax({
        url: `${API_URL}/raw-data`,
        type: 'GET',
        success: function(response) {
            if (response.success) {
                const tableBody = $('#raw-data-table tbody');
                tableBody.empty();
                
                response.data.forEach(function(item) {
                    tableBody.append(`
                        <tr>
                            <td>${item.plantilla_no || ''}</td>
                            <td>${item.plantilla_division || ''}</td>
                            <td>${item.plantilla_sectiondefinition || ''}</td>
                            <td>${item.equivalent_division || ''}</td>
                            <td>${item.position_title || ''}</td>
                            <td>${item.item_number || ''}</td>
                            <td>${item.sg || ''}</td>
                            <td>${item.date_vacated || ''}</td>
                            <td>${item.vacated_due_to || ''}</td>
                            <td>${item.vacated_by || ''}</td>
                        </tr>
                    `);
                });
            }
        }
    });
}

// Load clean data
function loadCleanData(status = '') {
    let url = `${API_URL}/clean-data`;
    if (status) {
        url += '?status=' + status;
    }
    
    $.ajax({
        url: url,
        type: 'GET',
        success: function(response) {
            if (response.success) {
                const tableBody = $('#clean-data-table tbody');
                tableBody.empty();
                
                response.data.forEach(function(item) {
                    tableBody.append(`
                        <tr>
                            <td>${item.plantilla_no || ''}</td>
                            <td>${item.plantilla_division || ''}</td>
                            <td>${item.position_title || ''}</td>
                            <td>${item.sg || ''}</td>
                            <td>${item.remarks || ''}</td>
                            <td>${item.date_published || ''}</td>
                            <td>${item.status || ''}</td>
                            <td>
                                <button class="action-btn edit-btn" onclick="editRecord(${item.id})">Edit</button>
                            </td>
                        </tr>
                    `);
                });
            }
        }
    });
}

// Load applicants
function loadApplicants() {
    $.ajax({
        url: `${API_URL}/applicants`,
        type: 'GET',
        success: function(response) {
            if (response.success) {
                const tableBody = $('#applicants-table tbody');
                tableBody.empty();
                
                response.data.forEach(function(item) {
                    tableBody.append(`
                        <tr>
                            <td>${item.fullname || ''}</td>
                            <td>${item.sex || ''}</td>
                            <td>${item.position_title || ''}</td>
                            <td>${item.techcode || ''}</td>
                            <td>${item.date_of_birth || ''}</td>
                            <td>${item.date_last_promotion || ''}</td>
                            <td>${item.date_last_increment || ''}</td>
                            <td>${item.date_of_longevity || ''}</td>
                            <td>${item.appointment_status || ''}</td>
                            <td>
                                <button class="action-btn edit-btn" onclick="editApplicant(${item.id})">Edit</button>
                            </td>
                        </tr>
                    `);
                });
            }
        }
    });
}

// Search applicants
function searchApplicants(term) {
    $.ajax({
        url: `${API_URL}/applicants/search?term=` + term,
        type: 'GET',
        success: function(response) {
            if (response.success) {
                const tableBody = $('#applicants-table tbody');
                tableBody.empty();
                
                response.data.forEach(function(item) {
                    tableBody.append(`
                        <tr>
                            <td>${item.fullname || ''}</td>
                            <td>${item.sex || ''}</td>
                            <td>${item.position_title || ''}</td>
                            <td>${item.techcode || ''}</td>
                            <td>${item.date_of_birth || ''}</td>
                            <td>${item.date_last_promotion || ''}</td>
                            <td>${item.date_last_increment || ''}</td>
                            <td>${item.date_of_longevity || ''}</td>
                            <td>${item.appointment_status || ''}</td>
                            <td>
                                <button class="action-btn edit-btn" onclick="editApplicant(${item.id})">Edit</button>
                            </td>
                        </tr>
                    `);
                });
            }
        }
    });
}

// Load plantilla options for applicant form
function loadPlantillaOptions() {
    $.ajax({
        url: `${API_URL}/clean-data`,
        type: 'GET',
        success: function(response) {
            if (response.success) {
                const selectElement = $('#plantilla-no');
                selectElement.empty();
                
                response.data.forEach(function(item) {
                    selectElement.append(`
                        <option value="${item.plantilla_no}">${item.plantilla_no} - ${item.position_title}</option>
                    `);
                });
            }
        }
    });
}

// Edit record
function editRecord(id) {
    $.ajax({
        url: `${API_URL}/clean-data`,
        type: 'GET',
        success: function(response) {
            if (response.success) {
                const record = response.data.find(item => item.id === id);
                
                if (record) {
                    $('#edit-id').val(record.id);
                    $('#edit-remarks').val(record.remarks);
                    $('#edit-date-published').val(record.date_published);
                    $('#edit-status').val(record.status);
                    
                    $('#edit-modal').css('display', 'block');
                }
            }
        }
    });
}

// Edit applicant
function editApplicant(id) {
    $.ajax({
        url: `${API_URL}/applicants/${id}`,
        type: 'GET',
        success: function(response) {
            if (response.success) {
                // Populate form with applicant data
                const applicant = response.data;
                $('#applicant-form').data('edit-id', id); // Store ID for update
                $('#fullname').val(applicant.fullname);
                $('#sex').val(applicant.sex);
                $('#position-title').val(applicant.position_title);
                $('#techcode').val(applicant.techcode);
                $('#date-of-birth').val(applicant.date_of_birth);
                $('#date-last-promotion').val(applicant.date_last_promotion);
                $('#date-last-increment').val(applicant.date_last_increment);
                $('#date-of-longevity').val(applicant.date_of_longevity);
                $('#appointment-status').val(applicant.appointment_status);
                $('#plantilla-no').val(applicant.plantilla_no);
                
                // Change form submit button text
                $('#applicant-form button[type="submit"]').text('Update Applicant');
                
                // Show modal
                $('#applicant-modal').css('display', 'block');
            } else {
                alert('Error: ' + response.message);
            }
        },
        error: function() {
            alert('Server error occurred.');
        }
    });
}

// Load files for the selected month
$('#load-files-btn').on('click', function() {
    const monthYear = $('#month-picker').val();
    loadUploadedFiles(monthYear);
});

// Function to load uploaded files
function loadUploadedFiles(monthYear) {
    $.ajax({
        url: `${API_URL}/uploaded-files?month_year=${monthYear}`,
        type: 'GET',
        success: function(response) {
            if (response.success) {
                const tableBody = $('#uploaded-files-table tbody');
                tableBody.empty();
                response.files.forEach(function(file) {
                    tableBody.append(`
                        <tr>
                            <td>${file.original_filename}</td>
                            <td>${file.upload_date}</td>
                            <td>
                                <button class="action-btn edit-btn" onclick="editFile(${file.id})">Edit</button>
                                <button class="action-btn delete-btn" onclick="deleteFile(${file.id})">Delete</button>
                            </td>
                        </tr>
                    `);
                });
            } else {
                alert('Error: ' + response.message);
            }
        },
        error: function() {
            alert('Server error occurred.');
        }
    });
}

// Function to delete file
function deleteFile(fileId) {
    if (confirm('Are you sure you want to delete this file?')) {
        $.ajax({
            url: `${API_URL}/files/${fileId}`,
            type: 'DELETE',
            success: function(response) {
                if (response.success) {
                    alert('File deleted successfully!');
                    loadUploadedFiles($('#month-picker').val());
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('Server error occurred.');
            }
        });
    }
}

// Function to edit file
function editFile(fileId) {
    $.ajax({
        url: `${API_URL}/files/${fileId}/content`,
        type: 'GET',
        success: function(response) {
            if (response.success) {
                try {
                    showFileEditor(fileId, response.data, response.file_info);
                } catch (error) {
                    console.error('Error showing file editor:', error);
                    alert('Error displaying file content. Please check console for details.');
                }
            } else {
                alert('Error: ' + response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('Server error:', xhr.responseText);
            alert('Server error occurred. Please check console for details.');
        }
    });
}

function showFileEditor(fileId, data, fileInfo) {
    if (!data || !Array.isArray(data) || data.length === 0) {
        alert('No valid data to edit');
        return;
    }

    // Create modal for file editing if it doesn't exist
    if (!$('#file-editor-modal').length) {
        $('body').append(`
            <div id="file-editor-modal" class="modal">
                <div class="modal-content" style="width: 95%; height: 90vh; margin: 2.5vh auto;">
                    <span class="close">&times;</span>
                    <h2>Edit File: ${fileInfo.original_filename}</h2>
                    <div class="file-editor-toolbar">
                        <div class="file-editor-search">
                            <input type="text" id="file-search" placeholder="Search in file...">
                            <button class="action-btn" onclick="searchInFile()">Search</button>
                            <button class="action-btn" onclick="clearSearch()">Clear</button>
                        </div>
                    </div>
                    <div id="file-editor-grid" style="height: calc(90vh - 150px); overflow: auto;">
                        <table id="file-editor-table" class="data-table">
                            <thead></thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    <button id="save-file-changes" class="action-btn">Save Changes</button>
                </div>
            </div>
        `);
    }

    const modal = $('#file-editor-modal');
    const table = $('#file-editor-table');
    
    // Clear existing content
    table.find('thead, tbody').empty();
    
    try {
        const headers = Object.keys(data[0]);
        const headerRow = $('<tr>');
        headers.forEach(header => {
            headerRow.append(`<th>${header}</th>`);
        });
        table.find('thead').append(headerRow);
        
        // Add data rows with cell-specific editors
        data.forEach(row => {
            const tableRow = $('<tr>');
            headers.forEach(header => {
                const cell = $('<td>');
                const value = row[header] !== null ? row[header] : '';
                cell.text(value);

                // Add click handler to show appropriate editor
                cell.on('click', function(e) {
                    const currentValue = $(this).text();
                    
                    // Remove any existing editor
                    $(this).find('select, input').remove();
                    
                    // Create appropriate editor based on column name
                    if (header.toLowerCase().includes('sex')) {
                        const select = $('<select>')
                            .addClass('editor-cell-select')
                            .append('<option value="Male">Male</option>')
                            .append('<option value="Female">Female</option>')
                            .append('<option value="Others">Others</option>')
                            .val(currentValue);
                        
                        select.on('change blur', function() {
                            cell.text($(this).val());
                            $(this).remove();
                        });
                        
                        cell.empty().append(select);
                        select.focus();
                    } 
                    else if (header.toLowerCase().includes('date')) {
                        const input = $('<input>')
                            .attr({
                                type: 'date',
                                value: currentValue
                            })
                            .addClass('editor-cell-select');
                        
                        input.on('change blur', function() {
                            cell.text($(this).val());
                            $(this).remove();
                        });
                        
                        cell.empty().append(input);
                        input.focus();
                    }
                    else if (header.toLowerCase().includes('status')) {
                        const select = $('<select>')
                            .addClass('editor-cell-select')
                            .append('<option value="Active">Active</option>')
                            .append('<option value="Inactive">Inactive</option>')
                            .append('<option value="Pending">Pending</option>')
                            .val(currentValue);
                        
                        select.on('change blur', function() {
                            cell.text($(this).val());
                            $(this).remove();
                        });
                        
                        cell.empty().append(select);
                        select.focus();
                    }
                    else {
                        // Make cell editable on click if not using a special editor
                        cell.attr('contenteditable', 'true').focus();
                    }
                });
                
                tableRow.append(cell);
            });
            table.find('tbody').append(tableRow);
        });

        // Handle save changes
        $('#save-file-changes').off('click').on('click', function() {
            const updatedData = [];
            table.find('tbody tr').each(function() {
                const row = {};
                $(this).find('td').each(function(i) {
                    const header = headers[i];
                    // Get value from appropriate input type
                    if ($(this).find('select, input').length) {
                        row[header] = $(this).find('select, input').val();
                    } else {
                        row[header] = $(this).text();
                    }
                });
                updatedData.push(row);
            });
            
            // Save changes
            $.ajax({
                url: `${API_URL}/files/${fileId}/content`,
                type: 'PUT',
                contentType: 'application/json',
                data: JSON.stringify({ content: updatedData }),
                success: function(response) {
                    if (response.success) {
                        alert('File updated successfully!');
                        modal.css('display', 'none');
                        loadUploadedFiles($('#month-picker').val());
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Save error:', xhr.responseText);
                    alert('Error saving changes: ' + error);
                }
            });
        });
        
        modal.css('display', 'block');
    } catch (error) {
        console.error('Error building table:', error);
        alert('Error building table. Please check console for details.');
    }
}

// Add search functionality
function searchInFile() {
    const searchTerm = $('#file-search').val().toLowerCase();
    $('#file-editor-table tbody tr').each(function() {
        const row = $(this);
        const text = row.text().toLowerCase();
        if (text.includes(searchTerm)) {
            row.show();
            // Highlight matching cells
            row.find('td').each(function() {
                const cell = $(this);
                const cellText = cell.text().toLowerCase();
                if (cellText.includes(searchTerm)) {
                    cell.addClass('highlight');
                }
            });
        } else {
            row.hide();
        }
    });
}

function clearSearch() {
    $('#file-search').val('');
    $('#file-editor-table tbody tr').show();
    $('#file-editor-table td').removeClass('highlight');
}