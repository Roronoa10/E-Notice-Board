<?php
session_start();
require 'db.php';

if (!isset($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit;
}

$uploadDir = "uploads/";
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Handle Notice Upload
if (isset($_POST['submit_notice']) && !empty($_FILES['notice_file']['name'])) {
    $filename = time() . '_' . basename($_FILES['notice_file']['name']);
    $targetPath = $uploadDir . $filename;
    
    if (move_uploaded_file($_FILES['notice_file']['tmp_name'], $targetPath)) {
        $stmt = $conn->prepare("INSERT INTO uploads (type, file_path) VALUES ('notice', ?)");
        $stmt->bind_param("s", $targetPath);
        $stmt->execute();
        header("Location: admin.php?success=1");
    } else {
        header("Location: admin.php?error=Notice+upload+failed");
    }
    exit;
}

// Handle Video Upload
if (isset($_POST['submit_video']) && !empty($_FILES['video_file']['name'])) {
    $filename = time() . '_' . basename($_FILES['video_file']['name']);
    $targetPath = $uploadDir . $filename;
    
    if (move_uploaded_file($_FILES['video_file']['tmp_name'], $targetPath)) {
        $stmt = $conn->prepare("INSERT INTO uploads (type, file_path) VALUES ('video', ?)");
        $stmt->bind_param("s", $targetPath);
        $stmt->execute();
        header("Location: admin.php?success=1");
    } else {
        header("Location: admin.php?error=Video+upload+failed");
    }
    exit;
}

// Handle Faculty Upload
if (isset($_POST['submit_faculty']) && !empty($_FILES['faculty_file']['name'])) {
    $filename = time() . '_' . basename($_FILES['faculty_file']['name']);
    $targetPath = $uploadDir . $filename;
    
    if (move_uploaded_file($_FILES['faculty_file']['tmp_name'], $targetPath)) {
        $stmt = $conn->prepare("INSERT INTO uploads (type, file_path) VALUES ('faculty', ?)");
        $stmt->bind_param("s", $targetPath);
        $stmt->execute();
        header("Location: admin.php?success=1");
    } else {
        header("Location: admin.php?error=Faculty+file+upload+failed");
    }
    exit;
}

// Handle Admin Upload
if (isset($_POST['submit_admin']) && !empty($_FILES['admin_file']['name'])) {
    $filename = time() . '_' . basename($_FILES['admin_file']['name']);
    $targetPath = $uploadDir . $filename;
    
    if (move_uploaded_file($_FILES['admin_file']['tmp_name'], $targetPath)) {
        $stmt = $conn->prepare("INSERT INTO uploads (type, file_path) VALUES ('admin', ?)");
        $stmt->bind_param("s", $targetPath);
        $stmt->execute();
        header("Location: admin.php?success=1");
    } else {
        header("Location: admin.php?error=Admin+file+upload+failed");
    }
    exit;
}

// Handle Text Content
if (isset($_POST['submit_text'])) {
    $types = ['club', 'breaking', 'general', 'headline'];
    
    foreach ($types as $type) {
        $field = $type . '_text';
        if (!empty($_POST[$field])) {
            $content = trim($_POST[$field]);
            $stmt = $conn->prepare("INSERT INTO texts (type, content) VALUES (?, ?)");
            $stmt->bind_param("ss", $type, $content);
            $stmt->execute();
        }
    }
    
    header("Location: admin.php?success=1");
    exit;
}

header("Location: admin.php?error=No+content+submitted");
?>