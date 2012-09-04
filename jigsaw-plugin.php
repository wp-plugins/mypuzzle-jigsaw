<?php   

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class jigsaw_mp_jigsaw
{
    function getResizedImage($inputImage, $isurl)
    {
        $extension = end(explode('.', $inputImage));
        $myimage_filename = end(explode('/', $inputImage));

        $image = new jigsaw_mp_simpleImage();
        if (!$isurl)
            $loadDir = plugins_url('img/', __FILE__);
        else
            $loadDir = '';
        //$uploadDir = plugins_url('img/', __FILE__);
        $uploadDir = wp_upload_dir();
        $image->load($loadDir.$inputImage);
        //get sizes
        $height = $image->getHeight();
        $width = $image->getWidth();
        if ($width/$height > (720/520)) 
            $image->resizeToWidth(640);
        else
            $image->resizeToHeight(440);
        if ($isurl)
            $file_name = $myimage_filename;
        else
            $file_name = $inputImage.'.'.$extension;
        $image->save($uploadDir.$file_name);
        return($uploadDir.$file_name);

    }

    function getUniqueCode($length = "") 
    {
        $code = md5(uniqid(rand(), true));
        if ($length != "")
            return substr($code, 0, $length);
        else return $code;
    }

}

class jigsaw_mp_simpleImage {
 
   var $image;
   var $image_type;
 
   function load($filename) {
 
      $image_info = getimagesize($filename);
      $this->image_type = $image_info[2];
      if( $this->image_type == IMAGETYPE_JPEG ) {
 
         $this->image = imagecreatefromjpeg($filename);
      } elseif( $this->image_type == IMAGETYPE_GIF ) {
 
         $this->image = imagecreatefromgif($filename);
      } elseif( $this->image_type == IMAGETYPE_PNG ) {
 
         $this->image = imagecreatefrompng($filename);
      }
   }
   function save($filename, $image_type=IMAGETYPE_JPEG, $compression=75, $permissions=null) {
 
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
      imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());
      $this->image = $new_image;
   }      
 
}

?>
