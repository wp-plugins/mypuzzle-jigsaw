=== MyPuzzle Jigsaw ===
Contributors: Thomas Seidel
Donate link: http://mypuzzle.org
Tags: jigsaw, mypuzzle, puzzle, jigsaw puzzle, puzzle games, free jigsaw puzzle
Requires at least: 2.5
Tested up to: 3.4.1
Stable tag: 1.1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Fast and easy integration of jigsaw puzzles into your blogs. 

== Description ==

Major features in this version include: 


* New! Use the included MyPuzzle Image Gallery and random startup images
* New! Link your own Image Library
* The jigsaw puzzles can be inserted everywhere: in your theme files, posts and pages.
* Use your own image url and automatically get jigsaw puzzles for them
* many different levels from 2x2 pieces up to 20x20 pieces are available 
* You can change size of the puzzle specifically 
* enable or disable rotation of pieces to make it easier or more complicated
* change background color to fit your themes and layouts
* show or hide the preview for the puzzle result image


For more details and examples visit the plugin page on <a href="http://mypuzzle.org/jigsaw/wordpress.html">Wordpress Jigsaw Plugin</a>

== Installation ==

1. Upload 'Jigsaw Mypuzzle' folder to the '/wp-content/plugins/' directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Go to the MyPuzzle Jigsaw Options page under settings and save your prefered options.
4. Use the Jigsaw code [jigsaw-mp] plus option to insert jigsaw puzzles into your posts/pages.

The <a href="http://mypuzzle.org/jigsaw/">jigsaw puzzles</a> are provided by mypuzzle.org.

== How to use ==

Use the wordpress post shortcut [jigsaw-mp] for default setup or with parameter for individualizing for each post.
Examples:
- [jigsaw-mp]
- [jigsaw-mp size=300 pieces=4 rotation=1 preview=1 bgcolor='#ffffff' myimage='']
- [jigsaw-mp size=400 pieces=3 rotation=1 preview=0 bgcolor='#f4f4f4' myimage='' myimage='http://mysite.domain/img_folder/example.jpg']
- [jigsaw-mp gallery='wp-content/uploads/myimages']
- Or any combination of the above

Visit <a href="http://mypuzzle.org/jigsaw/wordpress.html">Wordpress Jigsaw Plugin</a>

== Screenshots ==

1. Jigsaw MyPuzzle display

== Changelog ==  

 = 1.0.0 =  
 * Initial release.

 = 1.1.0 =  
 * Fixed some upload path handlings and preview-setting not being saved
 * Added MyPuzzle Image Gallery to choose from a variety of images
 * Added Option to link your own image path as gallery input