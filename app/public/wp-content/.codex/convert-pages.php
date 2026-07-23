<?php
declare(strict_types=1);

$db = mysqli_init();
if (!mysqli_real_connect($db, '127.0.0.1', 'root', 'root', 'local', 10047)) {
    fwrite(STDERR, "db connect failed: " . mysqli_connect_error() . PHP_EOL);
    exit(1);
}
mysqli_set_charset($db, 'utf8mb4');

$backupDir = 'C:/Users/shanm/Local Sites/ceramic-pro-new/app/public/wp-content/wp-block-backups/' . date('Ymd-His');
if (!is_dir($backupDir) && !mkdir($backupDir, 0777, true) && !is_dir($backupDir)) {
    fwrite(STDERR, "backup dir create failed\n");
    exit(1);
}

function q(mysqli $db, string $sql): mysqli_result|bool {
    $res = mysqli_query($db, $sql);
    if ($res === false) {
        throw new RuntimeException(mysqli_error($db) . ' | ' . $sql);
    }
    return $res;
}

function esc(mysqli $db, string $value): string {
    return mysqli_real_escape_string($db, $value);
}

function innerHtml(DOMNode $node): string {
    $html = '';
    foreach ($node->childNodes as $child) {
        $html .= $child->ownerDocument->saveHTML($child);
    }
    return $html;
}

function attrs(array $data): string {
    return $data ? ' ' . json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : '';
}

function shortcodeBlock(string $shortcode): string {
    return "<!-- wp:shortcode -->\n" . trim($shortcode) . "\n<!-- /wp:shortcode -->";
}

function htmlBlock(string $html): string {
    return "<!-- wp:html -->\n" . trim($html) . "\n<!-- /wp:html -->";
}

