document.addEventListener('DOMContentLoaded', function () {
    const dropdownItems = document.querySelectorAll('.dropdown-item');
    const tableRows = document.querySelectorAll('#requestsTable tbody tr');
    const searchInput = document.getElementById('searchInput');
    const selectedStatusText = document.getElementById('selectedStatus');

    // 🔹 Dropdown click handler
    dropdownItems.forEach(item => {
        item.addEventListener('click', function () {
            // Remove active state from all dropdown items
            dropdownItems.forEach(i => i.classList.remove('active'));
            this.classList.add('active');

            const status = this.getAttribute('data-status').toLowerCase();
            selectedStatusText.textContent = this.textContent;

            filterTable(status, searchInput.value.toLowerCase());
        });
    });

    // 🔹 Search handler
    searchInput.addEventListener('input', function () {
        const searchText = this.value.toLowerCase();
        const activeItem = document.querySelector('.dropdown-item.active');
        const status = activeItem ? activeItem.getAttribute('data-status').toLowerCase() : 'all';
        filterTable(status, searchText);
    });

    // 🔹 Combined filter (status + search)
    function filterTable(status, searchText = '') {
        tableRows.forEach(row => {
            const rowStatus = row.getAttribute('data-status').toLowerCase();
            const text = row.textContent.toLowerCase();
            const matchesStatus = (status === 'all' || rowStatus === status);
            const matchesSearch = text.includes(searchText);
            row.style.display = matchesStatus && matchesSearch ? '' : 'none';
        });
    }
});
