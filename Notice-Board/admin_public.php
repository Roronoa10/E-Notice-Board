<?php
session_start();
require 'db.php';

if (!isset($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit;
}

$message = '';
if (isset($_GET['success'])) {
    $message = '<div class="alert success">Public notice updated successfully!</div>';
} elseif (isset($_GET['error'])) {
    $message = '<div class="alert error">Error: '.htmlspecialchars($_GET['error']).'</div>';
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("SELECT thumbnail_path, full_image_path FROM public_notices WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    if ($row) {
        if (file_exists($row['thumbnail_path'])) unlink($row['thumbnail_path']);
        if (!empty($row['full_image_path']) && file_exists($row['full_image_path'])) unlink($row['full_image_path']);
    }
    
    $stmt = $conn->prepare("DELETE FROM public_notices WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        header("Location: admin_public.php?success=1");
        exit;
    } else {
        header("Location: admin_public.php?error=Delete+failed");
        exit;
    }
}

// Toggle active status
if (isset($_GET['toggle'])) {
    $id = $_GET['toggle'];
    $stmt = $conn->prepare("UPDATE public_notices SET is_active = NOT is_active WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: admin_public.php");
    exit;
}

// Handle form submission (add or edit)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : null;
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    
    if (empty($title) || empty($description)) {
        header("Location: admin_public.php?error=Title+and+description+are+required");
        exit;
    }
    
    $uploadDir = "uploads/public/";
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    // Get existing data if editing
    $existingData = null;
    if ($id) {
        $stmt = $conn->prepare("SELECT thumbnail_path, full_image_path FROM public_notices WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $existingData = $result->fetch_assoc();
    }
    
    // Handle thumbnail upload
    $thumbnailPath = $existingData['thumbnail_path'] ?? '';
    if (!empty($_FILES['thumbnail']['name'])) {
        // Delete old thumbnail if exists
        if (!empty($thumbnailPath) && file_exists($thumbnailPath)) {
            unlink($thumbnailPath);
        }
        
        // Upload new thumbnail
        $thumbnailName = time() . '_thumb_' . basename($_FILES['thumbnail']['name']);
        $thumbnailPath = $uploadDir . $thumbnailName;
        
        if (!move_uploaded_file($_FILES['thumbnail']['tmp_name'], $thumbnailPath)) {
            header("Location: admin_public.php?error=Thumbnail+upload+failed");
            exit;
        }
    }
    
    // Handle full image upload
    $fullImagePath = $existingData['full_image_path'] ?? '';
    if (!empty($_FILES['full_image']['name'])) {
        // Delete old full image if exists
        if (!empty($fullImagePath) && file_exists($fullImagePath)) {
            unlink($fullImagePath);
        }
        
        // Upload new full image
        $fullImageName = time() . '_full_' . basename($_FILES['full_image']['name']);
        $fullImagePath = $uploadDir . $fullImageName;
        
        if (!move_uploaded_file($_FILES['full_image']['tmp_name'], $fullImagePath)) {
            // Clean up thumbnail if we just uploaded it
            if (!empty($_FILES['thumbnail']['name']) && file_exists($thumbnailPath)) {
                unlink($thumbnailPath);
            }
            header("Location: admin_public.php?error=Full+image+upload+failed");
            exit;
        }
    }
    
    if ($id) {
        // Update existing notice
        $stmt = $conn->prepare("UPDATE public_notices SET title = ?, description = ?, thumbnail_path = ?, full_image_path = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $title, $description, $thumbnailPath, $fullImagePath, $id);
    } else {
        // Insert new notice
        $stmt = $conn->prepare("INSERT INTO public_notices (title, description, thumbnail_path, full_image_path) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $title, $description, $thumbnailPath, $fullImagePath);
    }
    
    if ($stmt->execute()) {
        header("Location: admin_public.php?success=1");
        exit;
    } else {
        // Clean up uploaded files if database operation failed
        if (!empty($_FILES['thumbnail']['name']) && file_exists($thumbnailPath)) {
            unlink($thumbnailPath);
        }
        if (!empty($_FILES['full_image']['name']) && file_exists($fullImagePath)) {
            unlink($fullImagePath);
        }
        header("Location: admin_public.php?error=Database+error");
        exit;
    }
}

// Get notice for editing
$editNotice = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM public_notices WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $editNotice = $result->fetch_assoc();
}

// Get all public notices
$notices = $conn->query("SELECT * FROM public_notices ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Public Notices</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="admin_public.css">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h3>Green University</h3>
            </div>
            <div class="sidebar-menu">
                <a href="admin.php" class="menu-item">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Board-Panel</span>
                </a>
                <a href="admin_public.php" class="menu-item active">
                    <i class="fas fa-bullhorn"></i>
                    <span>Public Notices</span>
                </a>
                <a href="admin.php" class="menu-item">
                    <i class="fas fa-file-upload"></i>
                    <span>File Uploads</span>
                </a>
                <a href="admin.php" class="menu-item">
                    <i class="fas fa-edit"></i>
                    <span>Text Content</span>
                </a>
                <a href="admin.php" class="menu-item">
                    <i class="fas fa-users"></i>
                    <span>User Management</span>
                </a>
                <a href="admin.php" class="menu-item">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1>Public Notices Management</h1>
                <div class="user-info">
                    <a href="logout.php" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i>
                        Logout
                    </a>
                </div>
            </div>

            <?php echo $message; ?>

            <div class="card">
                <h2><i class="fas fa-plus-circle"></i> <?= $editNotice ? 'Edit' : 'Add New' ?> Public Notice</h2>
                <form action="" method="POST" enctype="multipart/form-data">
                    <?php if ($editNotice): ?>
                        <input type="hidden" name="id" value="<?= $editNotice['id'] ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="title">Notice Title</label>
                        <input type="text" id="title" name="title" class="form-control" 
                               value="<?= $editNotice ? htmlspecialchars($editNotice['title']) : '' ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" class="form-control" required><?= 
                            $editNotice ? htmlspecialchars($editNotice['description']) : '' 
                        ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Thumbnail Image (Required)</label>
                        <div class="file-upload">
                            <button type="button" class="file-upload-btn">
                                <i class="fas fa-cloud-upload-alt"></i> 
                                <?= $editNotice ? 'Change Thumbnail' : 'Choose Thumbnail' ?>
                            </button>
                            <input type="file" name="thumbnail" class="file-upload-input" accept="image/*" 
                                   <?= !$editNotice ? 'required' : '' ?>>
                            <div class="file-name">
                                <?= $editNotice ? basename($editNotice['thumbnail_path']) : 'No file chosen' ?>
                            </div>
                        </div>
                        <?php if ($editNotice): ?>
                            <small>Leave empty to keep existing thumbnail</small>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label>Full Image (Optional)</label>
                        <div class="file-upload">
                            <button type="button" class="file-upload-btn">
                                <i class="fas fa-cloud-upload-alt"></i> 
                                <?= $editNotice ? 'Change Full Image' : 'Choose Full Image' ?>
                            </button>
                            <input type="file" name="full_image" class="file-upload-input" accept="image/*">
                            <div class="file-name">
                                <?= $editNotice && !empty($editNotice['full_image_path']) ? 
                                    basename($editNotice['full_image_path']) : 'No file chosen' ?>
                            </div>
                        </div>
                        <?php if ($editNotice && !empty($editNotice['full_image_path'])): ?>
                            <small>Leave empty to keep existing full image</small>
                        <?php endif; ?>
                    </div>
                    
                    <button type="submit" class="btn btn-block">
                        <i class="fas fa-save"></i> <?= $editNotice ? 'Update' : 'Save' ?> Notice
                    </button>
                    
                    <?php if ($editNotice): ?>
                        <a href="admin_public.php" class="btn btn-block btn-secondary">
                            <i class="fas fa-times"></i> Cancel Edit
                        </a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="card">
                <h2><i class="fas fa-list"></i> Existing Public Notices</h2>
                <div class="notices-grid">
                    <?php if ($notices->num_rows > 0): ?>
                        <?php while($notice = $notices->fetch_assoc()): ?>
                            <div class="notice-card">
                                <div class="notice-thumbnail">
                                    <img src="<?= htmlspecialchars($notice['thumbnail_path']) ?>" alt="<?= htmlspecialchars($notice['title']) ?>">
                                    <div class="notice-status <?= $notice['is_active'] ? '' : 'inactive' ?>">
                                        <?= $notice['is_active'] ? 'Active' : 'Inactive' ?>
                                    </div>
                                </div>
                                <div class="notice-content">
                                    <h3 class="notice-title"><?= htmlspecialchars($notice['title']) ?></h3>
                                    <div class="notice-date">
                                        <?= date('d M Y', strtotime($notice['created_at'])) ?>
                                    </div>
                                    <p class="notice-desc">
                                        <?= htmlspecialchars($notice['description']) ?>
                                    </p>
                                    <div class="notice-actions">
                                        <button class="action-btn toggle-btn" onclick="window.location.href='admin_public.php?toggle=<?= $notice['id'] ?>'">
                                            <i class="fas fa-power-off"></i> Toggle
                                        </button>
                                        <button class="action-btn edit-btn" onclick="window.location.href='admin_public.php?edit=<?= $notice['id'] ?>'">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <button class="action-btn delete-btn" onclick="if(confirm('Are you sure you want to delete this notice?')) window.location.href='admin_public.php?delete=<?= $notice['id'] ?>'">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #777;">
                            <i class="fas fa-bell-slash" style="font-size: 3rem; margin-bottom: 15px;"></i>
                            <h3>No public notices found</h3>
                            <p>Add your first public notice using the form above</p>
                        </div>
                    <?php endif; ?>
                </div>
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

        // Responsive sidebar toggle
        function handleSidebar() {
            if (window.innerWidth <= 768) {
                document.querySelector('.sidebar').style.width = '0';
                document.querySelector('.main-content').style.marginLeft = '0';
            } else if (window.innerWidth <= 992) {
                document.querySelector('.sidebar').style.width = '70px';
                document.querySelector('.main-content').style.marginLeft = '70px';
            } else {
                document.querySelector('.sidebar').style.width = '250px';
                document.querySelector('.main-content').style.marginLeft = '250px';
            }
        }
        
        window.addEventListener('resize', handleSidebar);
        handleSidebar();
    </script>
</body>
</html>