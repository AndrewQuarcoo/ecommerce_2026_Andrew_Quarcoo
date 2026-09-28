<?php
/**
 * home.php — the shoppn storefront landing page (decor theme).
 *
 * Loaded via index.php (which has required core/core.php). Reference-inspired
 * layout: hero + animated masthead, Dream Decor carousel, Most Loved Pieces
 * grid + feature, lifestyle band, image reveal, Sustainable split, testimonials
 * carousel and inspiration gallery.
 *
 * NOTE: images are currently pulled from Pexels for the preview. They are listed
 * in one place below so they can be swapped to locally-hosted files later.
 */
if (!function_exists('is_logged_in')) {
    require_once __DIR__ . '/../core/core.php';
}

/** Build a Pexels CDN URL for a photo id at a given width. */
function px($id, $w = 1200) {
    return 'https://images.pexels.com/photos/' . $id . '/pexels-photo-' . $id
         . '.jpeg?auto=compress&cs=tinysrgb&w=' . $w;
}

/**
 * Photography is curated to one warm palette — terracotta, clay, oatmeal and
 * cream — so the page reads as a single set rather than mixed stock. Cool,
 * grey or high-colour shots are deliberately avoided.
 */
$IMG = [
    'hero'      => px(32631105, 1600), // terracotta wall, cream sofa
    'lifestyle' => px(27059629, 1600), // wide, cream sofas + wooden table
    'feature'   => px(36054455, 1000), // glowing pleated lamp
    'bedroom'   => px(34794661, 1000), // pale vases on wood
    'artisan'   => px(9145541, 1200),  // potter's hands, warm wooden workshop
    'maker'     => px(19867573, 800),  // clay on the wheel, terracotta tones
    'dream'     => [
        ['img' => px(12177909, 800), 'label' => 'Warm Living'],
        ['img' => px(24994847, 800), 'label' => 'Cozy Corners'],
        ['img' => px(4503819, 800),  'label' => 'Modern Accents'],
        ['img' => px(32159332, 800), 'label' => 'Sunlit Spaces'],
    ],
    'gallery'   => [
        ['img' => px(7271902, 700),  'title' => 'Wooden Side Stool', 'desc' => 'Handcrafted piece for a cozy corner'],
        ['img' => px(6952339, 700),  'title' => 'Shelf Still Life', 'desc' => 'Sculptural vases and a soft lamp'],
        ['img' => px(8762876, 700),  'title' => 'Stacked Linen', 'desc' => 'Warm-toned cushions, washed soft'],
        ['img' => px(10557274, 700), 'title' => 'Sculpted Oak Stool', 'desc' => 'Minimal seat, maximal charm'],
    ],
];

$PRODUCTS = [
    ['img' => px(7828350, 700), 'title' => 'Minimalist Ceramic Vase', 'price' => '46.99', 'tag' => 'Sleek & versatile'],
    ['img' => px(7538120, 700), 'title' => 'Handwoven Textured Cushion', 'price' => '26.49', 'tag' => 'Cozy & eco-friendly'],
    ['img' => px(4271612, 700), 'title' => 'Abstract Wall Print', 'price' => '25.49', 'tag' => 'Refined & personal'],
    ['img' => px(6633447, 700), 'title' => 'Wooden Accent Tray', 'price' => '64.90', 'tag' => 'Functional & stylish'],
];

/** "Why shoppn?" pillars. `icon` is the inner markup of a 24×24 stroked SVG. */
$WHY = [
    ['title' => 'Craftsmanship', 'body' => 'Every piece is crafted with sustainable, eco-friendly materials to protect the planet you love while adding lasting beauty to your home.',
     'icon'  => '<path d="M12 3 4 7v6c0 4 3.4 7 8 8 4.6-1 8-4 8-8V7z"/><path d="M9.5 12l1.8 1.8 3.6-3.6"/>'],
    ['title' => 'Curated for You', 'body' => 'Unique, handpicked decor that reflects your personal style, not mass-market trends, bringing authenticity to your space.',
     'icon'  => '<path d="M3 16c3-1 5-3 6.5-6"/><path d="M14 4c3 1.4 5 4 5 7a7 7 0 0 1-7 7H5"/><path d="M14 4c-2 .6-3.4 2-4 4"/>'],
    ['title' => 'Timeless Quality', 'body' => 'Expertly designed to last, each piece blends contemporary trends with timeless style, creating a home that stays beautiful.',
     'icon'  => '<path d="M6 3h12l3 6-9 12L3 9z"/><path d="M3 9h18"/><path d="M9.5 9 12 3l2.5 6"/>'],
    ['title' => 'Eco-Friendly', 'body' => 'Each carefully crafted piece is made with eco-friendly, sustainable materials to protect the planet for the long term.',
     'icon'  => '<path d="M12 21V11"/><path d="M12 11c0-4 2.6-7 7-7 0 4.2-2.8 7-7 7z"/><path d="M12 15c-3.4 0-5.6-2.2-5.6-5.6C9.8 9.4 12 11.6 12 15z"/>'],
];

