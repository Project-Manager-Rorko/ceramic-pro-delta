<?php
$wp_root = getenv('CERAMIC_WP_ROOT') ?: '/Users/nagrajyr/Desktop/ceramic pro delta';
require rtrim($wp_root, '/') . '/wp-load.php';

function cp_html($value) { return esc_html(trim(preg_replace('/\s+/', ' ', $value))); }
function cp_heading($text, $level = 2, $class = '') {
    $attrs = ['level' => $level];
    if ($class) $attrs['className'] = $class;
    return '<!-- wp:heading ' . wp_json_encode($attrs) . ' -->\n<h' . $level . ($class ? ' class="' . esc_attr($class) . '"' : '') . '>' . cp_html($text) . '</h' . $level . '>\n<!-- /wp:heading -->';
}
function cp_paragraph($text, $class = '') {
    $attrs = $class ? ['className' => $class] : [];
    return '<!-- wp:paragraph ' . ($attrs ? wp_json_encode($attrs) . ' ' : '') . '-->\n<p' . ($class ? ' class="' . esc_attr($class) . '"' : '') . '>' . cp_html($text) . '</p>\n<!-- /wp:paragraph -->';
}
function cp_image($url, $id, $alt = '', $class = '') {
    $attrs = ['id' => $id, 'sizeSlug' => 'large', 'linkDestination' => 'none'];
    if ($class) $attrs['className'] = $class;
    return '<!-- wp:image ' . wp_json_encode($attrs) . ' -->\n<figure class="wp-block-image size-large' . ($class ? ' ' . esc_attr($class) : '') . '"><img src="' . esc_url($url) . '" alt="' . esc_attr($alt) . '" loading="eager" decoding="async" class="wp-image-' . (int) $id . '"/></figure>\n<!-- /wp:image -->';
}
function cp_video($url, $id, $class = '') {
    $attrs = ['id' => $id, 'autoplay' => true, 'loop' => true, 'muted' => true, 'controls' => false];
    if ($class) $attrs['className'] = $class;
    return '<!-- wp:video ' . wp_json_encode($attrs) . ' -->\n<figure class="wp-block-video' . ($class ? ' ' . esc_attr($class) : '') . '"><video autoplay loop muted src="' . esc_url($url) . '"></video></figure>\n<!-- /wp:video -->';
}
function cp_group($inner, $class = '', $background = '', $text = '') {
    $attrs = ['layout' => ['type' => 'constrained']];
    if ($class) $attrs['className'] = $class;
    if ($background) $attrs['style']['color']['background'] = $background;
    if ($text) $attrs['style']['color']['text'] = $text;
    return '<!-- wp:group ' . wp_json_encode($attrs) . ' -->\n<div class="wp-block-group' . ($class ? ' ' . esc_attr($class) : '') . '">' . $inner . '</div>\n<!-- /wp:group -->';
}
function cp_columns($inner, $class = '') {
    $attrs = ['align' => 'wide'];
    if ($class) $attrs['className'] = $class;
    return '<!-- wp:columns ' . wp_json_encode($attrs) . ' -->\n<div class="wp-block-columns' . ($class ? ' ' . esc_attr($class) : '') . '">' . $inner . '</div>\n<!-- /wp:columns -->';
}
function cp_column($inner) { return '<!-- wp:column -->\n<div class="wp-block-column">' . $inner . '</div>\n<!-- /wp:column -->'; }
function cp_button($label, $url) {
    return '<!-- wp:button {"className":"is-style-fill"} -->\n<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="' . esc_url($url) . '">' . cp_html($label) . '</a></div>\n<!-- /wp:button -->';
}
function cp_gallery($names, $alt = 'Ceramic Pro Mangalore') {
    $inner = '';
    foreach ($names as $name) {
        $asset = cp_asset($name);
        if ($asset) $inner .= cp_image($asset['url'], $asset['id'], $alt, 'cp-media-card');
    }
    return $inner ? '<!-- wp:gallery {"columns":3,"linkTo":"none"} -->\n<figure class="wp-block-gallery has-nested-images columns-3 is-cropped cp-media-grid">' . $inner . '</figure>\n<!-- /wp:gallery -->' : '';
}

$source = json_decode(file_get_contents('/tmp/ceramic-pro-pages.json'), true);
$pages = [];
foreach ($source as $item) $pages[$item['slug']] = $item;

$attachments = [];
foreach (get_posts(['post_type' => 'attachment', 'numberposts' => -1, 'post_status' => 'inherit']) as $attachment) {
    $file = get_attached_file($attachment->ID);
    if ($file && is_file($file)) $attachments[basename($file)] = ['id' => $attachment->ID, 'url' => wp_get_attachment_url($attachment->ID)];
}
function cp_asset($name) {
    global $attachments;
    return $attachments[$name] ?? $attachments[str_replace('.svg.png', '.svg_.png', $name)] ?? null;
}
function cp_page_url($slug) { return home_url('/index.php/' . trim($slug, '/') . '/'); }
function cp_svg($name) { return 'https://vipaccounts.org/ceramic-pro/wp-content/uploads/2026/05/' . ltrim($name, '/'); }

