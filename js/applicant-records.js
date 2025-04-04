document.addEventListener('DOMContentLoaded', () => {
    const addBtn = document.getElementById('add-applicant');
    const modal = document.getElementById('applicant-modal');
    const closeBtn = modal.querySelector('.close');
    const form = document.getElementById('applicant-form');

    // Initialize division dropdown
    loadDivisions();

    // Add button click handler
    addBtn.addEventListener('click', () => {
        modal.classList.add('show');
        form.reset();
    });

    // Close button handler
    closeBtn.addEventListener('click', () => {
        modal.classList.remove('show');
    });

    // Click outside modal to close
    window.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.classList.remove('show');
        }
    });

    // Form submission handler
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const formData = {
            position_title: document.getElementById('position-title').value,
            division: document.getElementById('division').value,
            status: document.getElementById('status').value
        };

        try {
            const response = await fetch('../api/applicant_handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            });

            const data = await response.json();
            
            if (data.success) {
                showNotification('Applicant added successfully!', 'success');
                modal.classList.remove('show');
                form.reset();
                loadApplicantRecords(); // Refresh the table
            } else {
                showNotification('Error: ' + data.message, 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showNotification('Failed to add applicant', 'error');
        }
    });

    // Add filter event handlers
    document.getElementById('apply-filters').addEventListener('click', loadApplicantRecords);

    // Initial load without filters
    loadApplicantRecords();
});

function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    document.body.appendChild(notification);

    setTimeout(() => {
        notification.remove();
    }, 3000);
}

function loadDivisions() {
    fetch('../api/applicant_handler.php?action=get_divisions')
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('division-filter');
            data.divisions.forEach(div => {
                const option = document.createElement('option');
                option.value = div.id;
                option.textContent = div.name;
                select.appendChild(option);
            });
        });
}

function loadApplicantRecords() {
    const month = document.getElementById('month-filter').value;
    const division = document.getElementById('division-filter').value;
    
    const queryParams = new URLSearchParams({
        month: month,
        division: division
    });

    fetch(`../api/applicant_handler.php?${queryParams}`)
        .then(response => response.json())
        .then(data => {
            const tbody = document.querySelector('#applicants-table tbody');
            tbody.innerHTML = '';
            
            data.forEach(applicant => {
                tbody.innerHTML += `
                    <tr>
                        <td>${applicant.id_no || ''}</td>
                        <td>${applicant.fullname || ''}</td>
                        <td>${applicant.last_name || ''}</td>
                        <td>${applicant.first_name || ''}</td>
                        <td>${applicant.middle_name || ''}</td>
                        <td>${applicant.extname || ''}</td>
                        <td>${applicant.mi || ''}</td>
                        <td>${applicant.sex || ''}</td>
                        <td>${applicant.position_title || ''}</td>
                        <td>${applicant.item_number || ''}</td>
                        <td>${applicant.techcode || ''}</td>
                        <td>${applicant.level || ''}</td>
                        <td>${applicant.appointment_status || ''}</td>
                        <td>${applicant.sg || ''}</td>
                        <td>${applicant.step || ''}</td>
                        <td>${applicant.monthly_salary || ''}</td>
                        <td>${formatDate(applicant.date_of_birth) || ''}</td>
                        <td>${formatDate(applicant.date_orig_appt) || ''}</td>
                        <td>${formatDate(applicant.date_govt_srvc) || ''}</td>
                        <td>${formatDate(applicant.date_last_promotion) || ''}</td>
                        <td>${formatDate(applicant.date_last_increment) || ''}</td>
                        <td>${formatDate(applicant.date_of_longevity) || ''}</td>
                    </tr>
                `;
            });
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Failed to load applicants', 'error');
        });
}

function formatDate(dateString) {
    if (!dateString) return '';
    return new Date(dateString).toLocaleDateString('en-PH');
}