$TESTIMONIALS = [
    ['img' => px(2530364, 500),  'name' => 'Sarah Malik', 'role' => 'Interior Designer',
     'quote' => 'Every piece feels intentional. These aren’t just products; they’re conversation starters that bring warmth and personality into my home.'],
    ['img' => px(36322503, 500), 'name' => 'Amina Yusuf', 'role' => 'Homeowner',
     'quote' => 'Beautifully made and sustainable. My living room finally feels like me — calm, warm and full of character.'],
    ['img' => px(18213112, 500), 'name' => 'Lena Novak', 'role' => 'Stylist',
     'quote' => 'The quality is exceptional and the details are perfect. shoppn has become my go-to for statement pieces.'],
];

$page_title      = 'Home';
$transparent_nav = true;
$full_bleed      = true;
require_once __DIR__ . '/layout/header.php';
?>

<!-- ══ HERO ══════════════════════════════════════════════ -->
<section class="hero" style="--hero:url('<?php echo $IMG['hero']; ?>')">
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <h1 class="hero-title">Create a Space<br>That’s Uniquely You</h1>
        <a class="btn-pill" href="<?php echo app_url('index.php#loved'); ?>">
            Shop Now <span class="arrow">→</span>
        </a>
    </div>
    <div class="masthead" aria-hidden="true">
        <span class="masthead-word">SHOPPN</span>
    </div>
</section>

<!-- ══ DREAM DECOR CAROUSEL ══════════════════════════════ -->
<section class="section section-cream" id="dream">
    <div class="container">
        <div class="section-head">
            <h2 class="serif">Dream Decor</h2>
            <div class="carousel-nav" data-carousel="dreamTrack">
                <button class="round-btn" data-dir="-1" aria-label="Previous">←</button>
                <button class="round-btn" data-dir="1" aria-label="Next">→</button>
            </div>
        </div>
    </div>
    <div class="carousel" id="dreamTrack" data-carousel-track>
        <?php foreach ($IMG['dream'] as $d): ?>
            <article class="dream-card reveal">
                <div class="dream-img" style="background-image:url('<?php echo $d['img']; ?>')"></div>
                <span class="dream-label"><?php echo htmlspecialchars($d['label']); ?></span>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<!-- ══ MOST LOVED PIECES ═════════════════════════════════ -->
<section class="section section-cream" id="loved">
    <div class="container loved-grid">
        <div class="loved-left">
            <h2 class="serif">Most Loved Pieces</h2>
            <div class="product-grid">
                <?php foreach ($PRODUCTS as $p): ?>
                    <article class="product-card reveal">
                        <div class="product-img" style="background-image:url('<?php echo $p['img']; ?>')"></div>
                        <h3 class="product-title"><?php echo htmlspecialchars($p['title']); ?></h3>
                        <div class="product-meta">
                            <span class="price">$<?php echo htmlspecialchars($p['price']); ?></span>
                            <span class="tag"><?php echo htmlspecialchars($p['tag']); ?></span>
                            <span class="mini-bowtie"><?php echo bowtie_svg('mini'); ?></span>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
        <aside class="loved-feature reveal">
            <p class="feature-intro">Discover why shoppn is your go-to for sustainable, unique home decor. Explore eco-friendly craftsmanship.</p>
            <div class="feature-img" style="background-image:url('<?php echo $IMG['feature']; ?>')"></div>
            <h3 class="feature-caption">Sculptural Wooden Lamp</h3>
        </aside>
    </div>
</section>

<!-- ══ ARTISANAL SPLIT ═══════════════════════════════════ -->
<section class="section section-cream" id="artisanal">
    <div class="container">
        <div class="artisan-grid reveal">
            <div class="artisan-panel">
                <h2 class="serif">Artisanal in Every Detail</h2>
                <p>Home decor you can trust — crafted with purpose to bring warmth,
                   character and authenticity to your space.</p>
                <div class="artisan-inset">
                    <div class="artisan-inset-img" style="background-image:url('<?php echo $IMG['maker']; ?>')"></div>
                    <a class="link-underline" href="<?php echo app_url('index.php#gallery'); ?>">Explore <span class="arrow">→</span></a>
                </div>
            </div>
            <div class="artisan-photo" style="background-image:url('<?php echo $IMG['artisan']; ?>')"></div>
        </div>
    </div>
</section>

<!-- ══ LIFESTYLE BAND ════════════════════════════════════ -->
<section class="lifestyle reveal" style="background-image:url('<?php echo $IMG['lifestyle']; ?>')"></section>

