# Image assets

| Filename | Use | Notes |
|---|---|---|
| `headshot.jpg` | About section portrait (circular crop) | Resized/compressed from the original `profilePicture.png` (2048×2048 → 1000×1000, 4.7MB → ~115KB) |
| `og-banner.jpg` | Social share preview for Facebook/LinkedIn (`og:image`) | From the wide "coverLinkedin" banner (1584×396) |
| `x-banner.jpg` | Social share preview for X (`twitter:image`) | From the "coverX" banner (1500×500) |
| `favicon.png` | Browser tab icon | Cropped from the dot-swirl portion of the wide banner |

The original uploaded PNGs (`coverLinkedin.png`, `coverX.png`, `profilePicture.png`) were large (up to 4.7MB) and unreferenced by the page, so they were removed from the working tree after generating the optimized versions above — they're still recoverable from git history if needed.