function cp_home_blocks($page_ids) {
    $video = cp_asset('ceramic-pro-home-new-banner.webm');
    $logo = ['id' => 0, 'url' => cp_svg('ceramic-logos1.svg')];
    $hero = ($video ? cp_video($video['url'], $video['id'], 'cp-hero-video') : '');
    $hero .= '<div class="cp-hero-overlay"></div>';
    $hero .= cp_heading('#1 CERAMIC PAINT PROTECTION', 5, 'cp-hero-kicker');
    $hero .= cp_heading('Driven by Protection. Defined by Perfection', 1, 'cp-hero-title');
    $badges = '';
    foreach (['ceramiclogos-icon2.svg', 'ceramiclogos-icon3.svg', 'ceramiclogos-icon4.svg', 'ceramiclogos-icon5.svg'] as $badge) $badges .= cp_image(cp_svg($badge), 0, 'Ceramic Pro certification', 'cp-badge');
    $bottom = cp_column('<div class="cp-badges">' . $badges . '</div>');
    $bottom .= cp_column(cp_paragraph('New-level shine, protection, and long-lasting vehicle transformation', 'cp-hero-copy') . '<div class="cp-hero-buttons">' . cp_button('Contact Us', cp_page_url('contact-us')) . cp_button('View Videos', cp_page_url('blog')) . '</div>');
    $hero .= cp_columns($bottom, 'cp-hero-bottom');
    $blocks = [cp_group($hero, 'cp-hero', '#161616', '#ffffff')];

    $about_left = cp_image(cp_svg('left-new-icon1.svg'), 0, 'Paint protection detail', 'cp-about-icon cp-about-icon-left');
    $about_right = cp_image(cp_svg('right-new-icon1.svg'), 0, 'Paint protection detail', 'cp-about-icon cp-about-icon-right');
    $about_car = cp_image(cp_svg('black-car-isolated-white-1.svg'), 0, 'Ceramic Pro vehicle', 'cp-about-car');
    $about_head = cp_group($about_left . cp_heading('Mastering the Art of Paint Protection.', 4) . $about_right, 'cp-about-heading');
    $blocks[] = cp_group($about_head . cp_heading('Ceramic Pro Mangalore is an authorized Ceramic Pro studio delivering high-quality detailing and paint protection services. Every vehicle is treated with precision, care, and a focus on achieving a flawless finish.', 3) . cp_button('About Us', cp_page_url('about-us')) . $about_car, 'cp-section cp-about-studio', '#202020', '#ffffff');

    $kavaca_img = cp_asset('ceramic-pro-Home-page-3rd-sec-img.webp');
    $kavaca = cp_column(cp_heading('KAVACA Paint Protection Film', 2) . cp_paragraph('Protect your vehicle\'s pristine finish with KAVACA PPF, the ultimate shield against road debris, scratches, and environmental elements. Engineered for maximum durability and visual clarity, our self-healing paint protection film ensures your car looks newer for longer.') . cp_button('Discover Process', cp_page_url('paint-protection-film-ppf')));
    if ($kavaca_img) $kavaca = cp_column(cp_image($kavaca_img['url'], $kavaca_img['id'], 'KAVACA Paint Protection Film', 'cp-showcase-image')) . $kavaca;
    $stats = cp_column(cp_heading('10+', 3) . cp_paragraph('Years')) . cp_column(cp_heading('1000+', 3) . cp_paragraph('Vehicles')) . cp_column(cp_heading('1800+', 3) . cp_paragraph('Customers'));
    $blocks[] = cp_group(cp_columns($kavaca, 'cp-kavaca-columns') . cp_columns($stats, 'cp-kavaca-stats'), 'cp-section cp-kavaca', '#161616', '#ffffff');

    $services = [['01', 'Ceramic Coating', 'ceramic-coating'], ['02', 'Paint Protection Film - PPF', 'paint-protection-film-ppf'], ['03', 'Interior Cleaning, Polishing & Conditioning', 'interior-cleaning-polishing-conditioning'], ['04', 'Composite Protection Film (CPF)', 'composite-protection-film-cpf'], ['05', 'Furniture Coating', 'furniture-coating']];
    $service_inner = cp_heading('Our Premium Services', 2);
    $service_images = ['ceramic-coatching-new-img1.webp', 'ceramic-coatching-new-img5.webp', 'ceramic-coatching-new-img4.webp', 'ceramic-coatching-new-img2.webp', 'ceramic-coatching-new-img3.webp'];
    $service_media = '';
    foreach ($service_images as $i => $service_name) {
        $asset = cp_asset($service_name);
        if ($asset) $service_media .= cp_image($asset['url'], $asset['id'], $services[$i][1], 'cp-service-image cp-service-image-' . $i);
    }
    $service_media = cp_group($service_media, 'cp-service-media-stack');
    $service_rows = '';
    foreach ($services as $i => $service) $service_rows .= cp_group(cp_paragraph($service[0], 'cp-service-index') . cp_heading($service[1], 4) . cp_button('→', cp_page_url($service[2])), 'cp-service-row');
    $service_panel = cp_column($service_media . cp_paragraph('Deep gloss. Long-lasting protection.', 'cp-service-caption') . cp_button('Discover Process', cp_page_url('ceramic-coating')));
    $service_list = cp_column($service_rows);
    $blocks[] = cp_group($service_inner . cp_columns($service_panel . $service_list, 'cp-service-layout'), 'cp-section cp-services', '#0a0a0a', '#ffffff');

    $why_img = cp_asset('WhyChooseCeramicPro-img.webp');
    $why_intro = cp_heading('Why Choose Ceramic Pro', 2) . cp_paragraph('High-performance nano ceramic coating designed for superior shine, durability, and easy maintenance.');
    $why_list = '<!-- wp:list --><ul class="wp-block-list"><li>Easy Daily Maintenance</li><li>Superhydrophobic Effect</li><li>Oxidation Resistance</li><li>UV Resistance</li><li>Advanced Chemical Resistance</li><li>Temperature Resistance</li><li>Corrosion Resistance</li><li>Improves Resale Value</li><li>High Gloss Finishing</li></ul><!-- /wp:list -->';
    $why = cp_column($why_intro . ($why_img ? cp_image($why_img['url'], $why_img['id'], 'Ceramic Pro Mustang', 'cp-feature-image') : '')) . cp_column($why_list);
    $blocks[] = cp_group(cp_columns($why, 'cp-feature-columns'), 'cp-section cp-feature-band', '#202020', '#ffffff');

    $kavaca_why = cp_asset('WhyChooseKavaca-img.webp');
    $kavaca_intro = cp_heading('Why Choose Kavaca', 2) . cp_paragraph('Self-healing KAVACA PPF engineered to preserve gloss, clarity, and long-term paint protection.');
    $kavaca_list = '<!-- wp:list --><ul class="wp-block-list"><li>Extreme Gloss</li><li>Scratch Resistance</li><li>Optically Clear Protection</li><li>Ceramic Technology</li><li>Self Healing Film</li><li>Hydrophobic Technology</li></ul><!-- /wp:list -->';
    $why_kavaca = cp_column($kavaca_intro . ($kavaca_why ? cp_image($kavaca_why['url'], $kavaca_why['id'], 'Kavaca Car Protection', 'cp-feature-image') : '')) . cp_column($kavaca_list);
    $blocks[] = cp_group(cp_columns($why_kavaca, 'cp-feature-columns'), 'cp-section cp-feature-band cp-feature-band-alt', '#1a1a1a', '#ffffff');

    $studio = cp_heading('Why Choose Ceramic Pro Mangalore', 2) . cp_paragraph('Certified expertise, premium finish quality, and long-term protection in a professional studio environment.');
    foreach ([['ceramiclogos-icon6.svg', 'Certified Ceramic Pro applicators', 'Certified experts deliver precise coating applications using advanced tools and proven techniques.'], ['ceramiclogos-icon8.svg', 'Premium finish quality', 'Achieve a flawless, high-gloss finish with enhanced clarity and depth.'], ['ceramiclogos-icon7.svg', 'Long-term paint protection', 'Provides durable protection against UV rays, scratches, and environmental contaminants.'], ['ceramiclogos-icon10.svg', 'Professional detailing studio environment', 'Controlled studio setup ensures dust-free application and optimal coating performance.']] as $item) { $studio .= cp_column(cp_image(cp_svg($item[0]), 0, $item[1], 'cp-studio-icon') . cp_heading($item[1], 6) . cp_paragraph($item[2])); }
    $blocks[] = cp_group($studio, 'cp-section cp-studio-features', '#202020', '#ffffff');

    $testimonials = cp_column(cp_image(cp_asset('ceramicpro-Testimonialsecvdo-01.webp')['url'], cp_asset('ceramicpro-Testimonialsecvdo-01.webp')['id'], 'Ceramic Pro testimonial', 'cp-testimonial-media') . cp_heading('Highly Recommend', 4) . cp_paragraph('We recently had the pleasure of visiting Ceramic Pro Mangalore for a ceramic coating job on our Land Cruiser 200, and we must say, the experience was exceptional!') . cp_paragraph('Bhavith Suvarana')) . cp_column(cp_image(cp_asset('ceramicpro-Testimonialsecvdo-02.webp')['url'], cp_asset('ceramicpro-Testimonialsecvdo-02.webp')['id'], 'Ceramic Pro testimonial', 'cp-testimonial-media') . cp_heading('Impressed & Satisfying', 4) . cp_paragraph('I got ceramic coating done for my Volkswagen at Ceramic Pro Mangaluru, and I am very impressed and satisfied with the work they have done.') . cp_paragraph('Hrishikesh Dasgupta')) . cp_column(cp_image(cp_asset('ceramicpro-Testimonialsecvdo-03.webp')['url'], cp_asset('ceramicpro-Testimonialsecvdo-03.webp')['id'], 'Ceramic Pro testimonial', 'cp-testimonial-media') . cp_heading('Excellent Service', 4) . cp_paragraph('Initially was little skeptical about should I get the ceramic done and is it really worth. Then I decided to go ahead and get it done.') . cp_paragraph('Gururaj B K')) . cp_column(cp_heading('Beyond Expectations', 4) . cp_paragraph('Professional work, great attention to detail, and a finish that exceeded expectations.') . cp_paragraph('Rohan Sharma'));
    $testimonials .= $testimonials;
    $blocks[] = cp_group(cp_heading('What Our Clients Say', 2) . cp_paragraph('Real experiences from customers who trust Ceramic Pro for advanced protection, superior finish, and long-lasting results.') . cp_columns($testimonials, 'cp-testimonial-grid'), 'cp-section cp-testimonials', '#202020', '#ffffff');

    $gallery = '';
    for ($i = 1; $i <= 7; $i++) { $asset = cp_asset(sprintf('ceramic-homepage-gallery-%02d.webp', $i)); if ($asset) $gallery .= cp_image($asset['url'], $asset['id'], 'Ceramic Pro Mangalore gallery'); }
    $blocks[] = cp_group(cp_heading('Our Works', 2) . cp_button('View All →', cp_page_url('gallery')) . '<!-- wp:gallery {"columns":3,"linkTo":"none"} --><figure class="wp-block-gallery has-nested-images columns-3 is-cropped cp-portfolio-mosaic">' . $gallery . '</figure><!-- /wp:gallery -->', 'cp-section cp-gallery', '#202020', '#ffffff');
    return implode("\n\n", $blocks);
}

