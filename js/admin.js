$(document).ready(function() {
    // Dashboard refresh interval
    setInterval(refreshDashboardStats, 30000); // Every 30 seconds
    
    // Quick role change handler
    $('.role-select').on('change', function() {
        const userId = $(this).data('user-id');
        const newRole = $(this).val();
        updateUserRole(userId, newRole);
    });

    // Password reset handler
    $('.reset-password').on('click', function() {
        const userId = $(this).data('user-id');
        resetUserPassword(userId);
    });

    // Filter users
    $('#search-users').on('keyup', function() {
        const searchTerm = $(this).val().toLowerCase();
        $('.users-table tbody tr').each(function() {
            const text = $(this).text().toLowerCase();
            $(this).toggle(text.includes(searchTerm));
        });
    });

    $('#role-filter, #status-filter').on('change', function() {
        const role = $('#role-filter').val();
        const status = $('#status-filter').val();
        
        $('.users-table tbody tr').each(function() {
            const userRole = $(this).find('td:nth-child(3)').text();
            const userStatus = $(this).find('td:nth-child(4)').text();
            
            const roleMatch = !role || userRole === role;
            const statusMatch = !status || userStatus.toLowerCase() === status;
            
            $(this).toggle(roleMatch && statusMatch);
        });
    });
});

function refreshDashboardStats() {
    $.ajax({
        url: '/HRIS/api/admin.php?action=get_stats',
        method: 'GET',
        success: function(response) {
            if (response.success) {
                $('.stats-cards .count').each(function() {
                    const key = $(this).closest('.card').attr('id');
                    $(this).text(response.stats[key] || 0);
                });
            }
        },
        error: function(xhr, status, error) {
            console.error('Error fetching stats:', error);
        }
    });
}

function updateUserRole(userId, role) {
    $.ajax({
        url: '/admin/update-role',
        method: 'POST',
        data: { user_id: userId, role: role },
        success: function(response) {
            if (response.success) {
                showNotification('Role updated successfully', 'success');
                logAuditAction('user_role_change', `Changed user ${userId} role to ${role}`);
            } else {
                showNotification('Error updating role', 'error');
            }
        }
    });
}

function resetUserPassword(userId) {
    if (confirm('Are you sure you want to reset this user\'s password?')) {
        $.ajax({
            url: '/admin/reset-password',
            method: 'POST',
            data: { user_id: userId },
            success: function(response) {
                if (response.success) {
                    showNotification('Password reset successful', 'success');
                    logAuditAction('password_reset', `Reset password for user ${userId}`);
                } else {
                    showNotification('Error resetting password', 'error');
                }
            }
        });
    }
}

function logAuditAction(action, details) {
    $.ajax({
        url: '/admin/log-action',
        method: 'POST',
        data: {
            action: action,
            details: details
        }
    });
}

function showNotification(message, type) {
    const notification = $(`<div class="notification ${type}">${message}</div>`);
    $('.notifications-container').append(notification);
    setTimeout(() => notification.remove(), 3000);
}

function addUser() {
    showUserModal();
}

function editUser(userId) {
    fetch(`/api/users/${userId}`)
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                showUserModal(data.user);
            }
        });
}

function resetPassword(userId) {
    if(confirm('Are you sure you want to reset this user\'s password?')) {
        fetch(`/api/users/${userId}/reset-password`, {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                alert('Password reset email has been sent.');
            } else {
                alert('Error: ' + data.message);
            }
        });
    }
}

function deleteUser(userId) {
    if(confirm('Are you sure you want to delete this user?')) {
        fetch(`/api/users/${userId}`, {
            method: 'DELETE'
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        });
    }
}

function showUserModal(userData = null) {
    // Implementation for user modal
}

function toggleStatus(userId) {
    if(confirm('Are you sure you want to change this user\'s status?')) {
        $.ajax({
            url: 'api.php?action=toggle_status',
            method: 'POST',
            data: { user_id: userId },
            success: function(response) {
                if(response.success) {
                    location.reload();
                } else {
                    alert('Error changing user status: ' + response.message);
                }
            }
        });
    }
}
