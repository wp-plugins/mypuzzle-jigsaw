<?php
/*
Plugin Name: MyPuzzle - Jigsaw
Plugin URI: http://mypuzzle.org/jigsaw/wordpress.html
Description: Include a mypuzzle.org jigsaw Puzzle in your blogs with just one shortcode. 
Version: 1.0.0
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
            'size' => '400',
            'pieces' => '3',
            'rotation' => '1',
            'preview' => '1',
            'bgcolor' => '#ffffff',
            'myimage' => '',
            'showlink' => '0'
            );
	if ($default) {
		update_option('shc_op', $shc_default);
		return $shc_default;
	}
	
	$options = get_option('shc_op');
	if (isset($options))
		return $options;
	update_option('shc_op', $shc_default);
	return $options;
}

/**
 * The Sortcode
 */
 
add_shortcode('jigsaw-mp', 'jigsaw_mp');

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
        $bgcolor = str_replace('#', '$preview', $bgcolor);
        if (!preg_match('/^[a-f0-9]{6}$/i', $bgcolor)) $bgcolor = 'FFFFFF';
        $myimage = $options['myimage'];
        $showlink = $options['showlink'];
        if (!is_numeric($showlink) || !jigsaw_mp_testRange(intval($showlink),0,1)) {$showlink=0;}

	extract(shortcode_atts(array(
                'size' => $size,
                'pieces' => $pieces,
                'rotation' => $rotation,
                'preview' => $preview,
                'bgcolor' => $bgcolor,
                'myimage' => $myimage,
		'showlink' => $showlink,
	), $atts));
        $flash = plugins_url('jigsaw-plugin.swf', __FILE__);
        
        $image = 'ocean-starfish.jpg';
        
        $myJigsaw = new jigsaw_mp_jigsaw();
        if ($myimage != '')
        {
            $myPic = $myJigsaw->getResizedImage($myimage, true);
            $showlink = 1;
        }
        else
            $myPic = $myJigsaw->getResizedImage($image, false);
        //check whether given url was an valid image url
        if (!$myPic) return("Error: Url for <strong>'myimage={$myimage}'</strong> is not an image I can not load! Please change settings.");
        
        $width = $size*1.4;
        $heigth = $size;
        //$myPic = plugins_url('img/', __FILE__).'slide-5x5.jpg';
    
        $output = "<div style='width:".$width."px'>";
        $output .= "<object id='myFlash' classid='clsid:d27cdb6e-ae6d-11cf-96b8-444553540000'";
	$output .= " codebase='http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0'";
	$output .= " width='".+$width."' height='".$heigth."' align='middle'>\r";  
	$output .= "<param name='allowScriptAccess' value='sameDomain' />\r";
	$output .= "<param name='allowFullScreen' value='false' />\r";
	$output .= "<param name='movie' value='".$flash."' />\r";
	$output .= "<param name='flashvars' value='myThumbnail=" . $preview . "&myRot=" . $rotation . "&myPieces=" . $pieces . "&myPic=" . $myPic . "' />\r";
	$output .= "<param name='quality' value='high' />\r";
	$output .= "<param name='menu' value='false' />\r";
	$output .= "<param name='bgcolor' value='#".$bgcolor."' />\r";
	$output .= "<embed src='".$flash."' flashvars='myThumbnail=" . $preview . "myRot=" . $rotation . "&myPieces=" . $pieces . "&myPic=" . $myPic . "' quality='high' bgcolor='#".$bgcolor."'  swLiveConnect='true' ";
	$output .= "    width='".$width."' height='".$heigth."' name='jigsaw' menu='false' align='middle' allowScriptAccess='sameDomain' ";
	$output .= "    allowFullScreen='false' type='application/x-shockwave-flash' pluginspage='http://www.macromedia.com/go/getflashplayer' />\r";
	$output .= "</object>\r";
        $output .= "<br/><a style=\"font-size: 10px\" href=\"http://mypuzzle.org/jigsaw/\">Jigsaw Puzzle</a>";
        $output .= "</div>";
        
        return($output);

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
                
                if ( $options != $newoptions ) {
                        $options = $newoptions;
                        update_option('shc_op', $options);			
                }

 	} 

	if(isset($_POST['Use_Default'])){
        update_option('shc_op', $options);
    }
        $showlink = $options['showlink'];
	$size = $options['size'];
	$pieces = $options['pieces'];
        $rotation = $options['rotation'];
        $image = $options['preview'];
        $bgcolor = $options['bgcolor'];
        $myimage = $options['myimage'];
        
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
                <td width="500">Try 300 then you are not sure.</td>
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
                <td width="200"></td>
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
                <td width="200"></td>
            </tr>
            <tr>
                <td width="100">
                    Show Preview
                </td>
                <td>
                    <select name="preview" id="preview" style="width: 150px">
                            <option value="0"<?php echo ($rotation == 0 ? " selected" : "") ?>><?php echo _e("No") ?></option>
                            <option value="1"<?php echo ($rotation == 1 ? " selected" : "") ?>><?php echo _e("Yes") ?></option>
                    </select>
                </td>
                <td width="200"></td>
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
                    Image Url
                </td>
                <td>
                    <input style="width: 150px" type="text" name="myimage" value="<?php echo ($myimage); ?>">
                </td>
                <td width="500">
                    Only available with link option.
                </td>
            </tr>
            
        </table>
        
        <p class="submit">
            <input type="submit" name="Submit" value="Update" class="button-primary" />
        </p>
        </form>
    </div>


<?php } 

