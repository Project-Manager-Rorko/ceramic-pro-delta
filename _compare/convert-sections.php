<?php
/**
 * Convert raw HTML page content into separate top-level Gutenberg section blocks.
 * Preserves classes/markup for design parity. Nested divs become groups; text becomes
 * heading/paragraph/image blocks when simple; complex fragments stay as freeform HTML.
 */

function esc_json_attr($s) {
    return str_replace(['\\', '"'], ['\\\\', '\\"'], $s);
}

function normalize_space($s) {
    return trim(preg_replace('/\s+/u', ' ', $s));
}

function convert_simple_inline($html) {
    // leave as-is for freeform if too complex
    return $html;
}

function has_only_text_and_inline($html) {
    $tmp = preg_replace('/<(a|strong|em|b|i|br|span|img)(\s[^>]*)?>/i', '', $html);
    $tmp = preg_replace('/<\/(a|strong|em|b|i|span)>/i', '', $tmp);
    $tmp = preg_replace('/<br\s*\/?>/i', '', $tmp);
    return strip_tags($tmp) === preg_replace('/\s+/', '', strip_tags($html)) || !preg_match('/<\s*(div|section|ul|ol|table|video|form|iframe|h[1-6]|p)\b/i', $html);
}

function convert_children($html, $depth = 0) {
    $html = trim($html);
    if ($html === '') return '';

    $out = '';
    $offset = 0;
    $len = strlen($html);

    // Parse top-level siblings in this HTML fragment
    while ($offset < $len) {
        // skip whitespace
        if (preg_match('/\G\s+/', $html, $m, 0, $offset)) {
            $offset += strlen($m[0]);
            continue;
        }

        // comment
        if (preg_match('/\G<!--.*?-->/s', $html, $m, 0, $offset)) {
            $offset += strlen($m[0]);
            continue;
        }

        // self-closing-ish tags: video, img, source, br, hr, input
        if (preg_match('/\G<(video|img|source|br|hr|input|meta|link)\b([^>]*)>/i', $html, $m, 0, $offset)) {
            // For video, capture full element including closing if present
            $tag = strtolower($m[1]);
            if ($tag === 'video') {
                if (preg_match('/\G<video\b[^>]*>.*?<\/video>/is', $html, $vm, 0, $offset)) {
                    $out .= "\n<!-- wp:html -->\n" . $vm[0] . "\n<!-- /wp:html -->\n";
                    $offset += strlen($vm[0]);
                    continue;
                }
            }
            if ($tag === 'img') {
                $attrs = $m[2];
                $src = '';
                $alt = '';
                $class = '';
                if (preg_match('/\bsrc=["\']([^"\']+)["\']/', $attrs, $sm)) $src = $sm[1];
                if (preg_match('/\balt=["\']([^"\']*)["\']/', $attrs, $am)) $alt = $am[1];
                if (preg_match('/\bclass=["\']([^"\']*)["\']/', $attrs, $cm)) $class = $cm[1];
                if ($src) {
                    $json = '{"url":"' . esc_json_attr($src) . '","alt":"' . esc_json_attr($alt) . '"';
                    if ($class !== '') $json = rtrim($json, '}') . ',"className":"' . esc_json_attr($class) . '"}';
                    $clsAttr = $class !== '' ? ' class="' . htmlspecialchars($class, ENT_QUOTES) . '"' : '';
                    $out .= "\n<!-- wp:image " . $json . " -->\n<figure class=\"wp-block-image" . ($class ? ' ' . htmlspecialchars($class, ENT_QUOTES) : '') . "\"><img src=\"" . htmlspecialchars($src, ENT_QUOTES) . "\" alt=\"" . htmlspecialchars($alt, ENT_QUOTES) . "\"/></figure>\n<!-- /wp:image -->\n";
                } else {
                    $out .= "\n<!-- wp:html -->\n" . $m[0] . "\n<!-- /wp:html -->\n";
                }
                $offset += strlen($m[0]);
                continue;
            }
            // other self-closing
            $out .= "\n<!-- wp:html -->\n" . $m[0] . "\n<!-- /wp:html -->\n";
            $offset += strlen($m[0]);
            continue;
        }

        // opening tag with possible children
        if (preg_match('/\G<([a-zA-Z0-9]+)(\s[^>]*)?>/s', $html, $m, 0, $offset)) {
            $tag = strtolower($m[1]);
            $fullOpen = $m[0];
            $attrs = isset($m[2]) ? $m[2] : '';
            $start = $offset;
            $offset += strlen($fullOpen);

            // void tags already handled above mostly
            if (in_array($tag, ['br','hr','img','source','input','meta','link'], true)) {
                continue;
            }

            // find matching close with nesting
            $level = 1;
            $innerStart = $offset;
            $inner = '';
            $endPos = $offset;
            while ($offset < $len && $level > 0) {
                if (preg_match('/\G<\/' . preg_quote($tag, '/') . '\s*>/i', $html, $cm, 0, $offset)) {
                    $level--;
                    if ($level === 0) {
                        $inner = substr($html, $innerStart, $offset - $innerStart);
                        $offset += strlen($cm[0]);
                        $endPos = $offset;
                        break;
                    }
                    $offset += strlen($cm[0]);
                    continue;
                }
                if (preg_match('/\G<' . preg_quote($tag, '/') . '\b[^>]*>/i', $html, $om, 0, $offset)) {
                    // same tag open - increase only if not self-closing
                    if (!preg_match('/\/\s*>$/', $om[0])) $level++;
                    $offset += strlen($om[0]);
                    continue;
                }
                // skip other tags / text one char or next tag
                if (preg_match('/\G[^<]+/', $html, $tm, 0, $offset)) {
                    $offset += strlen($tm[0]);
                    continue;
                }
                if (preg_match('/\G<[^>]+>/', $html, $tm, 0, $offset)) {
                    $offset += strlen($tm[0]);
                    continue;
                }
                $offset++;
            }

            $class = '';
            if (preg_match('/\bclass=["\']([^"\']*)["\']/', $attrs, $cm)) {
                $class = trim(preg_replace('/\s+/', ' ', $cm[1]));
            }
            $id = '';
            if (preg_match('/\bid=["\']([^"\']*)["\']/', $attrs, $im)) $id = $im[1];

            // headings
            if (preg_match('/^h([1-6])$/', $tag, $hm)) {
                $levelH = (int)$hm[1];
                $json = $levelH === 2 ? '' : ' {"level":' . $levelH . '}';
                if ($class !== '') {
                    $jsonObj = ['level' => $levelH];
                    if ($levelH === 2) unset($jsonObj); 
                    $parts = [];
                    if ($levelH !== 2) $parts[] = '"level":' . $levelH;
                    $parts[] = '"className":"' . esc_json_attr($class) . '"';
                    $json = ' {' . implode(',', $parts) . '}';
                }
                $clsAttr = $class !== '' ? ' class="' . htmlspecialchars($class, ENT_QUOTES) . '"' : '';
                $out .= "\n<!-- wp:heading" . $json . " -->\n<" . $tag . $clsAttr . ">" . $inner . "</" . $tag . ">\n<!-- /wp:heading -->\n";
                continue;
            }

            // paragraph
            if ($tag === 'p') {
                $json = $class !== '' ? ' {"className":"' . esc_json_attr($class) . '"}' : '';
                $clsAttr = $class !== '' ? ' class="' . htmlspecialchars($class, ENT_QUOTES) . '"' : '';
                $out .= "\n<!-- wp:paragraph" . $json . " -->\n<p" . $clsAttr . ">" . $inner . "</p>\n<!-- /wp:paragraph -->\n";
                continue;
            }

            // lists
            if ($tag === 'ul' || $tag === 'ol') {
                $out .= "\n<!-- wp:html -->\n<" . $tag . $attrs . ">" . $inner . "</" . $tag . ">\n<!-- /wp:html -->\n";
                continue;
            }

            // anchor-only wrappers sometimes
            if ($tag === 'a') {
                $out .= "\n<!-- wp:html -->\n<a" . $attrs . ">" . $inner . "</a>\n<!-- /wp:html -->\n";
                continue;
            }

            // div / section / article / span-as-wrapper with class -> group if div/section
            if (in_array($tag, ['div', 'section', 'article', 'header', 'footer', 'main', 'aside'], true)) {
                // If no class and only simple content, still group
                $json = $class !== '' ? ' {"className":"' . esc_json_attr($class) . '"}' : '';
                $clsAttr = $class !== '' ? ' ' . htmlspecialchars($class, ENT_QUOTES) : '';
                $innerBlocks = convert_children($inner, $depth + 1);
                // if conversion produced nothing useful, freeform the inner
                if (trim(strip_tags($inner)) !== '' && trim($innerBlocks) === '') {
                    $innerBlocks = "\n<!-- wp:freeform -->\n" . $inner . "\n<!-- /wp:freeform -->\n";
                }
                // preserve important non-class attributes via freeform if present (style, data-*, onclick, etc.)
                $hasExtra = preg_match('/\b(style|data-|onclick|role|aria-|href|src|autoplay|loop|muted|playsinline)=/i', $attrs);
                if ($hasExtra && $tag !== 'div' && $tag !== 'section') {
                    $out .= "\n<!-- wp:html -->\n<" . $tag . $attrs . ">" . $inner . "</" . $tag . ">\n<!-- /wp:html -->\n";
                } else if ($hasExtra && preg_match('/\b(style|data-|onclick)=/i', $attrs)) {
                    // keep as freeform wrapper to not lose attrs
                    $out .= "\n<!-- wp:html -->\n<" . $tag . $attrs . ">" . $inner . "</" . $tag . ">\n<!-- /wp:html -->\n";
                } else {
                    $out .= "\n<!-- wp:group" . $json . " -->\n<div class=\"wp-block-group" . $clsAttr . "\">" . $innerBlocks . "</div>\n<!-- /wp:group -->\n";
                }
                continue;
            }

            // span with class often used as layout - freeform whole
            if ($tag === 'span') {
                $out .= "\n<!-- wp:html -->\n<span" . $attrs . ">" . $inner . "</span>\n<!-- /wp:html -->\n";
                continue;
            }

            // fallback freeform entire element
            $out .= "\n<!-- wp:html -->\n<" . $tag . $attrs . ">" . $inner . "</" . $tag . ">\n<!-- /wp:html -->\n";
            continue;
        }

        // plain text node
        if (preg_match('/\G[^<]+/', $html, $m, 0, $offset)) {
            $text = trim($m[0]);
            if ($text !== '') {
                $out .= "\n<!-- wp:paragraph -->\n<p>" . htmlspecialchars($text, ENT_NOQUOTES) . "</p>\n<!-- /wp:paragraph -->\n";
            }
            $offset += strlen($m[0]);
            continue;
        }

        $offset++;
    }

    return $out;
}

