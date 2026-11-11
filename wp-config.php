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
define( 'DB_NAME', 'portfolio' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

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
define( 'AUTH_KEY',          'm(C|$-jta$^Q;M<a^&xrVuPcI6{<YX1D,:1z-_RbSdP|r_gE}iM+CJ9G7`U BW~9' );
define( 'SECURE_AUTH_KEY',   'cW6(s]0i5oU*X: u3w<=xD?/KNwGiaDpU8ZJ81X/!|d|r8-5]/O}MU!,)k.}hNaO' );
define( 'LOGGED_IN_KEY',     '*zEyEa-6T(Aa&Q%yHV~8*=8#K2&XM!4oms|]w83amy=f~7QJz-;)GndkXq],T^Rq' );
define( 'NONCE_KEY',         'SZ-6oK7feDxbU6mw%OI&-BCR.598o>hAIrhlh6ieqxaynb4C}Cd002mik&TmIN1;' );
define( 'AUTH_SALT',         'BwtZxfexLn$(:.B@t(;l9PG0YI5mrHvK5~%$|3U5.o0y5]AJ3)r24HnU?%L[JGdc' );
define( 'SECURE_AUTH_SALT',  '<Al|RT?yNMM7$6~pwGoc`5~?9NCuCAw?;W=&]$XFJ]f`PC>cvEVMvu~YUa&;%H5%' );
define( 'LOGGED_IN_SALT',    ')!S_L}XYO&QHFI3>vM19x==vhHa_ 7(m<^uLcU$3W!_s NwE/Nd/CcsylPP]Uu}K' );
define( 'NONCE_SALT',        'IhET2a9Izh;mWTE?v/a^U[%9ugZAAKI>a_|wJ^TuKab|;s,b}b$e#Jr,QroD5=-^' );
define( 'WP_CACHE_KEY_SALT', '[cH(JKR]z!3NK=!;>>..W6OVo4Dr;xJ1yE3~C+~+{59HsX7cDH7NbS`g]]BJY<aN' );


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

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
