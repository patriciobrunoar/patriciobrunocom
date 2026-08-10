// Patricio Bruno — site interactions: nav state, mobile menu, scroll reveals, hero particles.
(function () {
  "use strict";

  var reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

  /* Nav scroll state */
  var nav = document.querySelector(".nav");
  var onScroll = function () {
    if (window.scrollY > 12) nav.classList.add("scrolled");
    else nav.classList.remove("scrolled");
  };
  window.addEventListener("scroll", onScroll, { passive: true });
  onScroll();

  /* Mobile menu */
  var toggle = document.querySelector(".nav-toggle");
  var menu = document.querySelector(".mobile-menu");
  if (toggle && menu) {
    toggle.addEventListener("click", function () {
      var open = menu.classList.toggle("open");
      toggle.setAttribute("aria-expanded", open ? "true" : "false");
    });
    menu.querySelectorAll("a").forEach(function (a) {
      a.addEventListener("click", function () {
        menu.classList.remove("open");
        toggle.setAttribute("aria-expanded", "false");
      });
    });
  }

  /* Footer year */
  var yearEl = document.getElementById("year");
  if (yearEl) yearEl.textContent = new Date().getFullYear();

  /* Scroll reveal */
  if ("IntersectionObserver" in window && !reduceMotion) {
    var io = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            entry.target.classList.add("in-view");
            io.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.15, rootMargin: "0px 0px -40px 0px" }
    );
    document.querySelectorAll(".reveal").forEach(function (el) {
      io.observe(el);
    });
  } else {
    document.querySelectorAll(".reveal").forEach(function (el) {
      el.classList.add("in-view");
    });
  }

  /* Hero dot-swirl particle field — echoes the brand banner motif */
  var canvas = document.getElementById("hero-canvas");
  if (canvas && !reduceMotion) {
    var ctx = canvas.getContext("2d");
    var dpr = Math.min(window.devicePixelRatio || 1, 2);
    var particles = [];
    var W, H;

    function resize() {
      W = canvas.clientWidth;
      H = canvas.clientHeight;
      canvas.width = W * dpr;
      canvas.height = H * dpr;
      ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
      buildParticles();
    }

    function buildParticles() {
      var count = Math.round((W * H) / 9000);
      particles = [];
      for (var i = 0; i < count; i++) {
        var angle = Math.random() * Math.PI * 2;
        var swirl = Math.pow(Math.random(), 1.6);
        var cx = W * 0.72;
        var cy = H * 0.42;
        var radius = swirl * Math.max(W, H) * 0.62;
        particles.push({
          baseX: cx + Math.cos(angle) * radius,
          baseY: cy + Math.sin(angle) * radius * 0.72,
          r: Math.random() * 1.6 + 0.4,
          speed: Math.random() * 0.4 + 0.15,
          offset: Math.random() * Math.PI * 2,
          alpha: Math.random() * 0.5 + 0.25
        });
      }
    }

    var t = 0;
    function draw() {
      t += 0.006;
      ctx.clearRect(0, 0, W, H);
      for (var i = 0; i < particles.length; i++) {
        var p = particles[i];
        var dx = Math.sin(t * p.speed + p.offset) * 6;
        var dy = Math.cos(t * p.speed + p.offset) * 6;
        ctx.beginPath();
        ctx.arc(p.baseX + dx, p.baseY + dy, p.r, 0, Math.PI * 2);
        ctx.fillStyle = "rgba(79, 216, 224, " + p.alpha + ")";
        ctx.fill();
      }
      requestAnimationFrame(draw);
    }

    window.addEventListener("resize", resize, { passive: true });
    resize();
    requestAnimationFrame(draw);
  } else if (canvas) {
    canvas.style.display = "none";
  }
})();
