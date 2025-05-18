<?php
require 'db.php';

$notices = $conn->query("SELECT * FROM uploads WHERE type='notice' ORDER BY uploaded_at DESC");
$clubNotices = $conn->query("SELECT * FROM texts WHERE type='club' ORDER BY created_at DESC");
$breakingNews = $conn->query("SELECT * FROM texts WHERE type='breaking' ORDER BY created_at DESC LIMIT 1");
$generalNotices = $conn->query("SELECT * FROM texts WHERE type='general' ORDER BY created_at DESC");
$headline = $conn->query("SELECT * FROM texts WHERE type='headline' ORDER BY created_at DESC LIMIT 1");
$videos = $conn->query("SELECT * FROM uploads WHERE type='video' ORDER BY uploaded_at DESC");
$faculty = $conn->query("SELECT * FROM uploads WHERE type='faculty' ORDER BY uploaded_at DESC");
$adminFiles = $conn->query("SELECT * FROM uploads WHERE type='admin' ORDER BY uploaded_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Notice Board</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Additional inline styles for the sliders */
        .notice-slider, .faculty-slider, .admin-slider {
            position: relative;
            width: 100%;
            height: 100%;
        }
        
        .notice-slide, .faculty-slide, .admin-slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            transition: opacity 1s ease-in-out;
            display: flex;
            justify-content: center;
            align-items: center;
            background: white;
        }
        
        .notice-slide.active, .faculty-slide.active, .admin-slide.active {
            opacity: 1;
        }
        
        .notice-slide img, .faculty-slide img, .admin-slide img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        
        .notice-slide iframe, .faculty-slide iframe, .admin-slide iframe {
            width: 100%;
            height: 100%;
            border: none;
        }
        
        .video-container {
            width: 100%;
            height: 100%;
            background: black;
        }
        
        .video-container video {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        
        .admin-title {
            color: #e65100;
            font-weight: bold;
        }
        
        /* Fix for ticker */
        .breaking-ticker-wrapper {
            width: 100%;
            overflow: hidden;
        }
        
        .breaking-ticker {
            display: inline-block;
            white-space: nowrap;
            padding-left: 100%;
            animation: scroll-left 20s linear infinite;
        }
        
        @keyframes scroll-left {
            0% { transform: translateX(0); }
            100% { transform: translateX(-100%); }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Green University Notice Board</div>
        <div class="time" id="timeNow">--:--:--</div>
    </div>

    <div class="main">
        <div class="left">
            <div class="notices">
                <h3>About Us</h3>
                <div class="notice-container">
                    <?php 
                    $notice_items = [];
                    while($notice = $notices->fetch_assoc()): 
                        $ext = strtolower(pathinfo($notice['file_path'], PATHINFO_EXTENSION));
                        $file = 'uploads/' . basename($notice['file_path']);
                        $notice_items[] = ['file' => $file, 'ext' => $ext];
                    endwhile; 
                    ?>
                    
                    <?php if (!empty($notice_items)): ?>
                        <div class="notice-slider">
                            <?php foreach ($notice_items as $index => $item): ?>
                                <div class="notice-slide <?= $index === 0 ? 'active' : '' ?>">
                                    <?php if (in_array($item['ext'], ['jpg', 'jpeg', 'png'])): ?>
                                        <img src="<?= $item['file'] ?>">
                                    <?php elseif ($item['ext'] === 'pdf'): ?>
                                        <iframe src="<?= $item['file'] ?>#toolbar=0"></iframe>
                                    <?php else: ?>
                                        <a href="<?= $item['file'] ?>" target="_blank">Download File</a>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p>No notices available</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="club">
                
                <div class="club-scroll-container">
                    <h3 style="display: flex; justify-content: center; margin-bottom: 20px; font-family: 'Times New Roman', Times, serif;">
                    Important Dates
                </h3>
                    <?php while($club = $clubNotices->fetch_assoc()): ?>
                        <div class="club-item">
                            <p><?= htmlspecialchars($club['content']) ?></p>
                            <small><?= date('d M Y', strtotime($club['created_at'])) ?></small>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>

        <div class="center">
            <div class="video">
                <h3 style="display: flex; justify-content: center; align-items: center; margin-bottom: 20px; font-family: 'Times New Roman', Times, serif;">Video</h3>
                <?php 
                $video_items = $videos->fetch_all(MYSQLI_ASSOC);
                if (!empty($video_items)): ?>
                    <div class="video-container">
                        <video id="videoPlayer" autoplay muted loop></video>
                    </div>
                <?php else: ?>
                    <p>No videos available</p>
                <?php endif; ?>
            </div>

            <div class="breaking">
                <h3 style="display: flex; justify-content: center; margin-bottom: 2px; font-family: 'Times New Roman', Times, serif;">
                    Breaking News
                </h3>
                <div class="breaking-ticker-wrapper">
                    <div class="breaking-ticker">
                        <?php if ($breaking = $breakingNews->fetch_assoc()): ?>
                            <?= htmlspecialchars($breaking['content']) ?>
                        <?php else: ?>
                            No breaking news available
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="general-text">
                
                <div class="general-scroll-container">
                    <h3 style="display: flex; justify-content: center; align-items: center; margin-bottom: 2px; font-family: 'Times New Roman', Times, serif;">General Notices</h3>
                    <?php while($general = $generalNotices->fetch_assoc()): ?>
                        <div class="general-item">
                            <p><?= htmlspecialchars($general['content']) ?></p>
                            <small><?= date('d M Y', strtotime($general['created_at'])) ?></small>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>

        <div class="right">
            <!-- Faculty Section -->
            <div class="faculty">
                <h3>Notice</h3>
                <?php 
                $faculty_items = [];
                while($fac = $faculty->fetch_assoc()): 
                    $ext = strtolower(pathinfo($fac['file_path'], PATHINFO_EXTENSION));
                    $file = 'uploads/' . basename($fac['file_path']);
                    $faculty_items[] = ['file' => $file, 'ext' => $ext];
                endwhile; 
                ?>
                
                <?php if (!empty($faculty_items)): ?>
                    <div class="faculty-slider">
                        <?php foreach ($faculty_items as $index => $item): ?>
                            <div class="faculty-slide <?= $index === 0 ? 'active' : '' ?>">
                                <?php if ($item['ext'] === 'pdf'): ?>
                                    <iframe src="<?= $item['file'] ?>#toolbar=0"></iframe>
                                <?php elseif (in_array($item['ext'], ['jpg', 'jpeg', 'png'])): ?>
                                    <img src="<?= $item['file'] ?>">
                                <?php else: ?>
                                    <a href="<?= $item['file'] ?>" target="_blank">Download File</a>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p>No faculty documents available</p>
                <?php endif; ?>
            </div>

            <!-- Admin Section -->
            <div class="admin">
                <h3 class="admin-title">Admin Notices</h3>
                <?php 
                $admin_items = [];
                while($admin = $adminFiles->fetch_assoc()): 
                    $ext = strtolower(pathinfo($admin['file_path'], PATHINFO_EXTENSION));
                    $file = 'uploads/' . basename($admin['file_path']);
                    $admin_items[] = ['file' => $file, 'ext' => $ext];
                endwhile; 
                ?>
                
                <?php if (!empty($admin_items)): ?>
                    <div class="admin-slider">
                        <?php foreach ($admin_items as $index => $item): ?>
                            <div class="admin-slide <?= $index === 0 ? 'active' : '' ?>">
                                <?php if ($item['ext'] === 'pdf'): ?>
                                    <iframe src="<?= $item['file'] ?>#toolbar=0"></iframe>
                                <?php elseif (in_array($item['ext'], ['jpg', 'jpeg', 'png'])): ?>
                                    <img src="<?= $item['file'] ?>">
                                <?php else: ?>
                                    <a href="<?= $item['file'] ?>" target="_blank">Download File</a>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p>No admin documents available</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="footer">
        <div class="ticker">
            <?php if($headlineText = $headline->fetch_assoc()): ?>
                <?= htmlspecialchars($headlineText['content']) ?>
            <?php else: ?>
                Welcome to Green University Notice Board
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Wait for DOM to be fully loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Notice Slider
            const noticeSlides = document.querySelectorAll('.notice-slide');
            let currentNoticeSlide = 0;
            let noticeInterval;
            
            function showNextNotice() {
                noticeSlides[currentNoticeSlide].classList.remove('active');
                currentNoticeSlide = (currentNoticeSlide + 1) % noticeSlides.length;
                noticeSlides[currentNoticeSlide].classList.add('active');
            }
            
            if (noticeSlides.length > 1) {
                noticeInterval = setInterval(showNextNotice, 5000); // Change every 5 seconds
            }
            
            // Initialize Faculty Slider
            const facultySlides = document.querySelectorAll('.faculty-slide');
            let currentFacultySlide = 0;
            let facultyInterval;
            
            function showNextFaculty() {
                facultySlides[currentFacultySlide].classList.remove('active');
                currentFacultySlide = (currentFacultySlide + 1) % facultySlides.length;
                facultySlides[currentFacultySlide].classList.add('active');
            }
            
            if (facultySlides.length > 1) {
                facultyInterval = setInterval(showNextFaculty, 7000); // Change every 7 seconds
            }
            
            // Initialize Admin Slider
            const adminSlides = document.querySelectorAll('.admin-slide');
            let currentAdminSlide = 0;
            let adminInterval;
            
            function showNextAdmin() {
                adminSlides[currentAdminSlide].classList.remove('active');
                currentAdminSlide = (currentAdminSlide + 1) % adminSlides.length;
                adminSlides[currentAdminSlide].classList.add('active');
            }
            
            if (adminSlides.length > 1) {
                adminInterval = setInterval(showNextAdmin, 6000); // Change every 6 seconds
            }
            
            // Initialize Video Player
            const videos = <?= json_encode(array_map(function($v) { 
                return 'uploads/' . basename($v['file_path']); 
            }, $video_items)) ?>;
            const videoPlayer = document.getElementById('videoPlayer');
            let currentVideoIndex = 0;
            
            function playNextVideo() {
                if (videos.length === 0) return;
                
                currentVideoIndex = (currentVideoIndex + 1) % videos.length;
                videoPlayer.src = videos[currentVideoIndex];
                videoPlayer.load();
                videoPlayer.play().catch(e => {
                    console.error('Video playback error:', e);
                    // If error occurs, try next video after delay
                    setTimeout(playNextVideo, 1000);
                });
            }
            
            if (videoPlayer && videos.length > 0) {
                videoPlayer.src = videos[0];
                videoPlayer.load();
                videoPlayer.play().catch(e => {
                    console.error('Initial video playback error:', e);
                    playNextVideo();
                });
                
                videoPlayer.addEventListener('ended', playNextVideo);
                videoPlayer.addEventListener('error', () => {
                    setTimeout(playNextVideo, 1000);
                });
            }
            
            // Update Time Display
            function updateTime() {
                const now = new Date();
                const timeString = now.toLocaleTimeString('en-US', {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                    hour12: false
                });
                document.getElementById('timeNow').textContent = timeString;
            }
            
            setInterval(updateTime, 1000);
            updateTime();
            
            // Pause sliders when window loses focus
            window.addEventListener('blur', function() {
                if (noticeInterval) clearInterval(noticeInterval);
                if (facultyInterval) clearInterval(facultyInterval);
                if (adminInterval) clearInterval(adminInterval);
            });
            
            window.addEventListener('focus', function() {
                if (noticeSlides.length > 1) {
                    noticeInterval = setInterval(showNextNotice, 5000);
                }
                if (facultySlides.length > 1) {
                    facultyInterval = setInterval(showNextFaculty, 7000);
                }
                if (adminSlides.length > 1) {
                    adminInterval = setInterval(showNextAdmin, 6000);
                }
            });
            
            // Initialize Club Notices scroll (without duplication)
            const clubContainer = document.querySelector('.club-scroll-container');
            if (clubContainer) {
                clubContainer.style.animation = 'scroll-up 30s linear infinite';
            }
            
            // Initialize General Notices scroll (without duplication)
            const generalContainer = document.querySelector('.general-scroll-container');
            if (generalContainer) {
                generalContainer.style.animation = 'scroll-up 30s linear infinite';
            }
        });
    </script>
</body>
</html>