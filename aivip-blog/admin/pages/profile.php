<?php
require_once '../config/database.php';

// Get user data
$db = new Database();
$conn = $db->getConnection();

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$db->closeConnection();
?>

<div class="container-fluid">
    <h1 class="h3 mb-4">Profile Settings</h1>

    <div class="row">
        <div class="col-md-6">
            <!-- Profile Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Profile Information</h5>
                </div>
                <div class="card-body">
                    <form id="profile-form">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" 
                                   value="<?php echo htmlspecialchars($user['username']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i>Save Changes
                        </button>
                    </form>
                </div>
            </div>

            <!-- Change Password -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Change Password</h5>
                </div>
                <div class="card-body">
                    <form id="password-form">
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password</label>
                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                        </div>
                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" required>
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-key me-2"></i>Change Password
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <!-- Account Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Account Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <div>
                            <span class="badge bg-<?php echo $user['role'] === 'admin' ? 'danger' : 'primary'; ?>">
                                <?php echo ucfirst($user['role']); ?>
                            </span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Member Since</label>
                        <div>
                            <?php echo date('F j, Y', strtotime($user['created_at'])); ?>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Last Updated</label>
                        <div>
                            <?php echo date('F j, Y', strtotime($user['updated_at'])); ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Activity Summary -->
            <?php
            // Get user activity statistics
            $db = new Database();
            $conn = $db->getConnection();

            $statsStmt = $conn->prepare("SELECT 
                (SELECT COUNT(*) FROM posts WHERE author_id = ?) as total_posts,
                (SELECT COUNT(*) FROM posts WHERE author_id = ? AND status = 'published') as published_posts,
                (SELECT COUNT(*) FROM media WHERE uploaded_by = ?) as total_media");
            $statsStmt->bind_param("iii", $_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id']);
            $statsStmt->execute();
            $stats = $statsStmt->get_result()->fetch_assoc();

            $db->closeConnection();
            ?>
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Activity Summary</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3">
                        <div>Total Posts</div>
                        <div class="fw-bold"><?php echo $stats['total_posts']; ?></div>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <div>Published Posts</div>
                        <div class="fw-bold"><?php echo $stats['published_posts']; ?></div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <div>Media Uploads</div>
                        <div class="fw-bold"><?php echo $stats['total_media']; ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Handle profile form submission
const profileForm = document.getElementById('profile-form');
profileForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    try {
        const response = await fetch('../api/users/update-profile.php', {
            method: 'POST',
            body: JSON.stringify(Object.fromEntries(new FormData(profileForm))),
            headers: {
                'Content-Type': 'application/json'
            }
        });

        const data = await response.json();
        
        if (data.success) {
            alert('Profile updated successfully!');
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        alert('Error updating profile: ' + error.message);
    }
});

// Handle password form submission
const passwordForm = document.getElementById('password-form');
passwordForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    if (newPassword !== confirmPassword) {
        alert('New passwords do not match!');
        return;
    }
    
    try {
        const formData = {
            current_password: document.getElementById('current_password').value,
            new_password: newPassword
        };

        const response = await fetch('../api/users/change-password.php', {
            method: 'POST',
            body: JSON.stringify(formData),
            headers: {
                'Content-Type': 'application/json'
            }
        });

        const data = await response.json();
        
        if (data.success) {
            alert('Password changed successfully!');
            passwordForm.reset();
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        alert('Error changing password: ' + error.message);
    }
});
</script> 