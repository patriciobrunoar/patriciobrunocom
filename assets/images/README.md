# Expected image files

Drop these into `assets/images/` with these exact filenames and the site will pick them up automatically — no code changes needed.

| Filename | Use | Source | Recommended size |
|---|---|---|---|
| `headshot.jpg` | About section portrait (circular crop) | Your profile photo | Square, 800×800px+ |
| `og-banner.jpg` | Social share preview (Open Graph / LinkedIn / X link previews) | The wide dark-teal banner ("Patricio Bruno / Fractional Marketing Director & AI Strategist") | 1200×630px (crop the 1536×400 wide banner to this ratio, or send as-is) |
| `favicon.png` | Browser tab icon | Optional — a square crop/mark from the banner dot swirl | 512×512px |

**Note on the two banner images:** the hero section text (headline, sub-headline) is written directly in the page as real HTML — not baked into an image — so it stays editable, accessible, and doesn't duplicate the "Patricio Bruno" text already in your banner art. The hero *background* instead uses a CSS-recreated dot-swirl in the same cyan-on-dark-teal style as your banners. Your actual banner images are used as the `og-banner.jpg` social preview image instead, which is exactly what they're sized for (1536×400 / 1500×510).

If you'd rather the literal banner image appear as the hero background too, drop it in as `hero-banner.jpg` and ping me — one line in `index.html` switches the hero to use it.
