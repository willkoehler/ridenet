<?
/*
* File: SimpleImage.php
* Author: Simon Jarvis
* Copyright: 2006 Simon Jarvis
* Date: 08/11/06
* Link: http://www.white-hat-web-design.co.uk/articles/php-image-resizing.php
* 
* This program is free software; you can redistribute it and/or 
* modify it under the terms of the GNU General Public License 
* as published by the Free Software Foundation; either version 2 
* of the License, or (at your option) any later version.
* 
* This program is distributed in the hope that it will be useful, 
* but WITHOUT ANY WARRANTY; without even the implied warranty of 
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the 
* GNU General Public License for more details: 
* http://www.gnu.org/licenses/gpl.html
*
*/
 
class SimpleImage {
   
   var $image;
   var $image_type;

   
   function set($data) {
      $this->image = imagecreatefromstring($data);
      return(true);
   }
   function load($filename) {
      $image_info = getimagesize($filename);
      $this->image_type = $image_info[2];
      if( $this->image_type == IMAGETYPE_JPEG ) {
         $this->image = imagecreatefromjpeg($filename);
      } elseif( $this->image_type == IMAGETYPE_GIF ) {
         $this->image = imagecreatefromgif($filename);
      } elseif( $this->image_type == IMAGETYPE_PNG ) {
         $this->image = imagecreatefrompng($filename);
      } else {
         return(false);
      }
      return(true);
   }
   function save($filename, $image_type=IMAGETYPE_JPEG, $compression=95, $permissions=null) {
      if( $image_type == IMAGETYPE_JPEG ) {
         imagejpeg($this->image,$filename,$compression);
      } elseif( $image_type == IMAGETYPE_GIF ) {
         imagegif($this->image,$filename);         
      } elseif( $image_type == IMAGETYPE_PNG ) {
         imagepng($this->image,$filename);
      }   
      if( $permissions != null) {
         chmod($filename,$permissions);
      }
   }
   function output($image_type=IMAGETYPE_JPEG) {
      if( $image_type == IMAGETYPE_JPEG ) {
         imagejpeg($this->image);
      } elseif( $image_type == IMAGETYPE_GIF ) {
         imagegif($this->image);         
      } elseif( $image_type == IMAGETYPE_PNG ) {
         imagepng($this->image);
      }   
   }
   function getWidth() {
      return imagesx($this->image);
   }
   function getHeight() {
      return imagesy($this->image);
   }
   function resizeToHeight($height) {
      $ratio = $height / $this->getHeight();
      $width = $this->getWidth() * $ratio;
      $this->resize($width,$height);
   }
   function resizeToWidth($width) {
      $ratio = $width / $this->getWidth();
      $height = $this->getheight() * $ratio;
      $this->resize($width,$height);
   }
   function scale($scale) {
      $width = $this->getWidth() * $scale/100;
      $height = $this->getheight() * $scale/100; 
      $this->resize($width,$height);
   }
   function resize($width,$height) {
      $new_image = imagecreatetruecolor($width, $height);
      imagealphablending($new_image, false);    // Perserve alpha channel when resizing
      imagesavealpha($new_image,true);          // Perserve alpha channel when resizing
      imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());
      $this->image = $new_image;   
   }
   function resizeToFit($width, $height) {
      if($width / $this->getWidth() < $height / $this->getHeight())
      {
        $this->resizeToWidth($width);
      }
      else
      {
        $this->resizeToHeight($height);
      }
   }
   function resizeAndCropToFit($width, $height) {
      if($width / $this->getWidth() < $height / $this->getHeight())
      {
          $step1height = $height;
          $ratio = $height / $this->getHeight();
          $step1width = $this->getWidth() * $ratio;
      }
      else
      {
          $step1width = $width;
          $ratio = $width / $this->getWidth();
          $step1height = $this->getheight() * $ratio;
      }
      // resize to fit largest dimenstion
      $step1 = imagecreatetruecolor($step1width, $step1height);
      imagealphablending($step1, false);    // Perserve alpha channel when resizing
      imagesavealpha($step1,true);          // Perserve alpha channel when resizing
      imagecopyresampled($step1, $this->image, 0, 0, 0, 0, $step1width, $step1height, $this->getWidth(), $this->getHeight());
      // crop to fit smallest dimension
      $x_mid = $step1width/2;    //horizontal middle
      $y_mid = $step1height/2;   //vertical middle
      $step2 = imagecreatetruecolor($width, $height);
      imagealphablending($step2, false);    // Perserve alpha channel when resizing
      imagesavealpha($step2,true);          // Perserve alpha channel when resizing
      imagecopyresampled($step2, $step1, 0, 0, $x_mid-$width/2, $y_mid-$height/2, $width, $height, $width, $height);
      $this->image = $step2;
   }
   function getJPEGImageData() {
       ob_start();
       imagejpeg($this->image, NULL, 95);   // default quality of 75 was not good enough for banners
       $data = ob_get_contents();
       ob_end_clean();
       return($data);
   }
   function getPNGImageData() {
       ob_start();
       imagepng($this->image);
       $data = ob_get_contents();
       ob_end_clean();
       return($data);
   }
}
?>