<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - Smart Schedule System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">Admin Dashboard</a>
            <button id="logoutBtn" class="btn btn-light">Logout</button>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12 mb-4">
                <h2>Student Management</h2>
                <div class="input-group mb-3">
                    <input type="text" id="searchInput" class="form-control" placeholder="Search students...">
                    <button class="btn btn-outline-secondary" type="button" id="searchBtn">Search</button>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="studentsTable">
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-md-12">
                <h2>Complaints</h2>
                <div id="complaintsList" class="list-group">
                </div>
            </div>
        </div>
    </div>

    <script>
        const token = localStorage.getItem('token');
        if (!token) {
            window.location.href = '/admin/login';
        }

        // Load Students
        async function searchStudents(query = '') {
            try {
                const response = await fetch(`/api/admin/students/search?q=${query}`, {
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json'
                    }
                });
                const data = await response.json();
                displayStudents(data);
            } catch (error) {
                console.error('Error:', error);
            }
        }

        function displayStudents(students) {
            const tbody = document.getElementById('studentsTable');
            tbody.innerHTML = '';
            students.forEach(student => {
                tbody.innerHTML += `
                    <tr>
                        <td>${student.id}</td>
                        <td>${student.name}</td>
                        <td>${student.email}</td>
                        <td>
                            <button class="btn btn-sm btn-success" onclick="approveUser(${student.id})">Approve</button>
                            <button class="btn btn-sm btn-danger" onclick="deleteUser(${student.id})">Delete</button>
                        </td>
                    </tr>
                `;
            });
        }

        // Load Complaints
        async function loadComplaints() {
            try {
                const response = await fetch('/api/admin/complaints', {
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json'
                    }
                });
                const data = await response.json();
                displayComplaints(data);
            } catch (error) {
                console.error('Error:', error);
            }
        }

        function displayComplaints(complaints) {
            const list = document.getElementById('complaintsList');
            list.innerHTML = '';
            complaints.forEach(complaint => {
                list.innerHTML += `
                    <div class="list-group-item">
                        <h5>${complaint.title}</h5>
                        <p>${complaint.message}</p>
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="Reply..." id="reply-${complaint.id}">
                            <button class="btn btn-outline-primary" onclick="replyToComplaint(${complaint.id})">Send Reply</button>
                        </div>
                    </div>
                `;
            });
        }

        // Event Listeners
        document.getElementById('searchBtn').addEventListener('click', () => {
            const query = document.getElementById('searchInput').value;
            searchStudents(query);
        });

        document.getElementById('logoutBtn').addEventListener('click', async () => {
            try {
                await fetch('/api/admin/logout', {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json'
                    }
                });
                localStorage.removeItem('token');
                window.location.href = '/admin/login';
            } catch (error) {
                console.error('Error:', error);
            }
        });

        // Initialize
        searchStudents();
        loadComplaints();
    </script>
</body>
</html>