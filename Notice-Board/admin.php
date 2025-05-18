<?php
session_start();
require 'db.php';

// Redirect to login if not authenticated
if (!isset($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit;
}

// Initialize variables
$message = '';
$uploads = [];
$texts = [];

// Handle success/error messages
if (isset($_GET['success'])) {
    $message = '<div class="alert success"><i class="fas fa-check-circle"></i> Content uploaded successfully!</div>';
} elseif (isset($_GET['error'])) {
    $message = '<div class="alert error"><i class="fas fa-exclamation-circle"></i> Error: '.htmlspecialchars($_GET['error']).'</div>';
}

// Handle delete requests
if (isset($_GET['delete_upload'])) {
    $upload_id = $_GET['delete_upload'];
    // First get the file path to delete the physical file
    $stmt = $conn->prepare("SELECT file_path FROM uploads WHERE id = ?");
    $stmt->bind_param("i", $upload_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        if (file_exists($row['file_path'])) {
            unlink($row['file_path']);
        }
    }
    
    // Now delete the database record
    $stmt = $conn->prepare("DELETE FROM uploads WHERE id = ?");
    $stmt->bind_param("i", $upload_id);
    if ($stmt->execute()) {
        header("Location: admin.php?success=1");
        exit;
    } else {
        header("Location: admin.php?error=Failed+to+delete+upload");
        exit;
    }
}

if (isset($_GET['delete_text'])) {
    $text_id = $_GET['delete_text'];
    $stmt = $conn->prepare("DELETE FROM texts WHERE id = ?");
    $stmt->bind_param("i", $text_id);
    if ($stmt->execute()) {
        header("Location: admin.php?success=1");
        exit;
    } else {
        header("Location: admin.php?error=Failed+to+delete+text");
        exit;
    }
}

// Fetch data from database
$uploads = $conn->query("SELECT * FROM uploads ORDER BY uploaded_at DESC LIMIT 10");
$texts = $conn->query("SELECT * FROM texts ORDER BY created_at DESC LIMIT 5");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="admin.css">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2><i class="fas fa-university"></i> Green University</h2>
            </div>
            <div class="sidebar-menu">
                <a href="admin.php" class="menu-item active">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                <a href="admin_public.php" class="menu-item">
                    <i class="fas fa-bullhorn"></i>
                    <span>Public Notices</span>
                </a>
                <a href="admin.php" class="menu-item">
                    <i class="fas fa-file-upload"></i>
                    <span>Uploads</span>
                </a>
                <a href="admin.php" class="menu-item">
                    <i class="fas fa-edit"></i>
                    <span>Text Content</span>
                </a>
                <a href="logout.php" class="menu-item">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1>Admin Dashboard</h1>
                <div class="user-info">
                    <div class="user-avatar">AD</div>
                    <a href="logout.php" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>

            <?php echo $message; ?>

            <div class="card">
                <h2><i class="fas fa-upload"></i> Upload Content</h2>
                <form method="POST" action="upload_handler.php" enctype="multipart/form-data">
                    <!-- Notice Section -->
                    <div class="section-container">
                        <h3 class="section-title"><i class="fas fa-file-alt"></i> Notice Upload</h3>
                        <div class="form-group">
                            <label>Notice (PDF/Image)</label>
                            <div class="file-upload">
                                <button type="button" class="file-upload-btn">
                                    <i class="fas fa-cloud-upload-alt"></i> Choose File
                                </button>
                                <input type="file" name="notice_file" class="file-upload-input" accept=".pdf,.jpg,.jpeg,.png">
                                <div class="file-name">No file chosen</div>
                            </div>
                        </div>
                        <button type="submit" name="submit_notice" class="btn section-btn">
                            <i class="fas fa-upload"></i> Upload Notice
                        </button>
                    </div>

                    <!-- Video Section -->
                    <div class="section-container">
                        <h3 class="section-title"><i class="fas fa-video"></i> Video Upload</h3>
                        <div class="form-group">
                            <label>Video File</label>
                            <div class="file-upload">
                                <button type="button" class="file-upload-btn">
                                    <i class="fas fa-cloud-upload-alt"></i> Choose File
                                </button>
                                <input type="file" name="video_file" class="file-upload-input" accept=".mp4,.webm">
                                <div class="file-name">No file chosen</div>
                            </div>
                        </div>
                        <button type="submit" name="submit_video" class="btn section-btn">
                            <i class="fas fa-upload"></i> Upload Video
                        </button>
                    </div>

                    <!-- Faculty Section -->
                    <div class="section-container">
                        <h3 class="section-title"><i class="fas fa-chalkboard-teacher"></i> Faculty Upload</h3>
                        <div class="form-group">
                            <label>Faculty File</label>
                            <div class="file-upload">
                                <button type="button" class="file-upload-btn">
                                    <i class="fas fa-cloud-upload-alt"></i> Choose File
                                </button>
                                <input type="file" name="faculty_file" class="file-upload-input" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                                <div class="file-name">No file chosen</div>
                            </div>
                        </div>
                        <button type="submit" name="submit_faculty" class="btn section-btn">
                            <i class="fas fa-upload"></i> Upload Faculty File
                        </button>
                    </div>

                    <!-- Admin Section -->
                    <div class="section-container">
                        <h3 class="section-title"><i class="fas fa-user-shield"></i> Admin Upload</h3>
                        <div class="form-group">
                            <label>Admin File</label>
                            <div class="file-upload">
                                <button type="button" class="file-upload-btn">
                                    <i class="fas fa-cloud-upload-alt"></i> Choose File
                                </button>
                                <input type="file" name="admin_file" class="file-upload-input" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                                <div class="file-name">No file chosen</div>
                            </div>
                        </div>
                        <button type="submit" name="submit_admin" class="btn section-btn">
                            <i class="fas fa-upload"></i> Upload Admin File
                        </button>
                    </div>

                    <!-- Text Content Section -->
                    <div class="section-container">
                        <h3 class="section-title"><i class="fas fa-align-left"></i> Text Content</h3>
                        <div class="form-group">
                            <label>Club Notice (Text)</label>
                            <textarea name="club_text" class="form-control" rows="4"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Breaking News (Text)</label>
                            <input type="text" name="breaking_text" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>General Notice</label>
                            <textarea name="general_text" class="form-control" rows="4"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Headline Ticker</label>
                            <input type="text" name="headline" class="form-control">
                        </div>
                        <button type="submit" name="submit_text" class="btn section-btn">
                            <i class="fas fa-save"></i> Save Text Content
                        </button>
                    </div>
                </form>
            </div>

            <div class="card">
                <h2><i class="fas fa-file-alt"></i> Recent Uploads</h2>
                <?php if ($uploads && $uploads->num_rows > 0): ?>
                    <ul class="uploads-list">
                        <?php while ($row = $uploads->fetch_assoc()): ?>
                        <li>
                            <div>
                                <span class="type-<?= $row['type'] ?>"><?= $row['type'] ?></span>
                                <?= basename($row['file_path']) ?>
                                <small><?= date('M d, Y H:i', strtotime($row['uploaded_at'])) ?></small>
                            </div>
                            <div class="actions">
                                <a href="admin.php?delete_upload=<?= $row['id'] ?>" 
                                   onclick="return confirm('Are you sure you want to delete this file?')"
                                   class="action-btn delete-btn">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </div>
                        </li>
                        <?php endwhile; ?>
                    </ul>
                <?php else: ?>
                    <p>No uploads found.</p>
                <?php endif; ?>
            </div>

            <div class="card">
                <h2><i class="fas fa-align-left"></i> Recent Text Entries</h2>
                <?php if ($texts && $texts->num_rows > 0): ?>
                    <ul class="text-entries-list">
                        <?php while ($row = $texts->fetch_assoc()): ?>
                        <li>
                            <div>
                                <span class="type-<?= $row['type'] ?>"><?= $row['type'] ?></span>
                                <?= substr(htmlspecialchars($row['content']), 0, 50) ?>...
                                <small><?= date('M d, Y H:i', strtotime($row['created_at'])) ?></small>
                            </div>
                            <div class="actions">
                                <a href="admin.php?delete_text=<?= $row['id'] ?>" 
                                   onclick="return confirm('Are you sure you want to delete this text?')"
                                   class="action-btn delete-btn">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </div>
                        </li>
                        <?php endwhile; ?>
                    </ul>
                <?php else: ?>
                    <p>No text entries found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // File upload display
        document.querySelectorAll('.file-upload-input').forEach(input => {
            input.addEventListener('change', function() {
                const fileName = this.files[0] ? this.files[0].name : 'No file chosen';
                this.parentElement.querySelector('.file-name').textContent = fileName;
            });
        });

        // Mobile menu toggle
        const menuToggle = document.createElement('button');
        menuToggle.className = 'menu-toggle';
        menuToggle.innerHTML = '<i class="fas fa-bars"></i>';
        document.body.appendChild(menuToggle);
        
        menuToggle.addEventListener('click', () => {
            document.querySelector('.sidebar').classList.toggle('active');
        });
    </script>
</body>
</html>