$hero_assets = [
    'home' => 'ceramic-pro1-new2.webp',
    'about-us' => 'cp-landing-hero-about.webp',
    'ceramic-coating' => 'Ceramic-Coating-3rdsec.webp',
    'paint-protection-film-ppf' => 'PPF-3rdsec.webp',
    'interior-cleaning-polishing-conditioning' => 'Interior-Cleaning-Polishing-Conditioning-3rdsec.webp',
    'composite-protection-film-cpf' => 'CPF-3rdsec.webp',
    'furniture-coating' => 'Furniture-Coating-3rdsec.webp',
    'ceramic-pro' => 'ceramic-pro1-new3.webp',
    'products' => 'ceramic-coatching-new-img1.webp',
    'gallery' => 'ceramicpro-gallerypage-updatedimg-01.webp',
    'leadership' => 'leader-1.jpg',
    'contact-us' => 'abtus-4thsec.webp',
    'blogs' => 'blog-1.jpg',
    'blog' => 'blog-detail-hero.jpg',
];

$page_ids = [];
foreach ($pages as $slug => $item) {
    $existing = get_page_by_path($slug, OBJECT, 'page');
    $post = [
        'ID' => $existing ? $existing->ID : 0,
        'post_type' => 'page',
        'post_status' => 'publish',
        'post_title' => wp_strip_all_tags($item['title']['rendered']),
        'post_name' => $slug,
        'post_content' => '',
    ];
    $page_ids[$slug] = wp_insert_post($post, true);
}

foreach ($pages as $slug => $item) {
    if ($slug === 'home') {
        wp_update_post(['ID' => $page_ids[$slug], 'post_content' => cp_home_blocks($page_ids)]);
        continue;
    }
    $html = '<meta charset="utf-8">' . $item['content']['rendered'];
    $dom = new DOMDocument();
    @$dom->loadHTML($html);
    $xpath = new DOMXPath($dom);
    $blocks = [];
    $hero = cp_asset($hero_assets[$slug] ?? '');
    $title = wp_strip_all_tags($item['title']['rendered']);
    $hero_inner = '';
    if ($hero) $hero_inner .= cp_image($hero['url'], $hero['id'], $title, 'cp-hero-image');
    $hero_inner .= '<div class="cp-hero-overlay"></div>';
    $hero_inner .= cp_heading($title, 1, 'cp-hero-title');
    $hero_inner .= cp_paragraph('Ceramic Pro Mangalore | Certified surface protection studio', 'cp-hero-kicker');
    $blocks[] = cp_group($hero_inner, 'cp-hero', '#171717', '#ffffff');
    $seen = [];
    foreach ($xpath->query('//h1|//h2|//h3|//h4|//h5|//h6|//p') as $node) {
        $text = trim(preg_replace('/\s+/', ' ', wp_strip_all_tags($node->textContent)));
        if (!$text || isset($seen[$text]) || $text === $title) continue;
        $seen[$text] = true;
        $tag = strtolower($node->nodeName);
        $level = (int) substr($tag, 1);
        $blocks[] = $level <= 6 && $tag !== 'p' ? cp_heading($text, max(2, $level)) : cp_paragraph($text);
    }
    $list_items = [];
    foreach ($xpath->query('//ul/li|//ol/li') as $node) {
        $text = trim(preg_replace('/\s+/', ' ', wp_strip_all_tags($node->textContent)));
        if ($text && !isset($list_items[$text])) $list_items[$text] = true;
    }
    if ($list_items) {
        $items = '';
        foreach (array_keys($list_items) as $text) $items .= '<li>' . cp_html($text) . '</li>';
        $blocks[] = '<!-- wp:list -->\n<ul class="wp-block-list">' . $items . '</ul>\n<!-- /wp:list -->';
    }

    if ($slug === 'home') {
        $services = [
            ['Ceramic Coating', 'ceramic-coating'],
            ['Paint Protection Film - PPF', 'paint-protection-film-ppf'],
            ['Interior Cleaning, Polishing & Conditioning', 'interior-cleaning-polishing-conditioning'],
            ['Composite Protection Film (CPF)', 'composite-protection-film-cpf'],
            ['Furniture Coating', 'furniture-coating'],
        ];
        $service_inner = cp_heading('Our Premium Services', 2);
        $service_cols = '';
        foreach ($services as $service) $service_cols .= cp_column(cp_heading($service[0], 4) . cp_button('Explore service', cp_page_url($service[1])));
        $blocks[] = cp_group($service_inner . cp_columns($service_cols, 'cp-service-grid'), 'cp-section cp-services');
        $stats = cp_column(cp_heading('10+', 3) . cp_paragraph('Years')) . cp_column(cp_heading('1000+', 3) . cp_paragraph('Vehicles')) . cp_column(cp_heading('1800+', 3) . cp_paragraph('Customers'));
        $blocks[] = cp_group(cp_heading('Protection that lasts.', 2) . cp_columns($stats, 'cp-stats'), 'cp-section cp-stats-section', '#f5f2f7');
        $blocks[] = cp_group(cp_heading('What Our Clients Say', 2) . cp_paragraph('Real experiences from customers who trust Ceramic Pro for advanced protection, superior finish, and long-lasting results.'), 'cp-section cp-testimonials');
        $gallery = '';
        for ($i = 1; $i <= 7; $i++) { $asset = cp_asset(sprintf('ceramic-homepage-gallery-%02d.webp', $i)); if ($asset) $gallery .= cp_image($asset['url'], $asset['id'], 'Ceramic Pro Mangalore gallery'); }
        if ($gallery) $blocks[] = cp_group(cp_heading('Our Works', 2) . '<!-- wp:gallery {"columns":3,"linkTo":"none"} -->\n<figure class="wp-block-gallery has-nested-images columns-3 is-cropped">' . $gallery . '</figure>\n<!-- /wp:gallery -->', 'cp-section cp-gallery');
    }
    if ($slug === 'contact-us') {
        $form = '<!-- wp:html -->
<form class="cp-native-contact-form" action="mailto:" method="post" enctype="text/plain">
<label for="cp-name">FULL NAME</label><input id="cp-name" name="Full name" type="text" required>
<label for="cp-phone">PHONE NUMBER</label><input id="cp-phone" name="Phone number" type="tel" required>
<label for="cp-service">SERVICE OF INTEREST</label><select id="cp-service" name="Service" required><option value="">Select Service</option><option>Ceramic Coating</option><option>Paint Protection Film - PPF</option><option>Interior Cleaning, Polishing &amp; Conditioning</option><option>Composite Protection Film (CPF)</option><option>Furniture Coating</option></select>
<label for="cp-message">MESSAGE</label><textarea id="cp-message" name="Message" rows="5"></textarea>
<button type="submit">SUBMIT REQUEST</button>
</form>
<!-- /wp:html -->';
        $blocks[] = cp_group($form, 'cp-section cp-contact-form', '#f5f2f7');
    }
    if ($slug === 'gallery') {
        $gallery = '';
        for ($i = 1; $i <= 24; $i++) { $asset = cp_asset(sprintf('ceramicpro-gallerypage-updatedimg-%02d.webp', $i)); if ($asset) $gallery .= cp_image($asset['url'], $asset['id'], 'Ceramic Pro project'); }
        if ($gallery) $blocks[] = '<!-- wp:gallery {"columns":4,"linkTo":"none"} -->\n<figure class="wp-block-gallery has-nested-images columns-4 is-cropped">' . $gallery . '</figure>\n<!-- /wp:gallery -->';
    }
    if ($slug !== 'gallery') {
        $media_names = [];
        foreach ($xpath->query('//img') as $node) {
            $src = $node->getAttribute('src');
            $name = basename(parse_url($src, PHP_URL_PATH));
            if (!$name || preg_match('/^(ceramic-logos|ceramiclogos-icon[2-5]|ic_outline|locacation-footer|mob-footer|email-footer|toggle|Group-8438|youtube|our-works)/', $name)) continue;
            if (!in_array($name, $media_names, true) && cp_asset($name)) $media_names[] = $name;
        }
        if ($media_names) $blocks[] = cp_group(cp_heading('Featured Media', 2) . cp_gallery($media_names), 'cp-section cp-source-media', '#202020', '#ffffff');
    }
    $content = implode("\n\n", $blocks);
    if ($slug === 'contact-us') {
        // Keep native form tags inside the Gutenberg Custom HTML block.
        $GLOBALS['wpdb']->update($GLOBALS['wpdb']->posts, ['post_content' => $content], ['ID' => $page_ids[$slug]], ['%s'], ['%d']);
        clean_post_cache($page_ids[$slug]);
    } else {
        wp_update_post(['ID' => $page_ids[$slug], 'post_content' => $content]);
    }
}

