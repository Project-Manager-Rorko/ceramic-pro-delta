<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'local' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', 'root' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',          '69!fv@+5OU`y{z(UTUhkt^~GG@*MY=/g eHYd9qR7O#j/MCpp6o~gAw>h`p;+tD5' );
define( 'SECURE_AUTH_KEY',   '3N1<xaaPjB1T0$qV7&-Xkl^S21BH[7j8LMT{Y;0V7yVk^~PJb@[;K:J.?`Fg{kqw' );
define( 'LOGGED_IN_KEY',     '-Gv&#R )2J,P)n>Wrc*lO3uz)H+A5^*iFGcLpNTf? IH.Y5T!a&Wg{py=3X#8~2)' );
define( 'NONCE_KEY',         '$,eR&C@!mj<QCLPp.hjC^GyVi/J/!o2(~nHovc~g0SI|`HyS7{_%aGmns~oaO/_H' );
define( 'AUTH_SALT',         '%-`z7NhY+AKJc)`1oKcH= /d+/a;)hRo0A8h]VL@iq69U@{9^fQNMz!m<OLs5Vn[' );
define( 'SECURE_AUTH_SALT',  'uDe}Zh.gRwk_B)vD~1:R!xYuv2?GAfx{QB)@9p4;M{^[1!t8JsFe;5wq6>}]>add' );
define( 'LOGGED_IN_SALT',    '?`65@tUL7v<*tc5`w;2G}~;^*X}nf/v,q~&yHvAQQ tu+^}Ww85<UhtJ^6XDb{XK' );
define( 'NONCE_SALT',        '*?=sC^e|2dxB1~$mgh]O_<YJr#^RPe}+/fNP*?q]N%mw5nx3|U3V)Uq#!a JErTY' );
define( 'WP_CACHE_KEY_SALT', 'Gf${.&8aD=fMVI|P1~~Ztjy?M=wzR>P&K7Bi];GU00OZ/1^hf!t8-r< HfhU_]cA' );


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';


/* Add any custom values between this line and the "stop editing" line. */



/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', false );
}

define( 'WP_ENVIRONMENT_TYPE', 'local' );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
