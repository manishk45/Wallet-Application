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
define( 'AUTH_KEY',          '4OW>L7_o h<^V)Myo);cRa{@=Y V0 8-+bR@m{kRCx~.iVG9(RAp]>*)ZO[ItOdj' );
define( 'SECURE_AUTH_KEY',   'PRQ),)T?qymQRYEf>TGs}pJ<{w}lWY14oL@V>BcnK-GycxTZqfv.W7NL.~@[HwEx' );
define( 'LOGGED_IN_KEY',     'ovB%hER`$LC,MBo:FPn)>s|Gq(I[&V5;1K&:q$F*O!KjyAOy)MT O,$$l-};dTZ ' );
define( 'NONCE_KEY',         'b6eDU:QvM8k+(ti%(upjh*!.uGf]}l,>s<Y;*k3#}>oR(2$?av,x@<#]j/MB G1w' );
define( 'AUTH_SALT',         '|ohU]JVl9:Ja6I=eDW{#MKTzGaC#^&(G<|%M6Fv:H)]I8]1FaC80Q$y}aZ7({$2Y' );
define( 'SECURE_AUTH_SALT',  '|rsUEVVje ta.UX@f<<?U&I|njHM8j,LGO.OM::&6K^<M4xJe|I6*`tvNzd6a)<6' );
define( 'LOGGED_IN_SALT',    '/1$@0CS/$.Z9!t&r?s6lL2%y7[5&|V^fNi]|rQe|7ey0g!J7$H1`(j.voHJ06~Sz' );
define( 'NONCE_SALT',        './t`U:vuv2D1=lQ?Qc3Iv0`WR:ns%.1Wd*%Zp~Mih7:B[:&PI$Pm~.?H-4kJ29sw' );
define( 'WP_CACHE_KEY_SALT', 'uw&@sR}NzY5/W0MS[wJXg7hk$%`K:#%Cg6Y1-lCBj>}>bx;jR63ekd{a#]5Je!5(' );


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
