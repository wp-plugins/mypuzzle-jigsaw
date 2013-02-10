<?php
/*
Plugin Name: MyPuzzle - Jigsaw
Plugin URI: http://mypuzzle.org/jigsaw/wordpress.html
Description: Include a mypuzzle.org jigsaw Puzzle in your blogs with just one shortcode. 
Version: 1.2.0
Author: tom@mypuzzle.org
Author URI: http://mypuzzle.org/
Notes    : Visible Copyrights and Hyperlink to mypuzzle.org required
*/


/*  Copyright 2012  tom@mypuzzle.org  (email : tom@mypuzzle.org)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

include_once("jigsaw-plugin.php");

/**
 * Default Options
 */
function get_jigsaw_mp_options ($default = false){
	$shc_default = array(
            'size' => '460',
            'pieces' => '4',
            'rotation' => '1',
            'preview' => '1',
            'bgcolor' => '#ffffff',
            'myimage' => '',
            'gallery' => 'wp-content/plugins/mypuzzle-jigsaw/gallery/',
            'temppath' => '',
            'showlink' => '0',
            'doresize' => '0',
            'showrestart' => '1',
            'showgallery' => '0'
            );
	if ($default) {
		update_option('jigsaw_mp_set', $shc_default);
		return $shc_default;
	}
	
	$options = get_option('jigsaw_mp_set');
	if (isset($options))
		return $options;
	update_option('jigsaw_mp_set', $shc_default);
	return $options;
}

/**
 * The Sortcode
 */
add_action('wp_enqueue_scripts', 'jigsaw_mp_jscripts');
add_shortcode('jigsaw-mp', 'jigsaw_mp');


function jigsaw_mp_jscripts() {
    wp_enqueue_script( 'jquery' );
    
    //my jscripts
    wp_register_script('mp-jigsaw-js', plugins_url('/js/jigsaw_plugin.js', __FILE__));
    wp_enqueue_script('mp-jigsaw-js');
    wp_register_script('mp-jigsaw-pop', plugins_url('/js/jquery.bpopup-0.7.0.min.js', __FILE__));
    wp_enqueue_script('mp-jigsaw-pop');
    
    //my styles
    wp_register_style( 'mp-jigsaw-style', plugins_url('/css/jigsaw-plugin.css', __FILE__) );
    wp_enqueue_style( 'mp-jigsaw-style' );
} 

function jigsaw_mp_testRange($int,$min,$max) {     
    return ($int>=$min && $int<=$max);
}

