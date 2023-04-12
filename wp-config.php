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
define( 'DB_NAME', 'Test_task' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

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
define( 'AUTH_KEY',         'TZn`)RU/X^;LHR;!do2-XlTq/wpHPKN@xW-{$h&5w{QB(FHX2j{Z{Az6:Ri^C822' );
define( 'SECURE_AUTH_KEY',  'c/*Y$%iHV2OHVu7):V>4~ i0P+bwc|Rq1_Lry31]akBT/t6n&|f6:5T..3A+&>1g' );
define( 'LOGGED_IN_KEY',    'w@a@O_NEOI/wR&S{v00-t*kkqHqp<7:H/+brp/?|Hb`T5/!J`XhoE;Gifs<KMPkB' );
define( 'NONCE_KEY',        '7t5Rui!,{.##-db=q/|3nx]KR/>!ENGYk[enUrHwO}IMWw`t5|JGblEn%4tu`kN~' );
define( 'AUTH_SALT',        '(|:eqg7m% L/d<C4o101]?kz+6(OzEae4xv%S5{^RYu4$I$UlH~hk-qKPeAK)P{W' );
define( 'SECURE_AUTH_SALT', 'OGZ7s3(RuCfw&_?I=@q<4Gcs;_Ey$5&-korSLv-{I]=?ov}dHPW^(hP`^LP97G51' );
define( 'LOGGED_IN_SALT',   '{oX`v_Bt,[>Kd=@%-]IY5$6*6*)7nSGikE~S6.Juz6<@#$/(VsnYK9(~Hd=_3(q/' );
define( 'NONCE_SALT',       'Mnxc=Jm5A9NZ!I$ NIN@!1T0RX>l6)!07tKf/z{?>ZI]iOpF/>Ebjp~6?KB|~Ext' );

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
