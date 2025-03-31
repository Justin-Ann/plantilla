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
        url: '/admin/get-stats',
        method: 'GET',
        success: function(response) {
            if (response.success) {
                $('.stats-cards .count').each(function() {
                    const key = $(this).closest('.card').attr('id');
                    $(this).text(response.stats[key] || 0);
                });
            }
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

function editUser(userId) {
    $.ajax({
        url: 'api.php?action=get_user',
        method: 'GET',
        data: { user_id: userId },
        success: function(response) {
            if (response.success) {
                $('#edit-user-id').val(response.user.id);
                $('#edit-username').val(response.user.username);
                $('#edit-email').val(response.user.email);
                $('#edit-role').val(response.user.role);
                $('#edit-user-modal').show();
            }
        }
    });
}

function resetPassword(userId) {
    if(confirm('Are you sure you want to reset this user\'s password? An email will be sent to the user.')) {
        $.ajax({
            url: 'api.php?action=reset_password',
            method: 'POST',
            data: { user_id: userId },
            success: function(response) {
                if(response.success) {
                    alert('Password has been reset and email sent to user.');
                } else {
                    alert('Error resetting password: ' + response.message);
                }
            }
        });
    }
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