function convert_page_html_to_section_blocks($html) {
    $html = trim($html);
    // strip UTF-8 BOM
    $html = preg_replace('/^\xEF\xBB\xBF/', '', $html);
    $html = preg_replace('/^\x{FEFF}/u', '', $html);

    // If already blocks, return as-is
    if (preg_match('/<!--\s*wp:/', $html)) {
        return $html;
    }

    $blocks = '';
    if (!preg_match_all('/<section\b([^>]*)>(.*?)<\/section>/is', $html, $matches, PREG_SET_ORDER)) {
        // no sections - single freeform
        return "<!-- wp:freeform -->\n" . $html . "\n<!-- /wp:freeform -->\n";
    }

    foreach ($matches as $match) {
        $attrs = $match[1];
        $inner = $match[2];
        $class = '';
        if (preg_match('/\bclass=["\']([^"\']*)["\']/', $attrs, $cm)) {
            $class = trim(preg_replace('/\s+/', ' ', $cm[1]));
        }
        $json = $class !== '' ? ' {"className":"' . esc_json_attr($class) . '"}' : '';
        $clsAttr = $class !== '' ? ' ' . htmlspecialchars($class, ENT_QUOTES) : '';
        $innerBlocks = convert_children($inner);
        if (trim($innerBlocks) === '' && trim($inner) !== '') {
            $innerBlocks = "\n<!-- wp:freeform -->\n" . $inner . "\n<!-- /wp:freeform -->\n";
        }
        $blocks .= "<!-- wp:group" . $json . " -->\n";
        $blocks .= "<div class=\"wp-block-group" . $clsAttr . "\">";
        $blocks .= $innerBlocks;
        $blocks .= "</div>\n<!-- /wp:group -->\n\n";
    }

    return trim($blocks) . "\n";
}

// --- CLI ---
if ($argc < 3) {
    fwrite(STDERR, "Usage: php convert-sections.php input.html output.html\n");
    exit(1);
}
$in = file_get_contents($argv[1]);
$out = convert_page_html_to_section_blocks($in);
file_put_contents($argv[2], $out);
echo "Wrote " . strlen($out) . " bytes to {$argv[2]}\n";
// count root groups
preg_match_all('/<!-- wp:group /', $out, $g);
echo "group markers: " . count($g[0]) . "\n";
