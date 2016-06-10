<?php

function pepify($source, $overlay){
  $bottom_path = $source;

  $bottom_image = imagecreatefromjpeg($bottom_path);
  $top_image = imagecreatefrompng($overlay);

  list( $source_width, $source_height, $source_type ) = getimagesize($bottom_path);


  $source_aspect_ratio = $source_width / $source_height;
  $desired_aspect_ratio = 550 / 550;

  if ( $source_aspect_ratio > $desired_aspect_ratio )
  {
    //
    // Triggered when source image is wider
    //
    $temp_height = 550;
    $temp_width = ( int ) ( 550 * $source_aspect_ratio );
  }
  else
  {
    //
    // Triggered otherwise (i.e. source image is similar or taller)
    //
    $temp_width = 550;
    $temp_height = ( int ) ( 550 / $source_aspect_ratio );
  }

  //
  // Resize the image into a temporary GD image
  //

  $temp_gdim = imagecreatetruecolor( $temp_width, $temp_height );
  imagecopyresampled(
    $temp_gdim,
    $bottom_image,
    0, 0,
    0, 0,
    $temp_width, $temp_height,
    $source_width, $source_height
  );

  //
  // Copy cropped region from temporary image into the desired GD image
  //

  $x0 = ( $temp_width - 550 ) / 2;
  $y0 = ( $temp_height - 550 ) / 2;

  $desired_gdim = imagecreatetruecolor( 550, 550 );
  imagecopy(
    $desired_gdim,
    $temp_gdim,
    0, 0,
    $x0, $y0,
    550, 550
  );
  imagesavealpha($top_image, true);
  imagealphablending($top_image, true);
  imagecopy($desired_gdim, $top_image, 0, 0, 0, 0, 550, 550);
  return $desired_gdim;
  ;
}
