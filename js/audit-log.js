$(document).ready(function() {
    // Search functionality
    $('#search-log').on('keyup', function() {
        const searchTerm = $(this).val().toLowerCase();
        $('.audit-log-table tbody tr').each(function() {
            const text = $(this).text().toLowerCase();
            $(this).toggle(text.includes(searchTerm));
        });
    });
    
    // Action filter
    $('#action-filter').on('change', function() {
        const selectedAction = $(this).val().toLowerCase();
        $('.audit-log-table tbody tr').each(function() {
            const action = $(this).find('td:nth-child(3)').text().toLowerCase();
            $(this).toggle(!selectedAction || action.includes(selectedAction));
        });
    });
});
