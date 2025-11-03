 const tabs = document.querySelectorAll('#requestTabs .nav-link');
        const rows = document.querySelectorAll('#requestsTable tbody tr');
        const searchInput = document.getElementById('searchInput');

        let activeFilter = 'all';

        // 🔹 Handle tab filtering
        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                tabs.forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
                activeFilter = tab.dataset.status.toLowerCase();
                filterRows();
            });
        });

        // 🔹 Handle search
        searchInput.addEventListener('keyup', () => filterRows());

        function filterRows() {
            const searchValue = searchInput.value.toLowerCase();

            rows.forEach(row => {
                const rowStatus = row.dataset.status.toLowerCase();
                const rowText = row.textContent.toLowerCase();

                const matchesStatus = (activeFilter === 'all' || rowStatus === activeFilter);
                const matchesSearch = rowText.includes(searchValue);

                row.style.display = (matchesStatus && matchesSearch) ? '' : 'none';
            });

            // 🟡 Optional: Show message if no results
            const visibleRows = [...rows].some(r => r.style.display === '');
            if (!visibleRows) {
                if (!document.getElementById('noResultsRow')) {
                    const noRow = document.createElement('tr');
                    noRow.id = 'noResultsRow';
                    noRow.innerHTML = `<td colspan="9" class="text-center text-muted py-4">No matching requests found.</td>`;
                    document.querySelector('#requestsTable tbody').appendChild(noRow);
                }
            } else {
                const noRow = document.getElementById('noResultsRow');
                if (noRow) noRow.remove();
            }
        }