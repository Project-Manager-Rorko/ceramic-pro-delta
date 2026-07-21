# Ceramic Pro Delta

Gutenberg-only local WordPress clone builder for the Ceramic Pro Delta site.

## Build

Set `CERAMIC_WP_ROOT` to an existing WordPress installation, then run:

```sh
export CERAMIC_WP_ROOT="/path/to/wordpress"
php ceramic-build.php
php fix-gutenberg-newlines.php
```

The builder writes pages, template parts, navigation, media references, and global CSS through WordPress and Gutenberg APIs. Start the local site with:

```sh
php -S 127.0.0.1:8080 -t "$CERAMIC_WP_ROOT"
```

The site uses the unmodified Twenty Twenty-Five theme. No custom PHP templates, HTML templates, child theme, or custom plugin files are required. Page content and Site Editor changes are stored in the WordPress database; media files remain in the WordPress uploads directory.
