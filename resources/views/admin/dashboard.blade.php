<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - Smart Schedule System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .card {
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .navbar {
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">Admin Dashboard</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <button id="logoutBtn" class="btn btn-light">Logout</button>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div id="authError" class="alert alert-danger d-none">
            Authentication error. Please <a href="/admin/login">login again</a>.
        </div>
        
        <div id="dashboardContent" class="row">
            <div class="col-md-12 mb-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Student Management</h5>
                        <div class="d-flex">
                            <input type="text" id="searchInput" class="form-control form-control-sm me-2" placeholder="Search students...">
                            <button id="searchBtn" class="btn btn-sm btn-primary">Search</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="studentsTable">
                                    <!-- Student data will be loaded here dynamically -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Student Complaints</h5>
                    </div>
                    <div class="card-body">
                        <div id="complaintsList" class="list-group">
                            <!-- Complaints will be loaded here dynamically -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Get token from multiple sources for reliability
        let token = localStorage.getItem('token');
        
        // Fallback to cookie if localStorage is not available
        if (!token) {
            const tokenMatch = document.cookie.match(/(?:^|;\s*)admin_token=([^;]*)/);
            if (tokenMatch) token = tokenMatch[1];
        }
        
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const dashboardContent = document.getElementById('dashboardContent');
        const authError = document.getElementById('authError');
        
        // Verify authentication
        async function verifyAuth() {
            if (!token) {
                showAuthError();
                return false;
            }
            
            try {
                // Test the token with our auth-check endpoint
                const response = await fetch('/api/admin/auth-check', {
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json'
                    },
                    credentials: 'include'
                });
                
                const data = await response.json();
                
                if (!response.ok || !data.authenticated) {
                    showAuthError();
                    return false;
                }
                
                return true;
            } catch (error) {
                console.error('Auth verification error:', error);
                showAuthError();
                return false;
            }
        }
        
        function showAuthError() {
            // Clear tokens and show error message
            localStorage.removeItem('token');
            document.cookie = 'admin_token=; path=/; expires=Thu, 01 Jan 1970 00:00:00 GMT';
            authError.classList.remove('d-none');
            dashboardContent.classList.add('d-none');
            
            // Redirect to login after a short delay
            setTimeout(() => {
                window.location.href = '/admin/login';
            }, 2000);
        }

        // Load Students
        async function searchStudents(query = '') {
            if (!await verifyAuth()) return;
            
            try {
                const url = query 
                    ? `/api/admin/students/search?${new URLSearchParams({name: query}).toString()}`
                    : '/api/admin/students/search';
                    
                const response = await fetch(url, {
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    credentials: 'include'
                });
                
                if (!response.ok) {
                    if (response.status === 401 || response.status === 403) {
                        // Authentication failed
                        await verifyAuth();
                        return;
                    }
                    throw new Error('Failed to fetch students');
                }
                
                const data = await response.json();
                displayStudents(data);
            } catch (error) {
                console.error('Error fetching students:', error);
            }
        }

        function displayStudents(students) {
            const tbody = document.getElementById('studentsTable');
            tbody.innerHTML = '';
            
            if (students.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center">No students found</td></tr>';
                return;
            }
            
            students.forEach(student => {
                const statusBadgeClass = student.status === 'approved' ? 'bg-success' : 'bg-warning';
                
                tbody.innerHTML += `
                    <tr>
                        <td>${student.id}</td>
                        <td>${student.name}</td>
                        <td>${student.email}</td>
                        <td><span class="badge ${statusBadgeClass}">${student.status || 'pending'}</span></td>
                        <td>
                            ${student.status !== 'approved' ? `<button class="btn btn-sm btn-success me-2" onclick="approveUser(${student.id})">Approve</button>` : ''}
                            <button class="btn btn-sm btn-danger" onclick="deleteUser(${student.id})">Delete</button>
                        </td>
                    </tr>
                `;
            });
        }

        // User Management Functions
        async function approveUser(studentId) {
            if (!await verifyAuth()) return;
            
            try {
                const response = await fetch(`/api/admin/students/${studentId}/approve`, {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    credentials: 'include'
                });
                
                if (response.ok) {
                    alert('User approved successfully');
                    // Refresh the student list
                    await searchStudents();
                } else {
                    const data = await response.json();
                    alert(`Error: ${data.message || 'Something went wrong'}`);
                    
                    if (response.status === 401 || response.status === 403) {
                        await verifyAuth();
                    }
                }
            } catch (error) {
                console.error('Error approving user:', error);
                alert('Failed to approve user');
            }
        }

        async function deleteUser(studentId) {
            if (!await verifyAuth()) return;
            
            if (confirm('Are you sure you want to delete this user?')) {
                try {
                    const response = await fetch(`/api/admin/students/${studentId}`, {
                        method: 'DELETE',
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        credentials: 'include'
                    });
                    
                    if (response.ok) {
                        alert('User deleted successfully');
                        // Refresh the student list
                        await searchStudents();
                    } else {
                        const data = await response.json();
                        alert(`Error: ${data.message || 'Something went wrong'}`);
                        
                        if (response.status === 401 || response.status === 403) {
                            await verifyAuth();
                        }
                    }
                } catch (error) {
                    console.error('Error deleting user:', error);
                    alert('Failed to delete user');
                }
            }
        }

        // Load Complaints
        async function loadComplaints() {
            if (!await verifyAuth()) return;
            
            try {
                const response = await fetch('/api/admin/complaints', {
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    credentials: 'include'
                });
                
                if (!response.ok) {
                    if (response.status === 401 || response.status === 403) {
                        await verifyAuth();
                        return;
                    }
                    throw new Error('Failed to fetch complaints');
                }
                
                const data = await response.json();
                displayComplaints(data);
            } catch (error) {
                console.error('Error fetching complaints:', error);
            }
        }

        function displayComplaints(complaints) {
            const list = document.getElementById('complaintsList');
            
            if (!complaints || complaints.length === 0) {
                list.innerHTML = '<div class="list-group-item">No complaints found</div>';
                return;
            }
            
            list.innerHTML = '';
            complaints.forEach(complaint => {
                const date = new Date(complaint.created_at).toLocaleDateString();
                const statusBadge = complaint.status === 'replied' 
                    ? '<span class="badge bg-success ms-1">Replied</span>' 
                    : '<span class="badge bg-warning ms-1">Pending</span>';
                
                list.innerHTML += `
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-1">${complaint.subject} ${statusBadge}</h5>
                            <small>${date}</small>
                        </div>
                        <p class="mb-1">${complaint.message}</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <small>From: ${complaint.student?.name || 'Unknown student'}</small>
                            ${complaint.status !== 'replied' ? `<button class="btn btn-sm btn-primary" onclick="replyToComplaint(${complaint.id})">Reply</button>` : ''}
                        </div>
                    </div>
                `;
            });
        }

        async function replyToComplaint(complaintId) {
            if (!await verifyAuth()) return;
            
            const reply = prompt('Enter your reply:');
            if (reply) {
                try {
                    const response = await fetch(`/api/admin/complaints/${complaintId}/reply`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Authorization': `Bearer ${token}`,
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        credentials: 'include',
                        body: JSON.stringify({ reply })
                    });
                    
                    if (response.ok) {
                        alert('Reply sent successfully');
                        await loadComplaints();
                    } else {
                        const data = await response.json();
                        alert(`Error: ${data.message || 'Failed to send reply'}`);
                        
                        if (response.status === 401 || response.status === 403) {
                            await verifyAuth();
                        }
                    }
                } catch (error) {
                    console.error('Error sending reply:', error);
                    alert('Failed to send reply');
                }
            }
        }

        // Helper function to refresh CSRF token
        async function refreshCsrfToken() {
            try {
                const response = await fetch('/refresh-csrf', {
                    credentials: 'include'
                });
                const data = await response.json();
                document.querySelector('meta[name="csrf-token"]').setAttribute('content', data.token);
                return data.token;
            } catch (error) {
                console.error('Error refreshing CSRF token:', error);
                return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            }
        }

        // Event Listeners
        document.getElementById('searchBtn').addEventListener('click', () => {
            const query = document.getElementById('searchInput').value.trim();
            searchStudents(query);
        });

        // Search on Enter key in search input
        document.getElementById('searchInput').addEventListener('keyup', (e) => {
            if (e.key === 'Enter') {
                const query = document.getElementById('searchInput').value.trim();
                searchStudents(query);
            }
        });

        document.getElementById('logoutBtn').addEventListener('click', async () => {
            try {
                await refreshCsrfToken();
                
                await fetch('/api/admin/logout', {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    credentials: 'include'
                });
                
                // Clear all auth tokens
                localStorage.removeItem('token');
                document.cookie = 'admin_token=; path=/; expires=Thu, 01 Jan 1970 00:00:00 GMT';
                
                // Redirect to login
                window.location.href = '/admin/login';
            } catch (error) {
                console.error('Error during logout:', error);
                // Even if logout API fails, clear tokens and redirect
                localStorage.removeItem('token');
                window.location.href = '/admin/login';
            }
        });

        // Initialize
        verifyAuth().then(isAuthenticated => {
            if (isAuthenticated) {
                searchStudents();
                loadComplaints();
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>