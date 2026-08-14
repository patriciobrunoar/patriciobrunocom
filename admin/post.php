<?php
declare(strict_types=1);

/**
 * Add a LinkedIn post to the site's Perspective section.
 * Protect this directory with DreamHost's "password protect a directory"
 * panel feature (HTTP Basic Auth) — do not rely on this script for auth.
 */

$dataFile = __DIR__ . '/../assets/data/linkedin-posts.json';
$message = '';
$error = '';

function fetch_og_description(string $url): ?string
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 8,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; LinkPreviewBot/1.0; +https://patriciobruno.com)',
        CURLOPT_HTTPHEADER => ['Accept: text/html'],
    ]);
    $html = curl_exec($ch);
    curl_close($ch);
    if (!$html) {
        return null;
    }

    foreach (['og:description', 'description'] as $prop) {
        $propPattern = preg_quote($prop, '/');
        if (preg_match('/<meta[^>]+(?:property|name)=["\']' . $propPattern . '["\'][^>]+content=["\']([^"\']+)["\']/i', $html, $m)
            || preg_match('/<meta[^>]+content=["\']([^"\']+)["\'][^>]+(?:property|name)=["\']' . $propPattern . '["\']/i', $html, $m)
        ) {
            return html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5);
        }
    }
    return null;
}

function load_posts(string $dataFile): array
{
    if (!file_exists($dataFile)) {
        return [];
    }
    $decoded = json_decode((string) file_get_contents($dataFile), true);
    return is_array($decoded) ? $decoded : [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $url = trim($_POST['url'] ?? '');
    $excerpt = trim($_POST['excerpt'] ?? '');
    $date = trim($_POST['date'] ?? '') ?: date('Y-m-d');

    if (!preg_match('#^https://([a-z]+\.)?linkedin\.com/#i', $url)) {
        $error = "That doesn't look like a linkedin.com URL.";
    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        $error = 'Date must be in YYYY-MM-DD format.';
    } else {
        if ($excerpt === '') {
            $fetched = fetch_og_description($url);
            if ($fetched) {
                $excerpt = mb_strimwidth($fetched, 0, 400, '…');
            } else {
                $error = "Couldn't auto-fetch text from that link (LinkedIn may be blocking it, or the post isn't fully public). Type a short excerpt below and save again.";
            }
        }

        if ($error === '' && $excerpt !== '') {
            $posts = load_posts($dataFile);
            $posts = array_values(array_filter($posts, fn($p) => ($p['url'] ?? '') !== $url));
            array_unshift($posts, ['url' => $url, 'excerpt' => $excerpt, 'date' => $date]);
            $posts = array_slice($posts, 0, 2);

            if (!is_dir(dirname($dataFile))) {
                mkdir(dirname($dataFile), 0755, true);
            }
            file_put_contents(
                $dataFile,
                json_encode($posts, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n"
            );

            $message = 'Saved. The site now shows this as one of your latest 2 posts.';
        }
    }
}

$currentPosts = load_posts($dataFile);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<meta name="robots" content="noindex, nofollow" />
<title>Add LinkedIn Post — Patricio Bruno</title>
<style>
  body { font-family: -apple-system, "Inter", sans-serif; max-width: 640px; margin: 3rem auto; padding: 0 1.5rem; background: #0e3234; color: #eef4f2; }
  h1 { font-size: 1.4rem; }
  label { display: block; margin-top: 1.2rem; font-weight: 600; font-size: .9rem; }
  input[type=text], input[type=date], textarea {
    width: 100%; padding: .6rem; margin-top: .35rem; border-radius: 8px;
    border: 1px solid rgba(63,205,214,0.3); background: #123a3c; color: #eef4f2;
    font-size: .95rem; box-sizing: border-box; font-family: inherit;
  }
  textarea { min-height: 90px; }
  button {
    margin-top: 1.5rem; padding: .75rem 1.5rem; border-radius: 999px; border: none;
    background: #3fcdd6; color: #06201f; font-weight: 700; cursor: pointer; font-size: .95rem;
  }
  .msg { margin-top: 1rem; padding: .75rem 1rem; border-radius: 8px; font-size: .9rem; }
  .msg.ok { background: #1f4d3a; }
  .msg.err { background: #4d1f1f; }
  .current { margin-top: 2.5rem; padding-top: 1.5rem; border-top: 1px solid rgba(63,205,214,0.2); }
  .current article { margin-bottom: 1rem; font-size: .9rem; color: #a9c2bf; }
  .current a { color: #85e0e6; word-break: break-all; }
  small { color: #7d9694; }
</style>
</head>
<body>
  <h1>Add a LinkedIn post</h1>
  <p><small>Paste the post link and save. Leave the excerpt blank to auto-fetch it from the link.</small></p>

  <?php if ($message !== ''): ?><div class="msg ok"><?= htmlspecialchars($message) ?></div><?php endif; ?>
  <?php if ($error !== ''): ?><div class="msg err"><?= htmlspecialchars($error) ?></div><?php endif; ?>

  <form method="post">
    <label for="url">LinkedIn post URL</label>
    <input type="text" id="url" name="url" placeholder="https://www.linkedin.com/posts/..." value="<?= htmlspecialchars($_POST['url'] ?? '') ?>" required />

    <label for="excerpt">Excerpt (optional — auto-fetched if left blank)</label>
    <textarea id="excerpt" name="excerpt"><?= htmlspecialchars($_POST['excerpt'] ?? '') ?></textarea>

    <label for="date">Date</label>
    <input type="date" id="date" name="date" value="<?= htmlspecialchars($_POST['date'] ?? date('Y-m-d')) ?>" />

    <button type="submit">Save</button>
  </form>

  <div class="current">
    <strong>Currently live on the site:</strong>
    <?php if (!$currentPosts): ?>
      <p><small>No posts yet.</small></p>
    <?php else: foreach ($currentPosts as $p): ?>
      <article>
        <div><?= htmlspecialchars($p['date'] ?? '') ?></div>
        <div><?= nl2br(htmlspecialchars($p['excerpt'] ?? '')) ?></div>
        <a href="<?= htmlspecialchars($p['url'] ?? '#') ?>" target="_blank" rel="noopener"><?= htmlspecialchars($p['url'] ?? '') ?></a>
      </article>
    <?php endforeach; endif; ?>
  </div>
</body>
</html>
