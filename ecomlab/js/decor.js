/* ============================================================
   decor.js — front-end interactions for the shoppn storefront.
   All motion is GPU-friendly (transform/opacity) and respects
   prefers-reduced-motion.
   ============================================================ */
(function () {
    'use strict';

    var reduce = window.matchMedia &&
        window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /* ── QA helper: ?section=ID jumps to a section with reveals shown ── */
    try {
        var qs = new URLSearchParams(window.location.search);
        var sec = qs.get('section');
        var scrollY = qs.get('scroll');
        if (sec || scrollY !== null) {
            document.documentElement.style.scrollBehavior = 'auto';
            document.querySelectorAll('.reveal').forEach(function (e) { e.classList.add('in'); });
        }
        if (sec) {
            var target = document.getElementById(sec);
            if (target) window.scrollTo(0, target.getBoundingClientRect().top + window.pageYOffset - 80);
        }
        if (scrollY !== null) window.scrollTo(0, parseInt(scrollY, 10) || 0);
    } catch (e) {}

    /* ── Sticky nav: frost + darken once scrolled past the hero top ── */
    var nav = document.getElementById('siteNav');
    if (nav && nav.classList.contains('nav-over-hero')) {
        var onScroll = function () {
            if (window.scrollY > 60) nav.classList.add('scrolled');
            else nav.classList.remove('scrolled');
        };
        onScroll();
        window.addEventListener('scroll', onScroll, { passive: true });
    }

    /* ── Reveal-on-scroll via IntersectionObserver ── */
    var reveals = document.querySelectorAll('.reveal');
    if (reduce || !('IntersectionObserver' in window)) {
        reveals.forEach(function (el) { el.classList.add('in'); });
    } else {
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (e) {
                if (e.isIntersecting) {
                    e.target.classList.add('in');
                    io.unobserve(e.target);
                }
            });
        }, { threshold: 0.14, rootMargin: '0px 0px -8% 0px' });
        reveals.forEach(function (el) { io.observe(el); });
    }

    /* ── Carousels: arrow buttons scroll the matching track ── */
    function trackFor(id) {
        return document.querySelector('[data-carousel-track]#' + id) ||
               document.getElementById(id);
    }
    function scrollAmount(track) {
        var first = track.querySelector(':scope > *');
        if (!first) return track.clientWidth * 0.8;
        var style = getComputedStyle(track);
        var gap = parseFloat(style.columnGap || style.gap || '22') || 22;
        return first.getBoundingClientRect().width + gap;
    }
    document.querySelectorAll('[data-carousel]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id = btn.getAttribute('data-carousel');
            var dir = parseInt(btn.getAttribute('data-dir'), 10) || 1;
            var track = trackFor(id);
            if (track) track.scrollBy({ left: dir * scrollAmount(track), behavior: reduce ? 'auto' : 'smooth' });
        });
    });
    // Grouped arrow controls: <div class="carousel-nav" data-carousel="dream">
    document.querySelectorAll('.carousel-nav[data-carousel]').forEach(function (group) {
        var id = group.getAttribute('data-carousel');
        var track = trackFor(id);
        group.querySelectorAll('[data-dir]').forEach(function (b) {
            b.addEventListener('click', function () {
                var dir = parseInt(b.getAttribute('data-dir'), 10) || 1;
                if (track) track.scrollBy({ left: dir * scrollAmount(track), behavior: reduce ? 'auto' : 'smooth' });
            });
        });
    });

    /* ── Subtle masthead parallax on scroll (home hero) ──
       Wait for the CSS intro animation to finish first — CSS animations
       override inline transforms, so we take over only once it ends. */
    var masthead = document.querySelector('.masthead-word');
    if (masthead && !reduce) {
        var ready = false;
        masthead.addEventListener('animationend', function () {
            ready = true;
            masthead.style.animation = 'none';
            masthead.style.transform = 'translateY(22%)';
        });
        var ticking = false;
        window.addEventListener('scroll', function () {
            if (!ready || ticking) return;
            ticking = true;
            requestAnimationFrame(function () {
                var y = Math.min(window.scrollY, 600);
                masthead.style.transform = 'translateY(calc(22% + ' + (y * 0.12) + 'px))';
                ticking = false;
            });
        }, { passive: true });
    }
})();