function jigsaw_mp($atts) {
	global $post;
	$options = get_jigsaw_mp_options();	
	
	$size = $options['size'];
        if (!is_numeric($size) || !jigsaw_mp_testRange(intval($size),100,1000)) {$size=300;}
	$pieces = $options['pieces'];
        if (!is_numeric($pieces) || !jigsaw_mp_testRange(intval($pieces),2,20)) {$pieces=3;}
        $rotation = $options['rotation'];
        if (!is_numeric($rotation) || !jigsaw_mp_testRange(intval($rotation),0,1)) {$rotation=1;}
        $preview = $options['preview'];
        if (!is_numeric($preview) || !jigsaw_mp_testRange(intval($preview),0,1)) {$preview=1;}
        $bgcolor = $options['bgcolor'];
       
        $bgcolor = str_replace('#', '', $bgcolor);
        if (!preg_match('/^[a-f0-9]{6}$/i', $bgcolor)) $bgcolor = 'FFFFFF';
        $myimage = $options['myimage'];
        $showlink = $options['showlink'];
        if (!is_numeric($showlink) || !jigsaw_mp_testRange(intval($showlink),0,1)) {$showlink=0;}
        $gallery = $options['gallery'];
        if (!$gallery || $gallery=='') {
            $gallery = 'wp-content/plugins/mypuzzle-jigsaw/gallery';
        } else {
            $gallery = jigsaw_mp_clearpath($gallery);
        }
        $doresize = $options['doresize'];
        $showRestart = $options['showrestart'];
        $showGallery = $options['showgallery'];
        if (!is_numeric($doresize) || !jigsaw_mp_testRange(intval($doresize),0,1)) {$doresize=0;}

	extract(shortcode_atts(array(
                'size' => $size,
                'pieces' => $pieces,
                'rotation' => $rotation,
                'preview' => $preview,
                'bgcolor' => $bgcolor,
                'myimage' => $myimage,
		'gallery' => $gallery,
                'temppath' => $tmpPath,
		'showlink' => $showlink,
                'doresize' => $doresize,
                'showrestart' => $showRestart,
                'showgallery' => $showGallery
	), $atts));
        $flash = plugins_url('jigsaw-plugin.swf', __FILE__);
        $closebuton = plugins_url('img/close_button.png', __FILE__);
        $galleryDir = ABSPATH . $gallery;
        $galleryUrl = plugins_url('getGallery.php', __FILE__);
        $resizeUrl = plugins_url('getresizedImage.php', __FILE__);
        
        $image = 'ocean-starfish.jpg';
        
        $uploadDir = wp_upload_dir();
        $absPath = ABSPATH;
        $rndfile = jigsaw_mp_rndfile($galleryDir);
        if (!$rndfile || $rndfile == '') $rndfile = $image;
        if (!$myimage || $myimage == '')
            $myimage = $gallery.'/'.$rndfile;
        else {
            //check wether its an url or path
            //check wether we deal with an url or an local-path
            $urlar = parse_url($myimage);
            if ($urlar['host']=='') {
                $myimage = jigsaw_mp_clearpath($myimage); 
                $isurl = false;
            }else{
                $isurl = true;
            }  
        }
        if (!$temppath || $temppath == '') {
            $fulltemppath = $uploadDir['path'];
            $fulltempurl = $uploadDir['url'];
        }
        else {
            $fulltemppath = $absPath.'/'.jigsaw_mp_clearpath($temppath);
            $fulltempurl = site_url().'/'.jigsaw_mp_clearpath($temppath);
        }
        $siteurl = site_url();        
               
        $myJigsaw = new jigsaw_mp_jigsaw();
        if ($doresize==1) {
            if ($isurl)
                $myPic = $myJigsaw->getResizedImage($myimage, $fulltemppath, $fulltempurl);
            else
                $myPic = $myJigsaw->getResizedImage($siteurl.'/'.jigsaw_mp_clearpath($myimage), $fulltemppath, $fulltempurl);
            if (!$myPic) 
                return("Error: Could not load/resize the image, please check your upload permission or switch off the resize option.");
        }
        else
            if ($isurl)
                $myPic = $myimage;
            else
                $myPic = site_url() . '/'. $myimage;
        
        $width = $size;
        $heigth = intval($size / 720 * 520);        
    
        $output = "<div id='flashObject-jigsaw' style='z-index:0;'>";
        $output .= "<object id='myFlashJigsaw' classid='clsid:d27cdb6e-ae6d-11cf-96b8-444553540000'";
	$output .= " codebase='http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0'";
	$output .= " width='".$width."' height='".$heigth."' align='middle'>\r";  
	$output .= "<param name='allowScriptAccess' value='sameDomain' />\r";
	$output .= "<param name='allowFullScreen' value='false' />\r";
	$output .= "<param name='movie' value='".$flash."' />\r";
	$output .= "<param name='flashvars' value='myThumbnail=" . $preview . "&myRot=" . $rotation . "&myPieces=" . $pieces . "&myPic=" . $myPic . "&myRestart=" . $showRestart . "&myGallery=" . $showGallery . "' />\r";
	$output .= "<param name='quality' value='high' />\r";
	$output .= "<param name='menu' value='false' />\r";
	$output .= "<param name='bgcolor' value='#".$bgcolor."' />\r";
        $output .= "<param name='wmode' value='transparent' />";
	$output .= "<embed src='".$flash."' flashvars='myThumbnail=" . $preview . "myRot=" . $rotation . "&myPieces=" . $pieces . "&myPic=" . $myPic . "&myRestart=" . $showRestart . "&myGallery=" . $showGallery . "' quality='high' bgcolor='#".$bgcolor."'  swLiveConnect='true' ";
	$output .= "    width='".$width."' height='".$heigth."' name='jigsaw' menu='false' align='middle' allowScriptAccess='sameDomain' ";
	$output .= "    allowFullScreen='false' type='application/x-shockwave-flash' pluginspage='http://www.macromedia.com/go/getflashplayer' />\r";
	$output .= "</object>\r";
        $output .= "<div style=\"width:".$width."px;text-align: right;font-size:12px;\"><a href='http://mypuzzle.org/jigsaw/'>".jigsaw_getRndAnchor()."</a> by mypuzzle.org</div>";
        $output .= "</div>";
        //add diff for the image gallery
        $output .= "<div id='jigsaw_gallery' style='z-index:1;'>\r";
        $output .= "    <span class='jigsaw_button bClose'><img src='".$closebuton."' /></span>\r";
        $output .= "    <div id='jigsaw_image_container' class='jigsaw_scroll-pane'></div>\r";
        $output .= "</div>\r";
        //add diff for the image wrapper template
        $output .= "<div id='jigsaw_imgWrapTemplate' class='jigsaw_imageWrapper' style='visibility:hidden;'>\r"; //
        $output .= "    <img src='' class='jigsaw_pickImage'/>\r";
        $output .= "    <div class='jigsaw_imageTitle'>Turtle</div>\r";
        $output .= "</div>\r";
        //add invisible variables for jquery access
        $output .= "<div id='flashvar_preview_jigsaw' style='visibility:hidden;position:absolute'>".$preview."</div>\r";
        $output .= "<div id='flashvar_rotation_jigsaw' style='visibility:hidden;position:absolute'>".$rotation."</div>\r";
        $output .= "<div id='flashvar_pieces_jigsaw' style='visibility:hidden;position:absolute'>".$pieces."</div>\r";
        $output .= "<div id='flashvar_startPicture_jigsaw' style='visibility:hidden;position:absolute'>".$myPic."</div>\r";
        $output .= "<div id='flashvar_width_jigsaw' style='visibility:hidden;position:absolute'>".$width."</div>\r";
        $output .= "<div id='flashvar_height_jigsaw' style='visibility:hidden;position:absolute'>".$heigth."</div>\r";
        $output .= "<div id='flashvar_bgcolor_jigsaw' style='visibility:hidden;position:absolute'>".$bgcolor."</div>\r";
        $output .= "<div id='var_galleryUrl_jigsaw' style='visibility:hidden;position:absolute'>".$galleryUrl."</div>\r";
        $output .= "<div id='var_galleryDir_jigsaw' style='visibility:hidden;position:absolute'>".$galleryDir."</div>\r";
        $output .= "<div id='var_galleryPath_jigsaw' style='visibility:hidden;position:absolute'>".$gallery."</div>\r";
        $output .= "<div id='var_resizeUrl_jigsaw' style='visibility:hidden;position:absolute'>".$resizeUrl."</div>\r";
        $output .= "<div id='var_resizePath_jigsaw' style='visibility:hidden;position:absolute'>".$fulltemppath."</div>\r";
        $output .= "<div id='var_resizePathUrl_jigsaw' style='visibility:hidden;position:absolute'>".$fulltempurl."</div>\r";
        $output .= "<div id='var_plugin_jigsaw' style='visibility:hidden;position:absolute'>".$gallery."/</div>\r";
        $output .= "<div id='var_flash_jigsaw' style='visibility:hidden;position:absolute'>".$flash."</div>\r";
        $output .= "<div id='var_doresize_jigsaw' style='visibility:hidden;position:absolute'>".$doresize."</div>\r";
        $output .= "<div id='var_showrestart_jigsaw' style='visibility:hidden;position:absolute'>".$showRestart."</div>\r";
        $output .= "<div id='var_showgallery_jigsaw' style='visibility:hidden;position:absolute'>".$showGallery."</div>\r";
        $output .= "<div id='var_anchor_jigsaw' style='visibility:hidden;position:absolute'>".jigsaw_getRndAnchor()."</div>\r";
        $output .= "<div id='var_siteurl_jigsaw' style='visibility:hidden;position:absolute'>".site_url()."</div>\r";
        //add jscript to start gallery from flash
        $output .= "<script language='javascript'>\r";
        $output .= "function jigsaw_openGallery() {jigsaw_showGallery();}\r";
        $output .= "</script>\r";
        
        return($output);

}

