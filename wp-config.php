<?php

/**

 * The base configuration for WordPress

 *

 * The wp-config.php creation script uses this file during the

 * installation. You don't have to use the web site, you can

 * copy this file to "wp-config.php" and fill in the values.

 *

 * This file contains the following configurations:

 *

 * * MySQL settings

 * * Secret keys

 * * Database table prefix

 * * ABSPATH

 *

 * @link https://codex.wordpress.org/Editing_wp-config.php

 *

 * @package WordPress

 */


// ** MySQL settings - You can get this info from your web host ** //

/** The name of the database for WordPress */

define( 'DB_NAME', "hbook" );


/** MySQL database username */

define( 'DB_USER', "root" );


/** MySQL database password */

define( 'DB_PASSWORD', "12345678" );


/** MySQL hostname */

define( 'DB_HOST', "localhost" );


/** Database Charset to use in creating database tables. */

define( 'DB_CHARSET', 'utf8mb4' );


/** The Database Collate type. Don't change this if in doubt. */

define( 'DB_COLLATE', '' );


/**#@+

 * Authentication Unique Keys and Salts.

 *

 * Change these to different unique phrases!

 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}

 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.

 *

 * @since 2.6.0

 */

define( 'AUTH_KEY',         '=Y+,t-29!VkKRs+Rkh2d%@O^?xlR~>Zm~}P87tYwxJ7-SSTPyQ4&@g8Z=X$$*Sg?' );

define( 'SECURE_AUTH_KEY',  '/4S0Cm7@tN&$b^8p1v JbC)*50[oo)/Q7Wl=<l7kLwjE^Q+ubA&k}:d)(QM,(C?R' );

define( 'LOGGED_IN_KEY',    '.Xp-:SV=yZVL0dNmh%H4CWXEOx*0Lo-Oci(ECx=0El(MliQ9~$ t<imI=)#?M>`E' );

define( 'NONCE_KEY',        'pcHxsO`J}Sn+s4970n5TO~No2>G!K(bh},U<g`q-7]rtGP9M=PwUyjp}.j24lAwQ' );

define( 'AUTH_SALT',        'F[*H@(~XI*aRjF&&Gz_gO@3X||IOneAY(o#R*h/v&1~qhdy77h|#a^@8Bl*~lJ<a' );

define( 'SECURE_AUTH_SALT', 'Dk_XZ,W44o^`v #o$sf|sef,yqbVhG[Y9NM@k>ffmt6gSJ?{KsGfO{h0SW|E$h*{' );

define( 'LOGGED_IN_SALT',   '[ et+ [j>3V/n>1V,Ri8|zPP-gXG!.tF*nD]dP$&xqg%/fC1X[t/Xm`$&8Cl1b.T' );

define( 'NONCE_SALT',       's?<Wz@3xKbdWj_rSdi,Uq^eSuY{zD7AYk54d[D#zEA47>SGKdhF.0/nySO.A*p!R' );


/**#@-*/


/**

 * WordPress Database Table prefix.

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

 * visit the Codex.

 *

 * @link https://codex.wordpress.org/Debugging_in_WordPress

 */

define( 'WP_DEBUG', true );


/* That's all, stop editing! Happy publishing. */


/** Absolute path to the WordPress directory. */

if ( ! defined( 'ABSPATH' ) ) {

	define( 'ABSPATH', dirname( __FILE__ ) . '/' );

}


/** Sets up WordPress vars and included files. */

require_once( ABSPATH . 'wp-settings.php' );


