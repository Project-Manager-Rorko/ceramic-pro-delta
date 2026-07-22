<?php
/**
 * Template Name: Media (Blog & Videos)
 * A custom page template for displaying the tabbed Blog and Videos layout.
 */

get_header(); 
?>

<div class="media-page-container pdng-lt-rt pdng-tp-btm all-heading" >
    
    <div class="media-tabs-wrapper">
        <div class="media-tab-buttons">
            <button class="media-tab-btn active" data-target="tab-blogs" data-title="Blogs">Blogs</button>
            <button class="media-tab-btn" data-target="tab-videos" data-title="Videos">Videos</button>
        </div>

        <h1 class="media-tab-title" id="media-dynamic-title">Blogs</h1>

        <div class="media-tab-content active" id="tab-blogs">
            <?php 
            // This calls the AJAX blog layout we built in functions.php
            echo do_shortcode('[custom_blog_layout]'); 
            ?>
        </div>

        <div class="media-tab-content" id="tab-videos">
            <?php 
            // This calls the Custom Post Type video grid we built in functions.php
            echo do_shortcode('[video_gallery]'); 
            ?>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var tabButtons = document.querySelectorAll('.media-tab-btn');
            var tabContents = document.querySelectorAll('.media-tab-content');
            var dynamicTitle = document.getElementById('media-dynamic-title');

            // 1. Helper Function: Switches the active tab based on the target ID
            function openTab(targetId) {
                var targetBtn = document.querySelector('.media-tab-btn[data-target="' + targetId + '"]');
                if (!targetBtn) return;

                // Remove active classes from everything
                tabButtons.forEach(b => b.classList.remove('active'));
                tabContents.forEach(c => c.classList.remove('active'));

                // Add active class to the specific tab and button
                targetBtn.classList.add('active');
                dynamicTitle.textContent = targetBtn.getAttribute('data-title');
                
                var targetContent = document.getElementById(targetId);
                if (targetContent) {
                    targetContent.classList.add('active');
                }
            }

            // 2. Handle normal clicks on the main tab buttons
            tabButtons.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    openTab(this.getAttribute('data-target'));
                });
            });

            // 3. Handle page loads with #tab-videos in the URL (e.g., coming from another page)
            if (window.location.hash === '#tab-videos') {
                openTab('tab-videos');
            }

            // 4. Handle clicks on ANY <a href="#tab-videos"> links on this same page
            document.querySelectorAll('a[href="#tab-videos"]').forEach(function(link) {
                link.addEventListener('click', function(e) {
                    e.preventDefault(); // Stop the default choppy jump
                    openTab('tab-videos'); // Switch the tab
                    
                    // Smoothly scroll down to the tabs section
                    var mediaSection = document.querySelector('.media-tabs-wrapper');
                    if (mediaSection) {
                        mediaSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                });
            });
        });
    </script>
</div>

<?php 
get_footer(); 
?>