function jigsaw_getRndAnchor()
{
    $asKW = array('Jigsaw','Jigsaw','Jigsaw','Jigsaw'
        ,'Jigsaw','Jigsaw', 'Jigsaw', 'Jigsaw Puzzles'
        , 'Jigsaw Puzzles', 'Jigsaw Puzzles', 'Jigsaw Puzzles', 'Jigsaw Puzzles'
        , 'Free Jigsaw Puzzles', 'Free Jigsaw Puzzles', 'Jigsaw Puzzle', 'Jigsaw Puzzle');
    $asHC = array('a', 'b', 'c', 'd', 'e', 'f', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0');        
    $md5Str = strtolower(substr(strval(md5(strtolower($_SERVER['HTTP_HOST']))), 0, 1));    
    $idx = array_search($md5Str, $asHC);
    return($asKW[$idx]);
}


function jigsaw_mp_clearpath($inputpath) {
    if (substr($inputpath, 0, 1)=='/') $inputpath = substr($inputpath, 1);
    if (substr($inputpath, strlen($inputpath)-1, 1)=='/') $inputpath = substr($inputpath, 0, strlen($inputpath)-1);
    return($inputpath);
}

function jigsaw_mp_rndfile($dir) {
    
    if (!is_dir($dir)) return(null);
    if( $checkDir = opendir($dir) ) {
        $cFile = 0;
        // check all files in $dir, add to array listFile
        $preg = "/.(jpg|gif|png|jpeg)/i";
        while( $file = readdir($checkDir) ) {
            if(preg_match($preg, $file)) {
                if( !is_dir($dir . "/" . $file) ) {
                    $listFile[$cFile] = $file;
                    $cFile++;
                }
            }
        }
    }
    $num = rand(0, count($listFile)-1 );
    return($listFile[$num]);
}
/**
 * Settings
 */  

add_action('admin_menu', 'jigsaw_mp_set');

function jigsaw_mp_set() {
	$plugin_page = add_options_page('MyPuzzle Jigsaw', 'MyPuzzle Jigsaw', 'administrator', 'sudoku-jigsaw', 'jigsaw_mp_options_page');		
 }

function jigsaw_mp_options_page() {

	$options = get_jigsaw_mp_options();
	
    if(isset($_POST['Restore_Default']))	$options = get_jigsaw_mp_options(true);	?>

	<div class="wrap">   
	
	<h2><?php _e("MyPuzzle - Jigsaw Puzzle Settings") ?></h2>
	
	<?php 

	if(isset($_POST['Submit'])){
                $newoptions['showlink'] = isset($_POST['showlink'])?$_POST['showlink']:$options['showlink'];
     		$newoptions['size'] = isset($_POST['size'])?$_POST['size']:$options['size'];
     		$newoptions['pieces'] = isset($_POST['pieces'])?$_POST['pieces']:$options['pieces'];
                $newoptions['rotation'] = isset($_POST['rotation'])?$_POST['rotation']:$options['rotation'];
                $newoptions['preview'] = isset($_POST['preview'])?$_POST['preview']:$options['preview'];
                $newoptions['bgcolor'] = isset($_POST['bgcolor'])?$_POST['bgcolor']:$options['bgcolor'];
                $newoptions['myimage'] = isset($_POST['myimage'])?$_POST['myimage']:$options['myimage'];
                $newoptions['gallery'] = isset($_POST['gallery'])?$_POST['gallery']:$options['gallery'];
                $newoptions['temppath'] = isset($_POST['temppath'])?$_POST['temppath']:$options['temppath'];
                $newoptions['doresize'] = isset($_POST['doresize'])?$_POST['doresize']:$options['doresize'];
                $newoptions['showrestart'] = isset($_POST['showrestart'])?$_POST['showrestart']:$options['showrestart'];
                $newoptions['showgallery'] = isset($_POST['showgallery'])?$_POST['showgallery']:$options['showgallery'];
                
                if ( $options != $newoptions ) {
                        $options = $newoptions;
                        update_option('jigsaw_mp_set', $options);
                }

 	} 

	if(isset($_POST['Use_Default'])){
        update_option('jigsaw_mp_set', $options);
    }
        $showlink = $options['showlink'];
        if (!is_numeric($showlink) || !jigsaw_mp_testRange(intval($showlink),0,1)) {$showlink=0;}
	$size = $options['size'];
        if (!is_numeric($size) || !jigsaw_mp_testRange(intval($size),100,1500)) {$size=460;} //to be checked
	$pieces = $options['pieces'];
        if (!is_numeric($pieces) || !jigsaw_mp_testRange(intval($pieces),2,20)) {$pieces=4;}
        $rotation = $options['rotation'];
        if (!is_numeric($rotation) || !jigsaw_mp_testRange(intval($rotation),0,1)) {$rotation=1;}
        $preview = $options['preview'];
        $bgcolor = $options['bgcolor'];
        $bgcolor = str_replace('#', '', $bgcolor);
        if (!preg_match('/^[a-f0-9]{6}$/i', $bgcolor)) $bgcolor = 'FFFFFF';
        $myimage = $options['myimage'];
        $gallery = $options['gallery'];
        $temppath = $options['temppath'];
        $doresize = $options['doresize'];
        $showrestart = $options['showrestart'];
        $showgallery = $options['showgallery'];
        if (!is_numeric($doresize) || !jigsaw_mp_testRange(intval($doresize),0,1)) {$doresize=0;}
        
	?>
        <form method="POST" name="options" target="_self" enctype="multipart/form-data">
	<h3><?php _e("jigsaw Puzzle Parameters") ?></h3>
	
        <table width="" border="0" cellspacing="10" cellpadding="0">
            <tr>
                <td width="100">
                    Size in px
                </td>
                <td>
                    <input style="width: 150px" type="text" name="size" value="<?php echo ($size); ?>">
                    
                </td>
                <td width="500">
                    460 - equates to 460 pixel width for the flash and gives you the best image quality
                </td>
            </tr>
            <tr>
                <td width="50">
                    Pieces count
                </td>
                <td>
                    <select name="pieces" id="pieces" style="width: 150px">
                            <option value="3"<?php echo ($pieces == 3 ? " selected" : "") ?>><?php echo _e("3x3 - 9 pieces") ?></option>
                            <option value="4"<?php echo ($pieces == 4 ? " selected" : "") ?>><?php echo _e("4x4 - 16 pieces") ?></option>
                            <option value="5"<?php echo ($pieces == 5 ? " selected" : "") ?>><?php echo _e("5x5 - 25 pieces") ?></option>
                            <option value="6"<?php echo ($pieces == 6 ? " selected" : "") ?>><?php echo _e("6x6 - 36 pieces") ?></option>
                            <option value="7"<?php echo ($pieces == 7 ? " selected" : "") ?>><?php echo _e("7x7 - 49 pieces") ?></option>
                            <option value="8"<?php echo ($pieces == 8 ? " selected" : "") ?>><?php echo _e("8x8 - 64 pieces") ?></option>
                            <option value="9"<?php echo ($pieces == 9 ? " selected" : "") ?>><?php echo _e("9x9 - 81 pieces") ?></option>
                            <option value="10"<?php echo ($pieces == 10 ? " selected" : "") ?>><?php echo _e("10x10 - 100 pieces") ?></option>
                    </select>
                </td>
                <td width="200">
                    This configures the complexity and amount of overall pieces.
                </td>
            </tr>
            <tr>
                <td width="100">
                    Enable Rotation
                </td>
                <td>
                    <select name="rotation" id="rotation" style="width: 150px">
                            <option value="0"<?php echo ($rotation == 0 ? " selected" : "") ?>><?php echo _e("No") ?></option>
                            <option value="1"<?php echo ($rotation == 1 ? " selected" : "") ?>><?php echo _e("Yes") ?></option>
                    </select>
                </td>
                <td width="200">
                    The pieces can be rotated by mousewheel or the space key.
                </td>
            </tr>
            <tr>
                <td width="100">
                    Show Preview
                </td>
                <td>
                    <select name="preview" id="preview" style="width: 150px">
                            <option value="0"<?php echo ($preview == 0 ? " selected" : "") ?>><?php echo _e("No") ?></option>
                            <option value="1"<?php echo ($preview == 1 ? " selected" : "") ?>><?php echo _e("Yes") ?></option>
                    </select>
                </td>
                <td width="200">
                    Enable or disable a preview of the final image.
                </td>
            </tr>
            <tr>
                <td width="100">
                    Show Restart Button
                </td>
                <td>
                    <select name="showrestart" id="showrestart" style="width: 150px">
                            <option value="0"<?php echo ($showrestart == 0 ? " selected" : "") ?>><?php echo _e("No") ?></option>
                            <option value="1"<?php echo ($showrestart == 1 ? " selected" : "") ?>><?php echo _e("Yes") ?></option>
                    </select>
                </td>
                <td width="200">
                    Shows a button to restart the current puzzle.
                </td>
            </tr>
            <tr>
                <td width="100">
                    Show Gallery Button
                </td>
                <td>
                    <select name="showgallery" id="showgallery" style="width: 150px">
                            <option value="0"<?php echo ($showgallery == 0 ? " selected" : "") ?>><?php echo _e("No") ?></option>
                            <option value="1"<?php echo ($showgallery == 1 ? " selected" : "") ?>><?php echo _e("Yes") ?></option>
                    </select>
                </td>
                <td width="200">
                    Shows the gallery button to enable image selection for the user from the specified or default gallery path.
                </td>
            </tr>
            <tr>
                <td width="100">
                    Background Color
                </td>
                <td>
                    <input style="width: 150px" type="text" name="bgcolor" value="<?php echo ($bgcolor); ?>">
                </td>
                <td width="200">Like #FFFFFF for white.</td>
            </tr>
            <tr>
                <td width="100">
                    Image Url/Path
                </td>
                <td>
                    <input style="width: 150px" type="text" name="myimage" value="<?php echo ($myimage); ?>">
                </td>
                <td width="500">
                    Leave blank to have a random image displayed on page load, or point to a static image.
                </td>
            </tr>
            <tr>
                <td width="100">
                    Path to Gallery
                </td>
                <td>
                    <input style="width: 200px" type="text" name="gallery" value="<?php echo ($gallery); ?>">
                </td>
                <td width="700">
                    Point to your own image directory or leave blank for MyPuzzle Images Gallery. 
                </td>
            </tr>
            <tr>
                <td width="100">
                    Temporary Path
                </td>
                <td>
                    <input style="width: 200px" type="text" name="temppath" value="<?php echo ($temppath); ?>">
                </td>
                <td width="700">
                    Point to a temporary and writable directory for images to be resized. Leave blank for default upload.
                </td>
            </tr>
            <tr>
                <td width="100">
                    Resize
                </td>
                <td>
                    <select name="doresize" id="doresize" style="width: 200px">
                            <option value="0"<?php echo ($doresize == 0 ? " selected" : "") ?>><?php echo _e("Dont resize images") ?></option>
                            <option value="1"<?php echo ($doresize == 1 ? " selected" : "") ?>><?php echo _e("Resize images to fit") ?></option>
                    </select>
                </td>
                <td width="500">
                    Saves a resized copy in the image directory you designated above. 
                </td>
            </tr>
        </table>
        
        <p class="submit">
            <input type="submit" name="Submit" value="Update" class="button-primary" />
        </p>
        </form>
    </div>


<?php } 