update_option('show_on_front', 'page');
update_option('page_on_front', $page_ids['home']);
update_option('page_for_posts', $page_ids['blog']);
update_option('blogname', 'Ceramic Pro Mangalore');
update_option('blogdescription', 'Driven by Protection. Defined by Perfection.');

if (function_exists('wp_update_custom_css_post')) {
    $css = <<<'CSS'
@font-face { font-family: Geist; src: url('GEIST_REGULAR_URL') format('woff2'); font-weight: 400; font-display: swap; }
@font-face { font-family: Geist; src: url('GEIST_MEDIUM_URL') format('woff2'); font-weight: 500; font-display: swap; }
@font-face { font-family: Geist; src: url('GEIST_SEMIBOLD_URL') format('woff2'); font-weight: 600; font-display: swap; }
body { background: #ffffff; color: #181818; }
body, .wp-site-blocks { font-family: Geist, Arial, Helvetica, sans-serif; -webkit-font-smoothing: antialiased; }
.wp-site-blocks { padding-top: 0; padding-bottom: 0; }
.wp-block-post-title { display: none; }
.wp-block-navigation__container { gap: 1.1rem !important; }
.wp-block-template-part { margin: 0; }
.wp-site-blocks > .wp-block-template-part:first-child { position: relative; z-index: 20; }
.wp-site-blocks > main { margin-top: -98px !important; }
.wp-site-blocks > main > .entry-content { margin-block-start: 0 !important; }
.cp-header { position: absolute !important; top: 98px; left: 0; z-index: 20; width: 100vw; max-width: none !important; margin-left: 0 !important; margin-bottom: 0 !important; }
.cp-header .wp-block-group { background: #16161666 !important; backdrop-filter: none; border: 1px solid #ffffff1f; }
.cp-header .wp-block-image { margin: 0; }
.cp-header .wp-block-image img { width: 190px; height: auto; }
.cp-header .wp-block-navigation-item__content { color: #fff; font-size: 16px; }
.cp-header .wp-block-columns { width: 100%; max-width: none !important; margin: 0 !important; padding: 0 32px; }
.cp-header .wp-block-column:first-child { flex-basis: 243px !important; flex-grow: 0; }
.cp-header .wp-block-column:nth-child(2) { display: flex; align-items: center; justify-content: flex-end; gap: 20px; }
.cp-header-call .wp-block-button__link { background: #d62381; border-radius: 50px; color: #fff; padding: 12px 24px; text-transform: uppercase; font-size: 13px; }
.cp-hero, .cp-section { width: 100vw; max-width: none !important; margin-left: calc(50% - 50vw) !important; }
.cp-hero { position: relative; min-height: 100vh; display: block; overflow: hidden; text-align: left; padding: 120px 80px 60px; background: #161616; }
.cp-hero > .wp-block-group__inner-container { position: relative; z-index: 2; width: 100%; min-height: calc(100vh - 180px); display: flex; flex-direction: column; justify-content: space-between; }
.cp-hero > .wp-block-heading, .cp-hero > .wp-block-columns { position: relative; z-index: 3; }
.cp-hero-video { position: absolute; inset: 0; z-index: 0; margin: 0 !important; max-width: none !important; }
.cp-hero-video video { width: 100%; height: 100%; object-fit: cover; }
.cp-hero-image { position: absolute; inset: 0; z-index: 0; max-width: none !important; margin: 0 !important; }
.cp-hero-image img { width: 100%; height: 100%; object-fit: cover; }
.cp-hero-overlay { position: absolute; inset: 0; z-index: 1; background: linear-gradient(180deg, #16161600 48%, #161616cc 100%); }
.cp-hero-logo { position: relative; z-index: 3; width: 243px; margin: 0 0 20px !important; }
.cp-hero-logo img { width: 243px; height: auto; }
.cp-hero-title { color: #ffffff; font-size: clamp(2.25rem, 1.7143rem + 1.7857vw, 3.5rem); line-height: 1.3; max-width: 820px; margin: 0 0 20px; font-weight: 400; }
.cp-hero-kicker { color: #ffffff; text-transform: uppercase; letter-spacing: 3px; font-size: clamp(1.25rem, 1.1964rem + 0.1786vw, 1.375rem); line-height: 32px; margin: 0 0 12px; }
.cp-hero-bottom { width: 100%; align-items: end; color: #fff; }
.cp-badges { display: flex; align-items: center; gap: 25px; }
.cp-badge { width: 125px; height: 95px; margin: 0 !important; }
.cp-hero-bottom .wp-block-column:first-child { flex-basis: 65% !important; }
.cp-hero-bottom .wp-block-column:last-child { flex-basis: 35% !important; min-width: 360px; }
.cp-badge img { width: 100%; height: 100%; object-fit: contain; }
.cp-hero-copy { color: #ffffffcc; max-width: 360px; margin: 0 0 25px; }
.cp-hero-buttons { display: flex; gap: 14px; flex-wrap: nowrap; }
.cp-hero-buttons .wp-block-button__link, .cp-section .wp-block-button__link { background: #d62381; color: #fff; border-radius: 50px; padding: 13px 28px; text-transform: uppercase; font-size: 13px; }
.cp-section { padding: 90px 80px; }
.cp-section > .wp-block-group__inner-container { max-width: 1280px; margin: 0 auto; }
.cp-section > .wp-block-group__inner-container > .wp-block-heading { color: #fff; font-size: clamp(2rem, 3vw, 3.3rem); font-weight: 400; line-height: 1.1; }
.cp-about-studio { min-height: 420px; position: relative; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; overflow: hidden; background: linear-gradient(180deg,#202020 0%,#2b1c24 42%,#202020 100%) !important; }
.cp-about-studio::before { content: ''; position: absolute; left: 50%; top: 50%; width: 60%; height: 400px; transform: translate(-50%, -50%); background: #d62381; opacity: .15; filter: blur(100px); pointer-events: none; }
.cp-about-studio > * { position: relative; z-index: 1; }
.cp-about-heading { display: flex; align-items: center; justify-content: center; gap: 28px; width: 100%; }
.cp-about-icon { flex: 0 0 84px; width: 84px !important; height: 145px; margin: 0 !important; }
.cp-about-icon img { display: block; width: 84px !important; height: 145px !important; max-width: none !important; object-fit: contain; }
.cp-about-studio .wp-block-group__inner-container { max-width: 900px; }
.cp-about-studio p { color: #ffffffcc; max-width: 800px; margin: 22px auto 30px; font-size: 18px; line-height: 1.55; }
.cp-about-studio .wp-block-button { margin: 0 auto; }
.cp-kavaca-columns, .cp-feature-columns { align-items: center; gap: 80px; }
.cp-kavaca-columns .wp-block-column:first-child, .cp-feature-columns .wp-block-column:first-child { flex-basis: 48% !important; }
.cp-kavaca-columns .wp-block-column:last-child, .cp-feature-columns .wp-block-column:last-child { flex-basis: 52% !important; }
.cp-showcase-image, .cp-feature-image { margin: 0 !important; }
.cp-showcase-image img, .cp-feature-image img { width: 100%; border-radius: 8px; }
.cp-kavaca p, .cp-feature-band p, .cp-services p, .cp-testimonials p, .cp-studio-features p { color: #ffffffaa; font-size: 17px; line-height: 1.55; }
.cp-services { background: #0a0a0a !important; }
.cp-service-media-stack { position: relative; height: 405px; margin: 0 !important; overflow: hidden; }
.cp-service-media-stack .cp-service-image { position: absolute; inset: 0; width: 100%; height: 100%; margin: 0 !important; opacity: 0; animation: cp-service-fade 15s linear infinite; }
.cp-service-media-stack .cp-service-image img { width: 100%; height: 100%; object-fit: cover; border-radius: 0; }
.cp-service-media-stack .cp-service-image:nth-child(1) { animation-delay: 0s; }
.cp-service-media-stack .cp-service-image:nth-child(2) { animation-delay: 3s; }
.cp-service-media-stack .cp-service-image:nth-child(3) { animation-delay: 6s; }
.cp-service-media-stack .cp-service-image:nth-child(4) { animation-delay: 9s; }
.cp-service-media-stack .cp-service-image:nth-child(5) { animation-delay: 12s; }
.cp-services > .wp-block-group__inner-container > .wp-block-heading { margin-bottom: 14px; }
.cp-service-grid { gap: 0; margin-top: 35px; }
.cp-service-grid .wp-block-column { border-top: 1px solid #ffffff26; padding: 25px 14px; min-width: 0; }
.cp-service-index { color: #ffffff; font-size: 13px !important; }
.cp-service-grid h4 { color: #ffffff; font-size: 20px; font-weight: 400; min-height: 58px; }
.cp-service-image { margin: 0 0 22px !important; }
.cp-service-image img { width: 100%; height: 180px; object-fit: cover; border-radius: 8px; }
.cp-service-grid .wp-block-button { margin-top: 18px; }
.cp-feature-band { background: #202020 !important; }
.cp-feature-band-alt { background: #1a1a1a !important; }
.cp-feature-band ul { color: #ffffffcc; columns: 2; line-height: 1.9; padding-left: 20px; }
.cp-studio-features { text-align: center; }
.cp-studio-features > .wp-block-group__inner-container { display: grid; grid-template-columns: repeat(4, 1fr); gap: 18px; }
.cp-studio-features > .wp-block-group__inner-container > .wp-block-heading, .cp-studio-features > .wp-block-group__inner-container > .wp-block-paragraph { grid-column: 1 / -1; }
.cp-studio-features .wp-block-column { background: #161616; border: 1px solid #ffffff08; border-radius: 12px; padding: 30px 24px; }
.cp-studio-icon { width: 48px; height: 48px; margin: 0 auto 18px !important; }
.cp-studio-icon img { width: 100%; height: 100%; object-fit: contain; }
.cp-studio-features h6 { color: #fff; font-size: 18px; font-weight: 400; }
.cp-testimonials { background: #202020 !important; }
.cp-testimonial-grid { gap: 16px; margin-top: 32px; }
.cp-testimonial-grid .wp-block-column { border: 1px solid #ffffff33; border-radius: 12px; padding: 30px 24px; }
.cp-testimonial-media { margin: -30px -24px 24px !important; }
.cp-testimonial-media img { width: 100%; height: 220px; object-fit: cover; border-radius: 12px 12px 0 0; }
.cp-testimonial-grid h4 { color: #fff; font-size: 22px; font-weight: 500; }
.cp-gallery { background: #202020 !important; color: #fff; }
.cp-gallery .wp-block-heading { color: #fff; }
.wp-block-post-content { background: #161616; color: #fff; }
.wp-block-post-content > .wp-block-heading, .wp-block-post-content > .wp-block-paragraph, .wp-block-post-content > .wp-block-list { width: min(1120px, calc(100% - 160px)); margin-left: auto; margin-right: auto; }
.wp-block-post-content > .wp-block-heading { color: #fff; margin-top: 48px; }
.wp-block-post-content > .wp-block-paragraph { color: #ffffffaa; font-size: 17px; line-height: 1.6; }
.wp-block-post-content > .wp-block-list { color: #ffffffcc; padding-left: 28px; line-height: 1.8; }
.cp-contact-form { max-width: 900px; margin: 0 auto 64px; }
.wp-block-gallery { gap: 14px; }
.cp-media-grid { margin-top: 30px; }
.cp-media-grid .cp-media-card { flex: 1 1 calc(33.333% - 14px); margin: 0; }
.cp-media-grid .cp-media-card img { width: 100%; height: 260px; object-fit: cover; border-radius: 8px; }
.cp-bottom-social-strip { border-top: 1px solid #ffffff33; display: flex; flex-wrap: wrap; gap: 25px; padding-top: 26px; }
.cp-bottom-social-strip a { color: #ffffffcc; text-decoration: none; }
.cp-bottom-social-strip .cp-whatsapp { margin-left: auto; color: #fff; background: #25d366; border-radius: 50px; padding: 8px 16px; }
.cp-native-contact-form { display: grid; gap: 10px; max-width: 720px; margin: 0 auto; }
.cp-native-contact-form label { color: #202020; font-size: 12px; letter-spacing: 1px; font-weight: 600; }
.cp-native-contact-form input, .cp-native-contact-form select, .cp-native-contact-form textarea { width: 100%; box-sizing: border-box; border: 1px solid #20202033; background: #fff; color: #202020; padding: 13px 14px; font: inherit; }
.cp-native-contact-form textarea { resize: vertical; }
.cp-native-contact-form button { justify-self: start; border: 0; border-radius: 50px; background: #d62381; color: #fff; padding: 14px 28px; cursor: pointer; font: inherit; letter-spacing: 1px; }
.cp-testimonial-grid { overflow: visible; }
.cp-testimonial-media { position: relative; }
.cp-testimonial-media::after { content: '▶'; position: absolute; left: 50%; top: 50%; transform: translate(-50%, -50%); width: 46px; height: 46px; display: grid; place-items: center; border: 1px solid #fff; border-radius: 50%; color: #fff; background: #d62381cc; font-size: 16px; }
@keyframes cp-testimonial-marquee { from { transform: translateX(0); } to { transform: translateX(-50%); } }
@keyframes cp-service-fade { 0%, 15% { opacity: 1; } 20%, 100% { opacity: 0; } }
@media (max-width: 900px) { .cp-hero { min-height: 820px; padding: 110px 24px 40px; } .cp-hero > .wp-block-group__inner-container { min-height: 720px; } .cp-section { padding: 70px 24px; } .wp-block-post-content > .wp-block-heading, .wp-block-post-content > .wp-block-paragraph, .wp-block-post-content > .wp-block-list { width: calc(100% - 48px); } .cp-hero-bottom { flex-direction: column; align-items: flex-start; gap: 35px; } .cp-hero-bottom .wp-block-column:last-child { min-width: 0; } .cp-badges { flex-wrap: wrap; gap: 14px; } .cp-badge { width: 75px; height: 60px; } .cp-kavaca-columns, .cp-feature-columns { gap: 35px; } .cp-service-grid .wp-block-column { flex-basis: 100% !important; } .cp-studio-features > .wp-block-group__inner-container { grid-template-columns: 1fr 1fr; } }
@media (max-width: 520px) { .cp-hero-title { font-size: 2.4rem; } .cp-hero-logo { width: 190px; } .cp-hero-logo img { width: 190px; } .cp-feature-band ul { columns: 1; } .cp-studio-features > .wp-block-group__inner-container { grid-template-columns: 1fr; } }
@media (max-width: 900px) { .cp-testimonials { overflow: hidden; } .cp-testimonial-grid { display: flex !important; flex-wrap: nowrap !important; width: max-content !important; animation: cp-testimonial-marquee 34s linear infinite; } .cp-testimonial-grid .wp-block-column { flex: 0 0 280px; } .cp-testimonials:hover .cp-testimonial-grid, .cp-testimonials:focus-within .cp-testimonial-grid { animation-play-state: paused; } }

/* Source homepage geometry: native blocks retain editability while these classes
   reproduce the live composition and keep each band visually bounded. */
@media (min-width: 901px) {
    body.home .wp-site-blocks { margin-bottom: -66px !important; }
    .wp-block-post-content > .cp-hero { margin-top: 9px !important; }
    .wp-block-post-content > .cp-section { margin-top: 0 !important; }
    .cp-header { top: 32px; }
    .cp-header .wp-block-group { background: #16161666 !important; border-color: #ffffff1f; }
    .cp-hero { height: 720px; min-height: 720px; padding: 80px 40px 40px; }
    .cp-hero > .wp-block-group__inner-container { min-height: 600px; max-width: none !important; position: static; }
    .cp-hero > .wp-block-group__inner-container > * { max-width: none !important; margin-left: 0 !important; margin-right: 0 !important; }
    .cp-hero > .cp-hero-kicker, .cp-hero > .cp-hero-title, .cp-hero > .cp-hero-bottom { max-width: none !important; margin-left: 0 !important; margin-right: 0 !important; }
    .cp-hero > .cp-hero-bottom { width: 100% !important; }
    .cp-hero > .cp-hero-kicker { position: absolute; left: 40px; top: 132px; }
    .cp-hero > .cp-hero-title { position: absolute; left: 40px; top: 164px; }
    .cp-hero > .cp-hero-bottom { position: absolute; left: 40px; right: 40px; bottom: 40px; width: auto !important; }
    .cp-hero-kicker { font-size: 16px; line-height: 1.4; }
    .cp-hero-title { font-size: 56px; max-width: 720px; }
    .cp-badges .wp-block-image { flex: 0 0 125px !important; max-width: 125px !important; }
    .cp-about-studio { height: 627px; min-height: 627px; padding: 60px 40px; background: linear-gradient(180deg,#202020 0%,#202020 10.87%,#2b1c24 25.75%,#472436 41.28%,#51273c 48.56%,#412232 56.49%,#2b1c24 65.41%,#202020 74.34%,#202020 91.83%) !important; }
    .cp-about-studio > .wp-block-group__inner-container { max-width: 1120px; }
    .cp-about-heading { display: flex; align-items: center; justify-content: center; gap: 28px; width: 100%; }
    .cp-about-heading .wp-block-heading { width: 316px; margin: 0; font-size: 26px !important; line-height: 36px !important; letter-spacing: 3px; white-space: normal; }
    .cp-about-icon { flex: 0 0 48px; width: 48px !important; height: 145px; margin: 0 !important; }
    .cp-about-icon img { width: 48px !important; height: 145px !important; }
    .cp-about-studio h3 { font-size: 22px; line-height: 1.5; font-weight: 400; max-width: 980px; margin: 34px auto; }
    .cp-about-car { position: absolute; bottom: -4px; left: 0; width: 640px; transform: none; opacity: .25; pointer-events: none; z-index: 0 !important; }
    .cp-about-car img { width: 100%; }
    .cp-kavaca { height: 550px; min-height: 550px; padding: 60px 40px 34px; background: linear-gradient(180deg, #161616d1, #1616167a 50%, #161616d1), url('https://vipaccounts.org/ceramic-pro/wp-content/uploads/2026/06/ceramicpro-3rdsecimage.webp') center/cover no-repeat !important; }
    .cp-kavaca-columns { width: 100% !important; max-width: none !important; margin: 0 !important; }
    .cp-kavaca-columns > .wp-block-column:first-child { display: none; }
    .cp-kavaca-columns > .wp-block-column:last-child { flex: 1 1 100% !important; max-width: none !important; }
    .cp-kavaca-columns .cp-showcase-image { display: none; }
    .cp-kavaca h2 { font-size: 42px !important; text-align: center; }
    .cp-kavaca-columns p { max-width: 850px; margin-left: auto; margin-right: auto; text-align: center; }
    .cp-kavaca-columns .wp-block-button { display: none; }
    .cp-kavaca-stats { margin: auto 0 0 !important; justify-content: flex-start; gap: 70px; }
    .cp-kavaca-stats .wp-block-column { flex-basis: auto !important; flex-grow: 0; }
    .cp-kavaca-stats h3 { font-size: 42px; margin: 0; }
    .cp-kavaca-stats p { margin: 0; color: #fff !important; }
    .cp-services { height: 651px; min-height: 651px; padding: 58px 40px; }
    .cp-service-layout { display: grid !important; grid-template-columns: 1.2fr 1fr; gap: 80px; margin-top: 20px; }
    .cp-service-layout > .wp-block-column { flex-basis: auto !important; }
    .cp-service-image { margin: 0 !important; }
    .cp-service-image img { height: 405px; border-radius: 0; object-fit: cover; }
    .cp-service-media-stack { height: 405px; }
    .cp-service-caption { margin: 10px 0 0; color: #fff !important; }
    .cp-service-row { display: grid; grid-template-columns: 1fr auto; align-items: center; border-top: 1px solid #ffffff3a; padding: 13px 0; }
    .cp-service-row:last-child { border-bottom: 1px solid #ffffff3a; }
    .cp-service-row .wp-block-paragraph, .cp-service-row .wp-block-heading { margin: 0; }
    .cp-service-row .wp-block-heading { font-size: 21px !important; font-weight: 400; }
    .cp-service-row .wp-block-button { grid-column: 2; grid-row: 1 / span 2; margin: 0; }
    .cp-service-row .wp-block-button__link { background: none !important; padding: 0 !important; font-size: 26px; }
    .cp-feature-band { height: 670px; min-height: 670px; padding: 55px 40px; }
    .cp-feature-band-alt { height: 584px; min-height: 584px; }
    .cp-feature-band .wp-block-columns { height: 100%; width: 100% !important; max-width: none !important; margin: 0; }
    .cp-feature-band .wp-block-column { min-width: 0; }
    .cp-feature-band:not(.cp-feature-band-alt) .cp-feature-columns { display: block !important; position: relative; }
    .cp-feature-band:not(.cp-feature-band-alt) .cp-feature-columns > .wp-block-column:first-child { width: 100% !important; max-width: none !important; }
    .cp-feature-band:not(.cp-feature-band-alt) .cp-feature-image { position: absolute; left: 120px; top: 131px; width: 780px; height: 350px; margin: 0 !important; }
    .cp-feature-band:not(.cp-feature-band-alt) .cp-feature-image img { width: 100%; height: 350px; max-height: none; object-fit: contain; }
    .cp-feature-band:not(.cp-feature-band-alt) .cp-feature-columns > .wp-block-column:last-child { position: static; width: 100% !important; max-width: none !important; }
    .cp-feature-band:not(.cp-feature-band-alt) .cp-feature-columns h2 { position: absolute; left: 0; top: 0; width: 50%; margin: 0; }
    .cp-feature-band:not(.cp-feature-band-alt) .cp-feature-columns p { position: absolute; left: 0; top: 114px; width: 50%; margin: 0; }
    .cp-feature-band:not(.cp-feature-band-alt) .cp-feature-columns ul { position: absolute; right: 0; top: -7px; width: 458px; height: 550px; margin: 0; padding: 0; columns: 1; list-style: none; line-height: 1; }
    .cp-feature-band:not(.cp-feature-band-alt) .cp-feature-columns li { position: absolute; left: 0; display: flex; align-items: center; width: 100%; height: 50px; margin: 0; padding-left: 32px; font-size: 18px; white-space: nowrap; }
    .cp-feature-band:not(.cp-feature-band-alt) .cp-feature-columns li::before { content: ''; position: absolute; left: 0; width: 8px; height: 8px; border-radius: 50%; background: #d62381; box-shadow: 0 0 8px #d62381, 0 0 16px #d6238166; }
    .cp-feature-band:not(.cp-feature-band-alt) .cp-feature-columns li::after { content: ''; position: absolute; left: 8px; width: 26px; height: 1px; background: #d6238159; }
    .cp-feature-band:not(.cp-feature-band-alt) .cp-feature-columns li:nth-child(1) { top: 0; left: 0; }
    .cp-feature-band:not(.cp-feature-band-alt) .cp-feature-columns li:nth-child(2) { top: 50px; left: 54px; }
    .cp-feature-band:not(.cp-feature-band-alt) .cp-feature-columns li:nth-child(3) { top: 100px; left: 80px; }
    .cp-feature-band:not(.cp-feature-band-alt) .cp-feature-columns li:nth-child(4) { top: 150px; left: 98px; }
    .cp-feature-band:not(.cp-feature-band-alt) .cp-feature-columns li:nth-child(5) { top: 200px; left: 108px; }
    .cp-feature-band:not(.cp-feature-band-alt) .cp-feature-columns li:nth-child(6) { top: 250px; left: 108px; }
    .cp-feature-band:not(.cp-feature-band-alt) .cp-feature-columns li:nth-child(7) { top: 300px; left: 100px; }
    .cp-feature-band:not(.cp-feature-band-alt) .cp-feature-columns li:nth-child(8) { top: 350px; left: 72px; }
    .cp-feature-band:not(.cp-feature-band-alt) .cp-feature-columns li:nth-child(9) { top: 400px; left: 32px; }
    .cp-feature-band:not(.cp-feature-band-alt) .cp-feature-columns::after { content: ''; position: absolute; right: 56px; top: -12px; width: 205px; height: 575px; border: 1px solid #d6238138; border-left: 0; border-radius: 0 50% 50% 0; pointer-events: none; }
    .cp-feature-band-alt .cp-feature-columns { display: block !important; position: relative; }
    .cp-feature-band-alt .cp-feature-columns > .wp-block-column:first-child { width: 100% !important; max-width: none !important; }
    .cp-feature-band-alt .cp-feature-image { position: absolute; left: 20%; top: 145px; width: 60%; margin: 0 !important; }
    .cp-feature-band-alt .cp-feature-image img { width: 100%; max-height: none; object-fit: contain; }
    .cp-feature-band-alt .cp-feature-columns > .wp-block-column:last-child { position: static; width: 100% !important; max-width: none !important; }
    .cp-feature-band-alt .cp-feature-columns h2 { position: absolute; left: 0; top: 0; width: 100%; margin: 0; text-align: center; }
    .cp-feature-band-alt .cp-feature-columns p { position: absolute; left: 0; top: 106px; width: 100%; margin: 0; text-align: center; }
    .cp-feature-band-alt .cp-feature-columns ul { position: absolute; inset: 0; width: 100%; height: 100%; margin: 0; padding: 0; list-style: none; columns: 1; }
    .cp-feature-band-alt .cp-feature-columns li { position: absolute; display: flex; align-items: center; gap: 10px; font-size: 16px; white-space: nowrap; }
    .cp-feature-band-alt .cp-feature-columns li::before { content: ''; width: 8px; height: 8px; border-radius: 50%; background: #d62381; box-shadow: 0 0 8px #d62381; }
    .cp-feature-band-alt .cp-feature-columns li::after { content: ''; position: absolute; left: 8px; top: 50%; width: 38px; height: 1px; background: #d6238159; }
    .cp-feature-band-alt .cp-feature-columns li:hover { color: #d62381; }
    .cp-feature-band-alt .cp-feature-columns li:nth-child(1) { left: 4%; top: 190px; }
    .cp-feature-band-alt .cp-feature-columns li:nth-child(2) { left: 2%; top: 285px; }
    .cp-feature-band-alt .cp-feature-columns li:nth-child(3) { left: 4%; top: 380px; }
    .cp-feature-band-alt .cp-feature-columns li:nth-child(4) { right: 4%; top: 190px; }
    .cp-feature-band-alt .cp-feature-columns li:nth-child(5) { right: 2%; top: 285px; }
    .cp-feature-band-alt .cp-feature-columns li:nth-child(6) { right: 4%; top: 380px; }
    .cp-feature-band ul { font-size: 15px; line-height: 1.55; }
    .cp-studio-features { height: 634px; min-height: 634px; padding: 55px 40px; }
    .cp-studio-features > .wp-block-group__inner-container { grid-template-columns: repeat(4, 1fr); align-content: center; }
    .cp-studio-features .wp-block-column { padding: 24px 18px; min-height: 220px; }
    .cp-testimonials { height: 706px; min-height: 706px; padding: 55px 40px; }
    .cp-testimonial-grid { display: grid !important; grid-template-columns: repeat(4, 1fr); gap: 16px; }
    .cp-testimonial-grid { display: flex !important; flex-wrap: nowrap !important; width: max-content !important; animation: cp-testimonial-marquee 34s linear infinite; }
    .cp-testimonials:hover .cp-testimonial-grid, .cp-testimonials:focus-within .cp-testimonial-grid { animation-play-state: paused; }
    .cp-testimonial-grid .wp-block-column { padding: 20px 18px; min-width: 0; }
    .cp-testimonial-grid .wp-block-column { flex: 0 0 280px; }
    .cp-testimonial-grid > .wp-block-column { flex: 0 0 280px !important; width: 280px !important; min-width: 280px !important; max-width: 280px !important; box-sizing: border-box; }
    .cp-testimonial-grid > .wp-block-column > * { max-width: none; }
    .cp-testimonial-media { margin: -20px -18px 18px !important; }
    .cp-testimonial-media img { height: 220px; }
    .cp-testimonials .wp-block-heading { font-size: 40px !important; }
    .cp-gallery { height: 829px; min-height: 829px; padding: 55px 40px; }
    .cp-gallery > .wp-block-group__inner-container { position: relative; }
    .cp-gallery > .wp-block-group__inner-container > .wp-block-button { position: absolute; right: 0; top: 0; }
    .cp-portfolio-mosaic { display: grid !important; grid-template-columns: 1fr 1fr 1fr; grid-template-rows: 1fr 1fr; gap: 14px; height: 650px; margin-top: 40px; }
    .cp-portfolio-mosaic .wp-block-image { width: auto !important; height: auto !important; margin: 0 !important; }
    .cp-portfolio-mosaic .wp-block-image img { width: 100%; height: 100%; object-fit: cover; border-radius: 0; }
    .cp-portfolio-mosaic .wp-block-image:nth-child(1) { grid-row: span 2; }
    .cp-portfolio-mosaic .wp-block-image:nth-child(4) { grid-row: span 2; }
    .cp-portfolio-mosaic .wp-block-image:nth-child(7) { grid-row: span 2; }
    .cp-footer { margin-top: -70px !important; margin-bottom: 0 !important; padding-top: 24px !important; padding-bottom: 24px !important; }
}
CSS;
    foreach (['GEIST_REGULAR_URL' => 'Geist-Regular.woff2', 'GEIST_MEDIUM_URL' => 'Geist-Medium.woff2', 'GEIST_SEMIBOLD_URL' => 'Geist-SemiBold.woff2'] as $placeholder => $font) {
        $asset = cp_asset($font);
        if ($asset) $css = str_replace($placeholder, $asset['url'], $css);
    }
    $css_post = wp_update_custom_css_post($css, 'twentytwentyfive');
    if ($css_post && !is_wp_error($css_post)) $wpdb->update($wpdb->posts, ['post_content' => $css], ['ID' => $css_post->ID]);
}

$nav_items = [
    ['Home', 'home'], ['About Us', 'about-us'], ['Services', 'ceramic-coating'], ['Products', 'products'], ['Our Works', 'gallery'], ['Ceramic Pro', 'ceramic-pro'], ['Contact Us', 'contact-us'],
];
$nav = '';
foreach ($nav_items as $item) {
    $nav .= '<!-- wp:navigation-link ' . wp_json_encode(['label' => $item[0], 'type' => 'page', 'id' => $page_ids[$item[1]], 'url' => cp_page_url($item[1])]) . ' /-->\n';
}
$header = '<!-- wp:group {"align":"full","style":{"color":{"background":"#171717","text":"#ffffff"},"spacing":{"padding":{"top":"18px","bottom":"18px","left":"24px","right":"24px"}}},"layout":{"type":"constrained"}} -->\n<div class="wp-block-group alignfull has-white-color has-text-color has-background" style="background-color:#171717;padding-top:18px;padding-right:24px;padding-bottom:18px;padding-left:24px"><!-- wp:columns {"verticalAlignment":"center"} -->\n<div class="wp-block-columns are-vertically-aligned-center"><!-- wp:column {"verticalAlignment":"center"} -->\n<div class="wp-block-column is-vertically-aligned-center"><!-- wp:site-title {"level":0,"fontSize":"medium"} /--></div>\n<!-- /wp:column --><!-- wp:column {"verticalAlignment":"center"} -->\n<div class="wp-block-column is-vertically-aligned-center"><!-- wp:navigation {"overlayMenu":"mobile","layout":{"type":"flex","justifyContent":"right"}} -->\n<nav class="wp-block-navigation is-content-justification-right is-layout-flex wp-container-core-navigation-is-layout-1 wp-block-navigation-is-layout-flex">' . $nav . '</nav>\n<!-- /wp:navigation --></div>\n<!-- /wp:column --></div>\n<!-- /wp:columns --></div>\n<!-- /wp:group -->';
$footer = '<!-- wp:group {"align":"full","style":{"color":{"background":"#171717","text":"#ffffff"},"spacing":{"padding":{"top":"56px","bottom":"56px","left":"24px","right":"24px"}}},"layout":{"type":"constrained"}} -->\n<div class="wp-block-group alignfull has-white-color has-text-color has-background" style="background-color:#171717;padding-top:56px;padding-right:24px;padding-bottom:56px;padding-left:24px"><!-- wp:columns -->\n<div class="wp-block-columns"><!-- wp:column --><div class="wp-block-column"><!-- wp:site-title {"level":0} /--><!-- wp:paragraph --><p>Driven by Protection. Defined by Perfection.</p><!-- /wp:paragraph --></div><!-- /wp:column --><!-- wp:column --><div class="wp-block-column"><!-- wp:heading {"level":4} --><h4>Studio Information</h4><!-- /wp:heading --><!-- wp:paragraph --><p>#G01 Delta House, Kulur Ferry Road, Kulur, Mangalore</p><p>+91 63642 68555</p><p>Mon - Sat: 9:00 AM - 7:00 PM</p><!-- /wp:paragraph --></div><!-- /wp:column --><!-- wp:column --><div class="wp-block-column"><!-- wp:heading {"level":4} --><h4>Quick Links</h4><!-- /wp:heading --><!-- wp:navigation {"overlayMenu":"never"} -->\n<nav class="wp-block-navigation">' . $nav . '</nav>\n<!-- /wp:navigation --></div><!-- /wp:column --></div>\n<!-- /wp:columns --><!-- wp:paragraph {"align":"center"} --><p class="has-text-align-center">© Ceramic Pro Mangalore</p><!-- /wp:paragraph --></div>\n<!-- /wp:group -->';
$footer = str_replace('<!-- /wp:columns --><!-- wp:paragraph {"align":"center"}', '<!-- /wp:columns --><p class="cp-bottom-social-strip"><a href="https://www.facebook.com/CeramicProMangalore55/">Facebook</a><a href="https://www.instagram.com/ceramicpro_mangalore">Instagram</a><a href="https://www.youtube.com/channel/UCrOtdAr6t01GwRzqxnEzwPw">Youtube</a><a class="cp-whatsapp" href="https://wa.me/916364268555" aria-label="Chat with us on WhatsApp">WhatsApp</a></p><!-- wp:paragraph {"align":"center"}', $footer);

$header = str_replace('{"align":"full","style":', '{"align":"full","className":"cp-header","style":', $header);
$footer = str_replace('{"align":"full","style":', '{"align":"full","className":"cp-footer","style":', $footer);
$header = str_replace('class="wp-block-group alignfull has-white-color', 'class="wp-block-group alignfull cp-header has-white-color', $header);
$footer = str_replace('class="wp-block-group alignfull has-white-color', 'class="wp-block-group alignfull cp-footer has-white-color', $footer);
$header = str_replace('<!-- /wp:navigation --></div>\n<!-- /wp:column --></div>', '<!-- /wp:navigation --><div class="cp-header-call">' . cp_button('+91 63642 68555', 'tel:+916364268555') . '</div></div>\n<!-- /wp:column --></div>', $header);
$header_logo = ['id' => 0, 'url' => cp_svg('ceramic-logos1.svg')];
$header = str_replace('<!-- wp:site-title {"level":0,"fontSize":"medium"} /-->', cp_image($header_logo['url'], 0, 'Ceramic Pro Mangalore', 'cp-header-logo'), $header);
$footer = str_replace('<!-- wp:site-title {"level":0} /-->', cp_image($header_logo['url'], 0, 'Ceramic Pro Mangalore', 'cp-footer-logo'), $footer);
foreach ([['header', $header, 'header'], ['footer', $footer, 'footer']] as $part) {
    $old = get_page_by_path($part[0], OBJECT, 'wp_template_part');
    $id = wp_insert_post(['ID' => $old ? $old->ID : 0, 'post_type' => 'wp_template_part', 'post_status' => 'publish', 'post_title' => ucfirst($part[0]), 'post_name' => $part[0], 'post_content' => $part[1]], true);
    if (!is_wp_error($id)) {
        wp_set_post_terms($id, ['twentytwentyfive'], 'wp_theme', false);
        update_post_meta($id, 'wp_area', $part[2]);
    }
}

echo 'Created ' . count($page_ids) . " pages and Gutenberg template parts.\n";