function convertChildren(DOMElement $el): string {
    $out = [];
    foreach ($el->childNodes as $child) {
        if ($child instanceof DOMText) {
            $text = trim($child->wholeText);
            if ($text !== '') {
                if (preg_match('/^\[[A-Za-z0-9_:-]+(?:\s[^\]]*)?\]$/', $text)) {
                    $out[] = shortcodeBlock($text);
                } else {
                    $out[] = "<!-- wp:paragraph -->\n<p>" . htmlspecialchars($text, ENT_QUOTES) . "</p>\n<!-- /wp:paragraph -->";
                }
            }
            continue;
        }
        if (!($child instanceof DOMElement)) {
            continue;
        }
        $tag = strtolower($child->tagName);
        $class = trim($child->getAttribute('class'));
        $id = trim($child->getAttribute('id'));

        if (in_array($tag, ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'], true)) {
            $level = (int) substr($tag, 1);
            $extra = [];
            if ($class !== '') {
                $extra['className'] = $class;
            }
            $out[] = "<!-- wp:heading" . attrs(array_merge(['level' => $level], $extra)) . " -->\n<{$tag}>" . innerHtml($child) . "</{$tag}>\n<!-- /wp:heading -->";
            continue;
        }

        if ($tag === 'p') {
            $inner = trim(innerHtml($child));
            if (preg_match('/^\[[A-Za-z0-9_:-]+(?:\s[^\]]*)?\]$/', $inner)) {
                $out[] = shortcodeBlock($inner);
            } else {
                $out[] = "<!-- wp:paragraph" . attrs($class !== '' ? ['className' => $class] : []) . " -->\n<p>" . innerHtml($child) . "</p>\n<!-- /wp:paragraph -->";
            }
            continue;
        }

        if ($tag === 'img') {
            $out[] = "<!-- wp:image" . attrs(['url' => $child->getAttribute('src'), 'alt' => $child->getAttribute('alt'), 'sizeSlug' => 'full', 'linkDestination' => 'none']) . " -->\n<figure class=\"wp-block-image\"><img src=\"" . htmlspecialchars($child->getAttribute('src'), ENT_QUOTES) . "\" alt=\"" . htmlspecialchars($child->getAttribute('alt'), ENT_QUOTES) . "\" /></figure>\n<!-- /wp:image -->";
            continue;
        }

        if ($tag === 'ul' || $tag === 'ol') {
            $liHtml = '';
            foreach ($child->childNodes as $li) {
                if ($li instanceof DOMElement && strtolower($li->tagName) === 'li') {
                    $liHtml .= '<li>' . innerHtml($li) . '</li>';
                }
            }
            $out[] = "<!-- wp:list" . attrs($tag === 'ol' ? ['ordered' => true] : []) . " -->\n<{$tag}>" . $liHtml . "</{$tag}>\n<!-- /wp:list -->";
            continue;
        }

        if ($tag === 'a' && preg_match('/\b(btn|button|read|contact)\b/i', $class)) {
            $text = trim(strip_tags(innerHtml($child)));
            $out[] = "<!-- wp:buttons -->\n<div class=\"wp-block-buttons\"><!-- wp:button" . attrs(['url' => $child->getAttribute('href')]) . " -->\n<div class=\"wp-block-button\"><a class=\"wp-block-button__link wp-element-button\" href=\"" . htmlspecialchars($child->getAttribute('href'), ENT_QUOTES) . "\">" . htmlspecialchars($text, ENT_QUOTES) . "</a></div>\n<!-- /wp:button --></div>\n<!-- /wp:buttons -->";
            continue;
        }

        if (in_array($tag, ['section', 'div', 'article', 'header', 'footer', 'nav', 'figure', 'main', 'aside'], true)) {
            $blockClass = trim($class);
            $blockAttrs = [];
            if ($blockClass !== '') {
                $blockAttrs['className'] = $blockClass;
            }
            if ($id !== '') {
                $blockAttrs['anchor'] = $id;
            }
            $inner = convertChildren($child);
            if (trim($inner) === '') {
                $inner = htmlBlock($child->ownerDocument->saveHTML($child));
            }
            $out[] = "<!-- wp:group" . attrs($blockAttrs) . " -->\n<div class=\"wp-block-group" . ($blockClass !== '' ? ' ' . htmlspecialchars($blockClass, ENT_QUOTES) : '') . "\">\n" . $inner . "\n</div>\n<!-- /wp:group -->";
            continue;
        }

        if (in_array($tag, ['video', 'iframe'], true)) {
            $out[] = htmlBlock($child->ownerDocument->saveHTML($child));
            continue;
        }

        $out[] = htmlBlock($child->ownerDocument->saveHTML($child));
    }
    return implode("\n\n", array_filter($out, static fn($x) => trim($x) !== ''));
}

function convertToBlocks(string $html): string {
    libxml_use_internal_errors(true);
    $dom = new DOMDocument('1.0', 'UTF-8');
    $dom->loadHTML('<?xml encoding="utf-8" ?><div id="root">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    $root = $dom->getElementById('root');
    if (!$root) {
        return htmlBlock($html);
    }
    return convertChildren($root);
}

$res = q($db, "SELECT ID, post_name, post_title, post_content FROM wp_posts WHERE post_type='page' AND post_status='publish' ORDER BY ID");
$updates = [];
while ($page = mysqli_fetch_assoc($res)) {
    if (str_contains($page['post_content'], '<!-- wp:')) {
        continue;
    }
    $blocks = convertToBlocks($page['post_content']);
    $blockCount = substr_count($blocks, '<!-- wp:');
    if ($blockCount === 0) {
        continue;
    }
    $safeSlug = preg_replace('/[^A-Za-z0-9._-]+/', '-', $page['post_name']);
    file_put_contents($backupDir . '/' . $page['ID'] . '-' . $safeSlug . '.html', $page['post_content']);
    $content = esc($db, $blocks);
    q($db, "UPDATE wp_posts SET post_content='" . $content . "', post_content_filtered='', post_modified=NOW(), post_modified_gmt=UTC_TIMESTAMP() WHERE ID=" . (int) $page['ID']);
    $updates[] = [
        'id' => (int) $page['ID'],
        'slug' => $page['post_name'],
        'title' => $page['post_title'],
        'blocks' => $blockCount,
        'len' => strlen($blocks),
    ];
}

echo json_encode([
    'backupDir' => $backupDir,
    'updated' => $updates,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), PHP_EOL;
