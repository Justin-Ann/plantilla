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
