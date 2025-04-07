<?php
// Check if user is admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ?page=dashboard');
    exit;
}
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Contact Form Submissions</h1>
    </div>

    <!-- Search and Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="input-group">
                        <input type="text" class="form-control" id="search" placeholder="Search by name, email, or company...">
                        <button class="btn btn-outline-secondary" type="button" id="searchBtn">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-6 text-end">
                    <select class="form-select d-inline-block w-auto" id="limit">
                        <option value="10">10 per page</option>
                        <option value="25">25 per page</option>
                        <option value="50">50 per page</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Contact Form Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th class="sortable" data-sort="id">
                                ID
                                <i class="bi bi-sort-up"></i>
                            </th>
                            <th class="sortable" data-sort="nome_cognome">
                                Name
                                <i class="bi bi-sort-up"></i>
                            </th>
                            <th class="sortable" data-sort="email">
                                Email
                                <i class="bi bi-sort-up"></i>
                            </th>
                            <th class="sortable" data-sort="telefono">
                                Phone
                                <i class="bi bi-sort-up"></i>
                            </th>
                            <th class="sortable" data-sort="ragione_sociale">
                                Company
                                <i class="bi bi-sort-up"></i>
                            </th>
                            <th class="sortable" data-sort="messaggio">
                                Message
                                <i class="bi bi-sort-up"></i>
                            </th>
                            <th class="sortable" data-sort="privacy">
                                Privacy
                                <i class="bi bi-sort-up"></i>
                            </th>
                            <th class="sortable" data-sort="url_invio">
                                URL
                                <i class="bi bi-sort-up"></i>
                            </th>
                            <th class="sortable" data-sort="created_at">
                                Submitted
                                <i class="bi bi-sort-down"></i>
                            </th>
                        </tr>
                    </thead>
                    <tbody id="submissionsList">
                        <!-- Submissions will be loaded here -->
                    </tbody>
                </table>
                <div id="noDataMessage" class="text-center py-5" style="display: none;">
                    <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                    <p class="mt-3 text-muted">No contact form submissions found</p>
                </div>
            </div>

            <!-- Pagination -->
            <div class="mt-4" id="paginationContainer" style="display: none;">
                <nav>
                    <ul class="pagination" id="pagination">
                        <!-- Pagination will be loaded here -->
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<style>
.sortable {
    cursor: pointer;
    position: relative;
    white-space: nowrap;
}
.sortable:hover {
    background-color: rgba(0,0,0,.075);
}
.sortable i {
    margin-left: 5px;
    display: none;
    vertical-align: middle;
}
/* Remove hover display rule */
/* .sortable:hover i {
    display: inline-block;
} */
/* Fixed widths for all columns */
th[data-sort="id"],
td:nth-child(1) {
    width: 5%;
}
th[data-sort="nome_cognome"],
td:nth-child(2) {
    width: 15%;
}
th[data-sort="email"],
td:nth-child(3) {
    width: 15%;
}
th[data-sort="telefono"],
td:nth-child(4) {
    width: 10%;
}
th[data-sort="ragione_sociale"],
td:nth-child(5) {
    width: 15%;
}
th[data-sort="messaggio"],
td:nth-child(6) {
    width: 20%;
}
th[data-sort="privacy"],
td:nth-child(7) {
    width: 5%;
    text-align: center;
}
th[data-sort="url_invio"],
td:nth-child(8) {
    width: 10%;
}
th[data-sort="created_at"],
td:nth-child(9) {
    width: 5%;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentPage = 1;
    let currentSort = 'created_at';
    let currentOrder = 'DESC';
    let currentSearch = '';
    let currentLimit = 10;

    // Load submissions
    async function loadSubmissions() {
        try {
            const response = await fetch(`../api/contact/list.php?page=${currentPage}&limit=${currentLimit}&sort=${currentSort}&order=${currentOrder}&search=${encodeURIComponent(currentSearch)}`);
            const data = await response.json();
            
            if (data.success) {
                displaySubmissions(data.data.submissions);
                updatePagination(data.data);
            } else {
                alert('Error loading submissions: ' + data.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error loading submissions');
        }
    }

    // Display submissions in table
    function displaySubmissions(submissions) {
        const tbody = document.getElementById('submissionsList');
        const noDataMessage = document.getElementById('noDataMessage');
        tbody.innerHTML = '';

        // Show/hide no data message
        if (submissions.length === 0) {
            tbody.style.display = 'none';
            noDataMessage.style.display = 'block';
        } else {
            tbody.style.display = 'table-row-group';
            noDataMessage.style.display = 'none';
        }

        // Update sort icons
        document.querySelectorAll('.sortable').forEach(th => {
            const sort = th.dataset.sort;
            const icon = th.querySelector('i');
            if (icon) {
                if (sort === currentSort) {
                    icon.className = `bi bi-sort-${currentOrder === 'ASC' ? 'up' : 'down'}`;
                    icon.style.display = 'inline-block';
                } else {
                    icon.style.display = 'none';
                }
            }
        });

        submissions.forEach(sub => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${sub.id}</td>
                <td>${escapeHtml(sub.nome_cognome)}</td>
                <td>${escapeHtml(sub.email)}</td>
                <td>${escapeHtml(sub.telefono)}</td>
                <td>${escapeHtml(sub.ragione_sociale)}</td>
                <td>${escapeHtml(sub.messaggio)}</td>
                <td>
                    <span class="badge bg-${sub.privacy ? 'success' : 'danger'}">
                        ${sub.privacy ? 'Accepted' : 'Not Accepted'}
                    </span>
                </td>
                <td>${escapeHtml(sub.url_invio)}</td>
                <td>${formatDate(sub.created_at)}</td>
            `;
            tbody.appendChild(tr);
        });
    }

    // Update pagination
    function updatePagination(data) {
        const paginationContainer = document.getElementById('paginationContainer');
        const pagination = document.getElementById('pagination');
        pagination.innerHTML = '';

        // Only show pagination if there are multiple pages
        if (data.total_pages <= 1) {
            paginationContainer.style.display = 'none';
            return;
        }

        paginationContainer.style.display = 'flex';
        paginationContainer.style.justifyContent = 'center';

        // Previous button
        const prevLi = document.createElement('li');
        prevLi.className = `page-item ${data.page === 1 ? 'disabled' : ''}`;
        prevLi.innerHTML = `
            <a class="page-link" href="#" data-page="${data.page - 1}">Previous</a>
        `;
        pagination.appendChild(prevLi);

        // Page numbers
        for (let i = 1; i <= data.total_pages; i++) {
            const li = document.createElement('li');
            li.className = `page-item ${i === data.page ? 'active' : ''}`;
            li.innerHTML = `
                <a class="page-link" href="#" data-page="${i}">${i}</a>
            `;
            pagination.appendChild(li);
        }

        // Next button
        const nextLi = document.createElement('li');
        nextLi.className = `page-item ${data.page === data.total_pages ? 'disabled' : ''}`;
        nextLi.innerHTML = `
            <a class="page-link" href="#" data-page="${data.page + 1}">Next</a>
        `;
        pagination.appendChild(nextLi);
    }

    // Event listeners
    document.getElementById('searchBtn').addEventListener('click', function() {
        currentSearch = document.getElementById('search').value;
        currentPage = 1;
        loadSubmissions();
    });

    document.getElementById('search').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            currentSearch = this.value;
            currentPage = 1;
            loadSubmissions();
        }
    });

    document.getElementById('limit').addEventListener('change', function() {
        currentLimit = parseInt(this.value);
        currentPage = 1;
        loadSubmissions();
    });

    document.getElementById('pagination').addEventListener('click', function(e) {
        e.preventDefault();
        if (e.target.classList.contains('page-link')) {
            const page = parseInt(e.target.dataset.page);
            if (page && page !== currentPage) {
                currentPage = page;
                loadSubmissions();
            }
        }
    });

    document.querySelectorAll('.sortable').forEach(th => {
        th.addEventListener('click', function(e) {
            e.preventDefault();
            const sort = this.dataset.sort;
            
            if (currentSort === sort) {
                currentOrder = currentOrder === 'ASC' ? 'DESC' : 'ASC';
            } else {
                currentSort = sort;
                currentOrder = 'DESC';
            }
            
            loadSubmissions();
        });
    });

    // Helper functions
    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    function formatDate(dateString) {
        const date = new Date(dateString);
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        return `${months[date.getMonth()]} ${date.getDate()}, ${date.getFullYear()}`;
    }

    // Initial load
    loadSubmissions();
});
</script> 