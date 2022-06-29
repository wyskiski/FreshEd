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
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
// ** MySQL settings - You can get this info from your web host ** //
$url = parse_url(getenv('DATABASE_URL') ? getenv('DATABASE_URL') : getenv('CLEARDB_DATABASE_URL'));

/** The name of the database for WordPress */
define('DB_NAME', trim($url['path'], '/'));

/** MySQL database username */
define('DB_USER', $url['user']);

/** MySQL database password */
define('DB_PASSWORD', $url['pass']);

/** MySQL hostname */
define('DB_HOST', $url['host']);

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

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
define('AUTH_KEY',         'AZp@zUkXv;-<0SF(,E>bQ|VO~>3)gF>i-C|2z3MDPTS]m{%67cHuMBO=AV>>QB&)');
define('SECURE_AUTH_KEY',  'm8;L,]_II<tqnvlY0bcH<Qqb]pgl/+sA|VnfhReMXFIp:_|jQd_|VoUFFf6wLdE]');
define('LOGGED_IN_KEY',    '|>W+J+.1c|%Q]+;PdH9 P<Riw?BgJ22~->qjW1#qUd9Jj!^Sc@)$W6YJvAsWv<ya');
define('NONCE_KEY',        'WWNe,$PoKJcOt@o(i}m]Do1a]XB;%&jcZUL0g%4ScMV6^9k3h9d,^acM-69YH@uC');
define('AUTH_SALT',        ')-v]<^C-,^Sg-3w,<[+&ug8wbQ6I`$h2o.EgROq~6>~NE~UTLr{Jx|=8D]uy9.Gn');
define('SECURE_AUTH_SALT', 'D/KAXmnAJ2>Pe!`w[77UKs@Nrbojw9%WqnN|:fX<oPmK+(!tf7g*wQMqB x$ k}H');
define('LOGGED_IN_SALT',   'B)LIvYaVGRt;ZE{x%&Q.iX|bANYiy1u(9E0)+@W-RB< %/azQ}[Q8u71RaB5One)');
define('NONCE_SALT',       '{|`P,`|-X+njZ! dMS&C{^C49>$ATK#jc$n+7f1C.c~%P`#649t*2Bis+P2g$$VD');

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

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
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';

