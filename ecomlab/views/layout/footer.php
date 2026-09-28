<?php
/**
 * footer.php — closing layout for every view (decor theme). Pure HTML.
 */
if (!function_exists('bowtie_svg')) {
    function bowtie_svg($class = 'bowtie') {
        return '<svg class="' . $class . '" viewBox="0 0 40 24" aria-hidden="true">'
             . '<path d="M2 2 Q20 9 38 2 L38 22 Q20 15 2 22 Z" fill="currentColor"/></svg>';
    }
}
?>
</main>

<footer class="site-footer">
    <div class="footer-inner">
        <div class="footer-top">
            <div class="footer-newsletter">
                <h2 class="serif">Stay Inspired</h2>
                <p>Discover unique home decor ideas, new arrivals and offers.</p>
                <form class="newsletter" action="#" method="POST" onsubmit="return false">
                    <label for="nl_email">Email</label>
                    <input type="email" id="nl_email" name="nl_email" placeholder="you@example.com">
                    <label class="nl-check">
                        <input type="checkbox" checked> I agree to receive newsletters and promotions
                    </label>
                    <button type="submit" class="btn-outline-light">Subscribe</button>
                </form>
            </div>

            <div class="footer-col">
                <h3>Contact Us</h3>
                <p class="muted-light">Open daily from 9:00 to 18:00</p>
                <p>+1 (555) 123-45-67</p>
                <p>hello@shoppn.store</p>
                <div class="socials">
                    <a href="#" aria-label="Twitter"><svg viewBox="0 0 24 24" class="ic"><path fill="currentColor" d="M22 5.9c-.7.3-1.5.5-2.3.6.8-.5 1.5-1.3 1.8-2.3-.8.5-1.7.8-2.6 1a4 4 0 0 0-6.8 3.7A11.3 11.3 0 0 1 3.9 4.6a4 4 0 0 0 1.2 5.3c-.6 0-1.2-.2-1.8-.5a4 4 0 0 0 3.2 3.9c-.6.2-1.2.2-1.8.1a4 4 0 0 0 3.7 2.8A8 8 0 0 1 2 17.5a11.3 11.3 0 0 0 6.1 1.8c7.3 0 11.4-6.1 11.4-11.4v-.5c.8-.6 1.5-1.3 2-2z"/></svg></a>
                    <a href="#" aria-label="Instagram"><svg viewBox="0 0 24 24" class="ic"><path fill="none" stroke="currentColor" stroke-width="2" d="M4 8a4 4 0 0 1 4-4h8a4 4 0 0 1 4 4v8a4 4 0 0 1-4 4H8a4 4 0 0 1-4-4z"/><circle cx="12" cy="12" r="3.4" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="17.3" cy="6.7" r="1.2" fill="currentColor"/></svg></a>
                    <a href="#" aria-label="LinkedIn"><svg viewBox="0 0 24 24" class="ic"><path fill="currentColor" d="M4.98 3.5A2.5 2.5 0 1 0 5 8.5a2.5 2.5 0 0 0-.02-5zM3 9h4v12H3zM10 9h3.8v1.7h.05c.53-1 1.83-2.05 3.76-2.05 4 0 4.74 2.64 4.74 6.07V21H18.5v-5.4c0-1.3 0-2.96-1.8-2.96-1.8 0-2.08 1.4-2.08 2.86V21H10z"/></svg></a>
                </div>
            </div>

            <div class="footer-col">
                <h3>Catalog</h3>
                <ul class="footer-links">
                    <li><a href="<?php echo app_url('index.php'); ?>">New Arrivals 2026</a></li>
                    <li><a href="<?php echo app_url('index.php#dream'); ?>">Living Room Decor</a></li>
                    <li><a href="<?php echo app_url('index.php#sustainable'); ?>">Eco-Friendly Finds</a></li>
                    <li><a href="<?php echo app_url('index.php#gallery'); ?>">Handmade Art</a></li>
                </ul>
                <h3 class="mt">More</h3>
                <ul class="footer-links">
                    <li><a href="<?php echo app_url('index.php#why'); ?>">Our Story</a></li>
                    <li><a href="<?php echo app_url('index.php#artisanal'); ?>">Craftsmanship</a></li>
                    <li><a href="<?php echo app_url('index.php#testimonials'); ?>">Reviews</a></li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <a class="footer-logo" href="<?php echo app_url('index.php'); ?>">
                <span>SHOP</span><?php echo bowtie_svg('bowtie big'); ?><span>PN</span>
            </a>
            <p class="footer-blurb">
                Discover unique, handpicked decor reflecting your personal style,
                crafted from sustainable materials to protect the planet you love
                while adding lasting beauty to your home.
            </p>
        </div>

        <div class="footer-legal">
            <span>&copy; <?php echo date('Y'); ?> shoppn — E-Commerce Lab</span>
            <span>Built with HTML · CSS · JavaScript · PHP · MySQL</span>
        </div>
    </div>
</footer>

<script src="<?php echo app_url('js/decor.js'); ?>"></script>
</body>
</html>
