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
/** The name of the database for WordPress */
define( 'DB_NAME', 'wordpress' );

/** Database username */
define( 'DB_USER', 'wordpress' );

/** Database password */
define( 'DB_PASSWORD', 'password' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

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
define( 'AUTH_KEY',         'AMT?H8o[nR~Q-Q:1H.xT!]eE%#LU{6%FUE0;$a _.`)KdsJ7)@ZaKVA3C(r5&G3T' );
define( 'SECURE_AUTH_KEY',  '%4VS#io#v>?+v{Ow(3xcd2bG[I6qxQ-ee>`R^@u^?-z#(`XD|caR?{,cXc4$b1f7' );
define( 'LOGGED_IN_KEY',    '`@@]pnC+E5*Q1R++LrixQlN[$wE>=^^|G<g7zQS?!dodI)$*_^A2]%-}!iu@hg`g' );
define( 'NONCE_KEY',        '&T6JH0WH^eFo+L-f%F7Bd.!?m=4M=nnq{CN-`i#R9x{&o,qq-.Xg:mWUq&bwvEBY' );
define( 'AUTH_SALT',        '^qYms><[Cz<b1JtA<UxNR_BzcH]+MM&[dey,9Is[06$KJXkD{9vk?z%EzN| S AX' );
define( 'SECURE_AUTH_SALT', 'v6FuZF31ssSkrQ<3frej08)sE5JNOU%[2 dNgRZM hvMPjWMR-0oI~!veQOtB?tp' );
define( 'LOGGED_IN_SALT',   'yk}BoM.<g.AH[nLz>CPCTL+h04pDCGYU7_|RmBJS?ShnIlIb*Lq1ZZeFlB^0oH/|' );
define( 'NONCE_SALT',       'd<%>gAbLB5PO-v2HXNwb_%XPFZ,E:6NP6,%BF:59EcqramRIDN1_!sfJAjF^>tyg' );

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

