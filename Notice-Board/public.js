document.addEventListener('DOMContentLoaded', function() {
    // Mobile Navigation
    const hamburger = document.querySelector('.hamburger');
    const navLinks = document.querySelector('.nav-links');
    
    hamburger.addEventListener('click', function() {
        this.classList.toggle('active');
        navLinks.classList.toggle('active');
    });
    
    // Close mobile menu when clicking a link
    document.querySelectorAll('.nav-links a').forEach(link => {
        link.addEventListener('click', () => {
            hamburger.classList.remove('active');
            navLinks.classList.remove('active');
        });
    });
    
    // Animate elements when they come into view
    const animateOnScroll = function() {
        const elements = document.querySelectorAll('.notice-card, .event-item, .news-card');
        
        elements.forEach(element => {
            const elementPosition = element.getBoundingClientRect().top;
            const screenPosition = window.innerHeight / 1.3;
            
            if (elementPosition < screenPosition) {
                element.style.animation = `fadeInUp 0.5s ease forwards`;
            }
        });
    };
    
    window.addEventListener('scroll', animateOnScroll);
    animateOnScroll(); // Run once on load
    
    // Auto-scroll notice slider
    const noticeSlider = document.querySelector('.notice-slider');
    if (noticeSlider) {
        let scrollAmount = 0;
        const scrollSpeed = 1; // pixels per frame
        
        function autoScroll() {
            scrollAmount += scrollSpeed;
            noticeSlider.scrollLeft = scrollAmount;
            
            // Reset scroll position when reaching end
            if (scrollAmount >= noticeSlider.scrollWidth - noticeSlider.clientWidth) {
                scrollAmount = 0;
                noticeSlider.scrollLeft = 0;
            }
            
            requestAnimationFrame(autoScroll);
        }
        
        // Pause on hover
        noticeSlider.addEventListener('mouseenter', () => {
            scrollSpeed = 0;
        });
        
        noticeSlider.addEventListener('mouseleave', () => {
            scrollSpeed = 1;
        });
        
        autoScroll();
    }
    
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            
            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                window.scrollTo({
                    top: targetElement.offsetTop - 80,
                    behavior: 'smooth'
                });
            }
        });
    });
    
    // Current year in footer
    document.querySelector('.footer-bottom p').innerHTML = 
        document.querySelector('.footer-bottom p').innerHTML.replace('<?= date("Y") ?>', new Date().getFullYear());
});