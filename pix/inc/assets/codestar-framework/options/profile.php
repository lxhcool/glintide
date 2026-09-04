<?php if ( ! defined( 'ABSPATH' )  ) { die; } // Cannot access directly.

//
// Set a unique slug-like ID
//
$prefix = '_pix_profile_options';

//
// Create profile options
//
CSF::createProfileOptions( $prefix, array(
  'data_type' => 'serialize'
) );


