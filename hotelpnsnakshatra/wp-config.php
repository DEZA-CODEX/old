<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/documentation/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'velloppw_nakshatra' );

/** Database username */
define( 'DB_USER', 'velloppw_nakshatra' );

/** Database password */
define( 'DB_PASSWORD', '7pm3]S2n0!' );

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
define( 'AUTH_KEY',         'dovgx89epwpaqf5d63mjchjlwoltz29nrm0inppxltfsqtavfp9klyr2rpryqacy' );
define( 'SECURE_AUTH_KEY',  'o4cpqbfwr8zpmcnmckpxxcj6lnzqpvlqs3jwgbwn0z3wceopvchdb507wogiaej8' );
define( 'LOGGED_IN_KEY',    'wdulxkjyabvqtcr303tciuw0egb3x3oyftcuzfz1jeqtbmpaudpdjox7ki14zhfh' );
define( 'NONCE_KEY',        'ivcovbnp1afb8gcmdsgkxvpjrf9elbmb8oginspg6pn6cvyx841janh5mqwolyzc' );
define( 'AUTH_SALT',        'pb4ohqirnud4pzvz3udb0o1lyckf2x8fma4qwrxym7sovvcpzlmkjyse1bkj26wp' );
define( 'SECURE_AUTH_SALT', 'cx9mshufursyku9xp1bqlcznz0qoovywepkbg1rm14vrzivtqqmfrolwclc5vtog' );
define( 'LOGGED_IN_SALT',   'zgosxzaaijjhlrlf4ubivebh5kcufab4i3lyg33tk3antehvkrd6gr1o6bc05rqi' );
define( 'NONCE_SALT',       'd8utgjulpdctosefhjiod6pjzqhjil58t1xbjthgmpiyfeweres3u4vkd2fquj4c' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'nakshatra';

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
 * @link https://wordpress.org/documentation/article/debugging-in-wordpress/
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
