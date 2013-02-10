var m_width_jigsaw;
var m_height_jigsaw;
var m_flash_jigsaw;
var m_rotation_jigsaw;
var m_preview_jigsaw;
var m_pieces_jigsaw;
var m_bgColor_jigsaw;

var m_chgImage_jigsaw;
var m_myImage_jigsaw;

var m_urlGallery_jigsaw;
var m_dirGallery_jigsaw;
var m_pathGallery_jigsaw;

var m_doResize_jigsaw;
var m_showRestart_jigsaw;
var m_showGallery_jigsaw;
var m_urlResize_jigsaw;
var m_pathResize_jigsaw;
var m_urlResizePath_jigsaw;
var m_siteurl_jigsaw;
var m_anchor_jigsaw;


function jigsaw_pictureChange() {
	var e = document.getElementById("selPicture");
	var sPic = e.value;
	rewriteFlashObject(sPic);
}

function jigsaw_rewriteFlashObject(sPic) {
	var e = document.getElementById("flashObject-jigsaw");
	var s = "";
        s += "<object id='myFlashJigsaw' classid='clsid:d27cdb6e-ae6d-11cf-96b8-444553540000'";
	s += " codebase='http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0'";
	s += " width='"+m_width_jigsaw+"' height='"+m_height_jigsaw+"' align='middle'>";  
	s += "<param name='allowScriptAccess' value='sameDomain' />";
	s += "<param name='allowFullScreen' value='false' />";
	s += "<param name='movie' value='"+m_flash_jigsaw+"' />";
	s += "<param name='flashvars' value='myPic=" + sPic + "&myRot=" + m_rotation_jigsaw + "&myThumbnail=" + m_preview_jigsaw+ "&myPieces=" + m_pieces_jigsaw+"&myRestart=" + m_showRestart_jigsaw+"&myGallery=" + m_showGallery_jigsaw+"' />";
	s += "<param name='quality' value='high' />";
	s += "<param name='menu' value='false' />";
	s += "<param name='bgcolor' value='"+m_bgColor_jigsaw+"' />";
        s += "<param name='wmode' value='transparent' />";
	s += "<embed src='"+m_flash_jigsaw+"' flashvars='myPic=" + sPic + "&myRot=" + m_rotation_jigsaw + "&myThumbnail=" + m_preview_jigsaw + "&myPieces=" + m_pieces_jigsaw+"&myRestart=" + m_showRestart_jigsaw+"&myGallery=" + m_showGallery_jigsaw+"' quality='high' bgcolor='"+m_bgColor_jigsaw+"'  swLiveConnect='true' ";
	s += "    width='"+m_width_jigsaw+"' height='"+m_height_jigsaw+"' name='jigsaw' menu='false' align='middle' allowScriptAccess='sameDomain' ";
	s += "    allowFullScreen='false' type='application/x-shockwave-flash' pluginspage='http://www.macromedia.com/go/getflashplayer' />";
	s += "</object>";	     
        s += "<div style='width:"+m_width_jigsaw+"px;text-align: right;font-size:12px;'><a href='http://mypuzzle.org/jigsaw/'>"+m_anchor_jigsaw+"</a> by mypuzzle.org</div>";
        e.innerHTML = s;
}

function jigsaw_showGallery() {
        
    m_chgImage_jigsaw = false;
    jQuery('#jigsaw_gallery').bPopup({
        onOpen: function() {
            jigsaw_getFlashVars();
            jigsaw_getData();
        },
        onClose: function() { 
            if (m_chgImage_jigsaw==true) {
                jigsaw_rewriteFlashObject(m_myImage_jigsaw); 
            }
        }
    });
}

function jigsaw_getFlashVars() {
    
    m_width_jigsaw = jQuery('#flashvar_width_jigsaw').text();
    m_height_jigsaw = jQuery('#flashvar_height_jigsaw').text();
    m_flash_jigsaw = jQuery('#var_flash_jigsaw').text();
    m_rotation_jigsaw = jQuery('#flashvar_rotation_jigsaw').text();
    m_preview_jigsaw = jQuery('#flashvar_preview_jigsaw').text();
    m_pieces_jigsaw = jQuery('#flashvar_pieces_jigsaw').text();
    m_bgColor_jigsaw = jQuery('#flashvar_bgcolor_jigsaw').text();
    
    m_myImage_jigsaw = jQuery('#flashvar_startPicture_jigsaw').text();
    
    m_urlGallery_jigsaw = jQuery('#var_galleryUrl_jigsaw').text();
    m_dirGallery_jigsaw = jQuery('#var_galleryDir_jigsaw').text();
    m_pathGallery_jigsaw = jQuery('#var_galleryPath_jigsaw').text();
    
    m_doResize_jigsaw = jQuery('#var_doresize_jigsaw').text();
    m_showRestart_jigsaw = jQuery('#var_showrestart_jigsaw').text();
    m_showGallery_jigsaw = jQuery('#var_showgallery_jigsaw').text();
    m_urlResize_jigsaw = jQuery('#var_resizeUrl_jigsaw').text();
    m_pathResize_jigsaw = jQuery('#var_resizePath_jigsaw').text();
    m_urlResizePath_jigsaw = jQuery('#var_resizePathUrl_jigsaw').text();
    m_siteurl_jigsaw = jQuery('#var_siteurl_jigsaw').text();
    m_anchor_jigsaw = jQuery('#var_anchor_jigsaw').text();
    
}


function jigsaw_getData(){
    // getting json data
    var item;
    var sUrl = m_urlGallery_jigsaw+'?dir='+m_dirGallery_jigsaw;
    //console.log(sUrl);
    jQuery.getJSON(sUrl,'callback=?', function(data){
        jQuery('#jigsaw_image_container').empty();
        jQuery.each(data, function(key, val) {
            item = jQuery('#jigsaw_imgWrapTemplate').clone();
            item.attr({'style': ''});
            item.find('.jigsaw_imageTitle').text(key);
            var d = new Date();
            item.find('img').attr('src',m_siteurl_jigsaw + '/' + m_pathGallery_jigsaw+'/'+val);
            
            item.find('img').click(function(){      
                m_myImage_jigsaw = jQuery(this).attr("src");
                //console.log(m_myImage);
                m_chgImage_jigsaw = true;
                if (m_doResize_jigsaw==1) {
                    var imgTitle = jQuery(this).parent().find('.jigsaw_imageTitle').text();
                    //console.log(m_dirGallery_jigsaw+'/'+imgTitle);
                    jigsaw_getResizedImage(m_dirGallery_jigsaw+'/'+imgTitle);
                    
                }
                else
                    jQuery('#jigsaw_gallery').bPopup().close()
                
            });
            jQuery('#jigsaw_image_container').append(item);
        });       
        return('');
    });//end getJson
}// end getData

function jigsaw_getResizedImage(selImage) {
    
    var sUrl = m_urlResize_jigsaw+'?imageUrl='+selImage+'&resizePath='+m_pathResize_jigsaw;
    //console.log(sUrl);
    jQuery.getJSON(sUrl,function(data){
        
        if (data == null) return;

        jQuery.each(data, function(key, val) {
            //console.log(key+"-"+val);
            if (key == 'file') m_myImage_jigsaw = m_urlResizePath_jigsaw + '/' + val;
            //console.log('m_myImage: '+m_myImage);
        });
        jQuery('#jigsaw_gallery').bPopup().close();
    });
}
