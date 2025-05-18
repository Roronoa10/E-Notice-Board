<?php
require 'db.php';

// Fetch all notices and other content
$notices = $conn->query("SELECT * FROM uploads WHERE type='notice' ORDER BY uploaded_at DESC");
$clubNotices = $conn->query("SELECT * FROM texts WHERE type='club' ORDER BY created_at DESC");
$breakingNews = $conn->query("SELECT * FROM texts WHERE type='breaking' ORDER BY created_at DESC LIMIT 1");
$generalNotices = $conn->query("SELECT * FROM texts WHERE type='general' ORDER BY created_at DESC");
$headline = $conn->query("SELECT * FROM texts WHERE type='headline' ORDER BY created_at DESC LIMIT 1");
$publicNotices = $conn->query("SELECT * FROM public_notices WHERE is_active = TRUE ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Green University - Public Notice Board</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Modal Styles */
        .notice-modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.8);
            overflow-y: auto;
        }

        .modal-content {
            background-color: #fff;
            margin: 5% auto;
            padding: 30px;
            border-radius: 8px;
            width: 80%;
            max-width: 800px;
            box-shadow: 0 5px 30px rgba(0,0,0,0.3);
            position: relative;
        }

        .close-modal {
            position: absolute;
            top: 15px;
            right: 25px;
            font-size: 28px;
            font-weight: bold;
            color: #aaa;
            cursor: pointer;
        }

        .close-modal:hover {
            color: #333;
        }

        .modal-header {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .modal-header h2 {
            color: #2e7d32;
            margin-bottom: 5px;
        }

        .modal-date {
            color: #666;
            font-size: 14px;
        }

        .modal-body {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .modal-image-container {
            text-align: center;
            margin-bottom: 20px;
        }

        .modal-image-container img {
            max-width: 100%;
            max-height: 400px;
            border-radius: 4px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .modal-description {
            line-height: 1.6;
            color: #333;
            white-space: pre-line;
        }

        @media (max-width: 768px) {
            .modal-content {
                width: 90%;
                margin: 10% auto;
                padding: 20px;
            }
        }

        /* Public Notice Card Styles */
        .public-notice-card {
            cursor: pointer;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .public-notice-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
    </style>
    <link rel="stylesheet" href="public.css">
</head>
<body>

    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="container">
            <a href="#" class="logo">Green University</a>
            <ul class="nav-links">
                <li><a href="index.php">Notice Board</a></li>
                <li><a href="#notices">Notices</a></li>
                <li><a href="#events">Events</a></li>
                <li><a href="#news">News</a></li>
                <li><a href="login.php" class="login-btn"><i class="fas fa-lock"></i> Admin Login</a></li>
            </ul>
            <div class="hamburger">
                <div class="line"></div>
                <div class="line"></div>
                <div class="line"></div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>University Notice Board</h1>
            <p>Stay updated with all the latest announcements and events</p>
        </div>
        <div class="hero-scroll">
            <span>Scroll Down</span>
            <div class="scroll-arrow"></div>
        </div>
    </section>

    <!-- Breaking News Ticker -->
    <section class="breaking-news">
        <div class="container">
            <span class="breaking-label">Breaking:</span>
            <div class="ticker-wrap">
                <div class="ticker">
                    <?php if ($breaking = $breakingNews->fetch_assoc()): ?>
                        <?= htmlspecialchars($breaking['content']) ?>
                    <?php else: ?>
                        No breaking news at this time.
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <main class="container">
        <!-- Notices Section -->
        <section id="notices" class="section notices-section">
            <h2 class="section-title"><span>Latest Notices</span></h2>
            
            <div class="notices-container">
                <?php if ($notices->num_rows > 0): ?>
                    <div class="notice-slider">
                        <?php while($notice = $notices->fetch_assoc()): 
                            $ext = strtolower(pathinfo($notice['file_path'], PATHINFO_EXTENSION));
                            $file = 'uploads/' . basename($notice['file_path']);
                            $date = date('d M Y', strtotime($notice['uploaded_at']));
                        ?>
                            <div class="notice-card">
                                <div class="notice-header">
                                    <span class="notice-date"><?= $date ?></span>
                                    <span class="notice-badge">New</span>
                                </div>
                                <div class="notice-content">
                                    <?php if (in_array($ext, ['jpg', 'jpeg', 'png'])): ?>
                                        <div class="notice-image">
                                            <img src="<?= $file ?>" alt="Notice Image">
                                        </div>
                                    <?php elseif ($ext === 'pdf'): ?>
                                        <div class="notice-pdf">
                                            <i class="fas fa-file-pdf"></i>
                                            <p>PDF Document</p>
                                            <a href="<?= $file ?>" target="_blank" class="view-btn">View PDF</a>
                                        </div>
                                    <?php else: ?>
                                        <div class="notice-file">
                                            <i class="fas fa-file-alt"></i>
                                            <p>Document File</p>
                                            <a href="<?= $file ?>" target="_blank" class="view-btn">Download</a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-notice">
                        <i class="fas fa-bell-slash"></i>
                        <p>No notices available at this time</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Events Section -->
        <section id="events" class="section events-section">
            <h2 class="section-title"><span>Upcoming Events</span></h2>
            
            <div class="events-timeline">
                <?php if ($clubNotices->num_rows > 0): ?>
                    <?php while($event = $clubNotices->fetch_assoc()): 
                        $date = date('d M Y', strtotime($event['created_at']));
                    ?>
                        <div class="event-item">
                            <div class="event-date">
                                <span><?= $date ?></span>
                            </div>
                            <div class="event-content">
                                <p><?= htmlspecialchars($event['content']) ?></p>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-event">
                        <i class="fas fa-calendar-times"></i>
                        <p>No upcoming events scheduled</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- General News Section -->
        <section id="news" class="section news-section">
            <h2 class="section-title"><span>General News</span></h2>
            
            <div class="news-container">
                <?php if ($generalNotices->num_rows > 0): ?>
                    <?php while($news = $generalNotices->fetch_assoc()): 
                        $date = date('d M Y', strtotime($news['created_at']));
                    ?>
                        <div class="news-card">
                            <div class="news-date"><?= $date ?></div>
                            <div class="news-content">
                                <p><?= htmlspecialchars($news['content']) ?></p>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-news">
                        <i class="fas fa-newspaper"></i>
                        <p>No news updates at this time</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Public Notices Section -->
        <section id="public-notices" class="section public-notices-section">
            <h2 class="section-title"><span>Public Notices</span></h2>
            
            <div class="public-notices-container">
                <?php if ($publicNotices->num_rows > 0): ?>
                    <div class="public-notices-grid">
                        <?php while($notice = $publicNotices->fetch_assoc()): 
                            $date = date('d M Y', strtotime($notice['created_at']));
                        ?>
                            <div class="public-notice-card" 
                                 data-title="<?= htmlspecialchars($notice['title']) ?>" 
                                 data-desc="<?= htmlspecialchars($notice['description']) ?>" 
                                 data-date="<?= $date ?>"
                                 data-image="<?= !empty($notice['full_image_path']) ? htmlspecialchars($notice['full_image_path']) : htmlspecialchars($notice['thumbnail_path']) ?>">
                                <div class="public-notice-thumbnail">
                                    <img src="<?= htmlspecialchars($notice['thumbnail_path']) ?>" alt="<?= htmlspecialchars($notice['title']) ?>">
                                </div>
                                <div class="public-notice-content">
                                    <h3><?= htmlspecialchars($notice['title']) ?></h3>
                                    <div class="public-notice-date"><?= $date ?></div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-public-notice">
                        <i class="fas fa-bullhorn"></i>
                        <p>No public notices available at this time</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <!-- Modal for Public Notices -->
    <div class="notice-modal" id="noticeModal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <div class="modal-header">
                <h2 id="modalNoticeTitle"></h2>
                <div class="modal-date" id="modalNoticeDate"></div>
            </div>
            <div class="modal-body">
                <div class="modal-image-container">
                    <img id="modalNoticeImage" src="" alt="Notice Image">
                </div>
                <div class="modal-description" id="modalNoticeDesc"></div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-logo">
                    <h3>Green University</h3>
                    <p>Excellence in Education</p>
                </div>
                <div class="footer-links">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="#notices">Notices</a></li>
                        <li><a href="#events">Events</a></li>
                        <li><a href="#news">News</a></li>
                        <li><a href="login.php">Admin Login</a></li>
                    </ul>
                </div>
                <div class="footer-contact">
                    <h4>Contact Us</h4>
                    <p><i class="fas fa-map-marker-alt"></i> 123 University Ave, Green City</p>
                    <p><i class="fas fa-phone"></i> +1 234 567 890</p>
                    <p><i class="fas fa-envelope"></i> info@greenuniversity.edu</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> Green University. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Get the modal
            const modal = document.getElementById('noticeModal');
            const modalTitle = document.getElementById('modalNoticeTitle');
            const modalDesc = document.getElementById('modalNoticeDesc');
            const modalDate = document.getElementById('modalNoticeDate');
            const modalImage = document.getElementById('modalNoticeImage');
            const closeBtn = document.querySelector('.close-modal');

            // Get all notice cards
            const noticeCards = document.querySelectorAll('.public-notice-card');

            // Add click event to each notice card
            noticeCards.forEach(card => {
                card.addEventListener('click', function() {
                    // Get data attributes
                    const title = this.getAttribute('data-title');
                    const desc = this.getAttribute('data-desc');
                    const date = this.getAttribute('data-date');
                    const image = this.getAttribute('data-image');

                    // Set modal content
                    modalTitle.textContent = title;
                    modalDesc.textContent = desc;
                    modalDate.textContent = date;
                    modalImage.src = image;
                    modalImage.alt = title;

                    // Display modal
                    modal.style.display = 'block';
                    document.body.style.overflow = 'hidden';
                });
            });

            // Close modal when X is clicked
            closeBtn.addEventListener('click', function() {
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
            });

            // Close modal when clicking outside content
            window.addEventListener('click', function(event) {
                if (event.target === modal) {
                    modal.style.display = 'none';
                    document.body.style.overflow = 'auto';
                }
            });

            // Close modal with ESC key
            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape' && modal.style.display === 'block') {
                    modal.style.display = 'none';
                    document.body.style.overflow = 'auto';
                }
            });
        });
    </script>
    <script src="public.js"></script>
</body>
</html>