<!-- ══ SUSTAINABLE SPLIT ═════════════════════════════════ -->
<section class="section section-white" id="sustainable">
    <div class="container sustain-grid">
        <div class="sustain-img reveal" style="background-image:url('<?php echo $IMG['bedroom']; ?>')"></div>
        <div class="sustain-text reveal">
            <h2 class="sustain-title serif">Sustainable Decor That Brings Beauty To Your Home</h2>
            <a class="btn-outline" href="<?php echo app_url('index.php#gallery'); ?>">Sustainability Promise <span class="arrow">→</span></a>
            <div class="sustain-foot">
                <span>Natural Materials</span>
                <span>Sustainable Impact</span>
            </div>
        </div>
    </div>
</section>

<!-- ══ BRAND BAND + WHY ══════════════════════════════════ -->
<section class="brand-band reveal">
    <div class="container brand-band-grid">
        <p class="brand-statement serif">
            At shoppn, every detail counts.
            <span class="alt">Our handcrafted pieces are unique and
            customizable to fit your vision perfectly.</span>
        </p>
        <div class="brand-band-aside">
            <p class="brand-band-label">We Craft Decor That Speaks to You</p>
            <a class="btn-square" href="<?php echo app_url('views/register.php'); ?>">
                Start Your Journey <span class="arrow">→</span>
            </a>
        </div>
    </div>
</section>

<section class="section section-white" id="why">
    <div class="container why-grid">
        <div class="why-copy reveal">
            <h2 class="serif">Why shoppn?</h2>
            <p>Discover why shoppn is your go-to for sustainable, unique home decor.
               Explore eco-friendly craftsmanship, curated designs and timeless quality
               that transform your space with meaning and style.</p>
            <a class="link-underline" href="<?php echo app_url('index.php#sustainable'); ?>">Read more</a>
        </div>
        <div class="why-panel reveal">
            <?php foreach ($WHY as $w): ?>
                <article class="why-item">
                    <svg class="why-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <?php echo $w['icon']; ?>
                    </svg>
                    <h3><?php echo htmlspecialchars($w['title']); ?></h3>
                    <p><?php echo htmlspecialchars($w['body']); ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ══ TESTIMONIALS ══════════════════════════════════════ -->
<section class="section section-cream testimonials" id="testimonials">
    <div class="container">
        <div class="section-head three">
            <div class="trust">Loved &amp; Trusted by<br><strong>100+ Homeowners</strong></div>
            <h2 class="serif center">Hear From Our Happy Customers</h2>
            <div class="carousel-nav" data-carousel="tstTrack">
                <button class="round-btn" data-dir="-1" aria-label="Previous">←</button>
                <button class="round-btn" data-dir="1" aria-label="Next">→</button>
            </div>
        </div>
    </div>
    <div class="carousel testimonial-track" id="tstTrack" data-carousel-track>
        <?php foreach ($TESTIMONIALS as $t): ?>
            <article class="testimonial reveal">
                <div class="t-photo" style="background-image:url('<?php echo $t['img']; ?>')"></div>
                <div class="t-body">
                    <p class="t-quote">“<?php echo htmlspecialchars($t['quote']); ?>”</p>
                    <p class="t-name"><?php echo htmlspecialchars($t['name']); ?>, <span><?php echo htmlspecialchars($t['role']); ?></span></p>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<!-- ══ INSPIRATION GALLERY ═══════════════════════════════ -->
<section class="section section-white gallery-section" id="gallery">
    <div class="container gallery-head">
        <p class="gallery-lead serif">Explore how our decor transforms living rooms, bedrooms, and cozy corners. Mix and match to create a home that tells your story.</p>
        <a class="link-underline" href="<?php echo app_url('index.php#dream'); ?>">Explore the Gallery <span class="arrow">→</span></a>
    </div>
    <div class="gallery-wrap">
        <button class="round-btn edge left" data-carousel="galTrack" data-dir="-1" aria-label="Previous">←</button>
        <div class="carousel gallery-track" id="galTrack" data-carousel-track>
            <?php foreach ($IMG['gallery'] as $g): ?>
                <article class="gallery-card reveal">
                    <div class="gallery-img" style="background-image:url('<?php echo $g['img']; ?>')"></div>
                    <div class="gallery-info">
                        <h4><?php echo htmlspecialchars($g['title']); ?></h4>
                        <p><?php echo htmlspecialchars($g['desc']); ?></p>
                    </div>
                    <span class="gallery-arrow">↗</span>
                </article>
            <?php endforeach; ?>
        </div>
        <button class="round-btn edge right" data-carousel="galTrack" data-dir="1" aria-label="Next">→</button>
    </div>
</section>

<?php require_once __DIR__ . '/layout/footer.php'; ?>
