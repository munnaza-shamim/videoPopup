<?php
    /**
     * Plugin Name:      PictureInPicture

    */
    
    global $wpdb;
    
    define('CXL_DOMAIN', 'popup');
    
    define('CXL_DIR', dirname( __FILE__ ));
    
    define('CXL_SLUG', plugin_dir_url( __FILE__ ));
    
    define('CXL_UPLOAD', wp_upload_dir());
    
    define('CXL_VERSION', '1.0.3');
    
    define('CXL_POPUP', $wpdb->prefix.'cxl_popup_video');
    define('CXL_POPUP_IMPRESSION', $wpdb->prefix.'cxl_popup_impression_video');
    
    include_once('autoload.php');
  
    register_activation_hook(__FILE__, function(){
        if (!file_exists(CXL_UPLOAD['basedir']."/popup-video/")) {
            mkdir(CXL_UPLOAD['basedir']."/popup-video/", 0777, true);
        }
        global $wpdb; 
        require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
        if( $wpdb->get_var( "SHOW TABLES LIKE '".CXL_POPUP."'" ) != CXL_POPUP ) {
            if ( ! empty( $wpdb->charset ) )
                $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
            if ( ! empty( $wpdb->collate ) )
                $charset_collate .= " COLLATE $wpdb->collate"; 
                $sql = "CREATE TABLE " . CXL_POPUP . " (
                        id int(11) NOT NULL auto_increment,
                        title varchar(250) NOT NULL,
                        description LONGTEXT NOT NULL,
                        video varchar(250) NOT NULL,

                        status varchar(15) NOT NULL,
                        PRIMARY KEY (id) 
            ) $charset_collate;";
            dbDelta( $sql ); 
        }
        
        if( $wpdb->get_var( "SHOW TABLES LIKE '".CXL_POPUP_IMPRESSION."'" ) != CXL_POPUP_IMPRESSION ) {
            if ( ! empty( $wpdb->charset ) )
                $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
            if ( ! empty( $wpdb->collate ) )
                $charset_collate .= " COLLATE $wpdb->collate"; 
                $sql = "CREATE TABLE " . CXL_POPUP_IMPRESSION . " (
                        id int(11) NOT NULL auto_increment,
                        pop_id varchar(250) NOT NULL,
                        type varchar(25) NOT NULL,
                        ip varchar(25) NOT NULL,
                        PRIMARY KEY (id) 
            ) $charset_collate;";
            dbDelta( $sql ); 
        }
    });

    add_action('admin_menu', function(){
        add_menu_page('Video PopUp', 'Video PopUp', 'manage_options', 'video-popup_page', function(){
            global $wpdb;
            if(isset($_POST["save_popup"])){
                $title          = $_POST['title'];
                $description    = $_POST['description'];
                $check = $wpdb->get_row("SELECT id FROM `".CXL_POPUP."` WHERE title = '".$title."' AND description = '".$description."'");
                if(empty($check)){
                    $target_dir = CXL_UPLOAD['basedir']."/popup-video/";
                    $video = time().'_'.$_FILES["video"]["name"];
                    if(move_uploaded_file($_FILES["video"]["tmp_name"], $target_dir.$video)){
                        $insert = $wpdb->insert(CXL_POPUP,
                            array(
                                "title" => $title,
                                "description" => $description,
                                "video" => $video,
                                "status" => 'Active'
                            )
                        );
                    }
                    if($insert){
                        zpt_wordpress_admin_notice('success','Popup saved successfully!');
                    }
                    else{
                        zpt_wordpress_admin_notice('error','Problem while saving Popup!');
                    }
                }
                else{
                    zpt_wordpress_admin_notice('success','Popup already exists.');
                }
            }
            if(isset($_POST["update_save_popup"])){
                $id             = $_POST['update_id'];
                $title          = $_POST['update_title'];
                $description    = $_POST['update_description'];
                $update;
                if(!empty($id) && !empty($title) && !empty($description)){
                    if(!empty($_FILES["update_video"]["name"])){
                        $target_dir = CXL_UPLOAD['basedir']."/popup-video/";
                        $video = str_replace("'","_",time().'_'.$_FILES["update_video"]["name"]);
                        if(move_uploaded_file($_FILES["update_video"]["tmp_name"], $target_dir.$video)){
                            $update = $wpdb->update(CXL_POPUP, 
                                array(
                                    "title" => $title,
                                    "description" => $description,
                                    "video" => $video,
                                ),
                                array(
                                    "id" => $id
                                )
                            );
                        }
                    }
                    else{
                        $update = $wpdb->update(CXL_POPUP, 
                            array(
                                "title" => $title,
                                "description" => $description,
                            ),
                            array(
                                "id" => $id
                            )
                        );
                    }
                    if($update){
                        zpt_wordpress_admin_notice('success','Popup update successfully!');
                    }
                    else{
                        zpt_wordpress_admin_notice('error','Problem while updating Popup!');
                    }
                }
                else{
                    zpt_wordpress_admin_notice('success','Please fill all required fields.');
                }
            }
            if(isset($_POST["delete_save_popup"])){
                $id             = $_POST['update_id'];
                if(!empty($id)){
                    $delete = $wpdb->delete(CXL_POPUP, 
                        array(
                            "id" => $id
                        )
                    );
                    if($delete){
                        zpt_wordpress_admin_notice('success','Popup deleted successfully!');
                    }
                    else{
                        zpt_wordpress_admin_notice('error','Problem while deleting Popup!');
                    }
                }
                else{
                    zpt_wordpress_admin_notice('success','Somthing went worng!');
                }
            }
            
            $popups = $wpdb->get_results("SELECT * FROM `".CXL_POPUP."`");
        ?>
            <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet"/>
            <link href="https://cdn.datatables.net/v/bs5/jszip-2.5.0/dt-1.13.4/b-2.3.6/b-colvis-2.3.6/b-html5-2.3.6/b-print-2.3.6/r-2.4.1/datatables.min.css" rel="stylesheet"/>
             
            <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/pdfmake.min.js"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/vfs_fonts.js"></script>
            <script src="https://cdn.datatables.net/v/bs5/jszip-2.5.0/dt-1.13.4/b-2.3.6/b-colvis-2.3.6/b-html5-2.3.6/b-print-2.3.6/r-2.4.1/datatables.min.js"></script>
            
            <div class="container" style="width:100%;max-width:100%;">
              <h2>Popup Listing <button type="button" style="margin-top: 7px;" class="button button-default" data-bs-toggle="modal" data-bs-target="#popups">Add New</a></h2> 
              <table class="table table-condensed table-stripped table-bordered table-hover" id="cxl_courese_result_table" style="width:100%;max-width:100%;">
                <thead>
                  <tr>
                    <th class="text-center">#</th>
                    <th class="text-center">Title</th>
                    <th class="text-center">Description</th>
                    <th class="text-center">Video</th>
                    <th class="text-center">Shortcode</th>
                    <th class="text-center">Impression</th>
                    <th class="text-center">Action Open</th>
                    <th class="text-center">Action Close</th>
                    <th class="text-center">CTR</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Action</th>
                  </tr>
                </thead>
                <tbody>
                    <?php
                        if(!empty($popups)){
                            $count = 1;
                            foreach($popups as $popup){
                                $video = $popup->video ? '<video controls src="'.CXL_UPLOAD["baseurl"].'/popup-video/'.$popup->video.'" style="width: 150px;">' : 'No Video Found';
                               
                                $stauts = $popup->status == 'Active' ? 'checked' : false;
                                echo'<tr>
                                        <td class="text-center">
                                            <span class="hidden">'.$count.'</span>
                                            <input type="checkbox" class="show_hide_popup" value="'.$popup->id.'" '.$stauts.'/>
                                        </td>
                                        <td>'.$popup->title.'</td>
                                        <td>'.$popup->description.'</td>
                                        <td class="text-center">'.$video.'</td>
                                        <td class="text-center"><code>[popup id="'.$popup->id.'"]</code></td>';
                                        $view = $wpdb->get_row("SELECT COUNT(*) as total FROM `".CXL_POPUP_IMPRESSION."` WHERE `pop_id` = '$popup->id' AND `type` = 'view'");
                                        if(!empty($view->total)){
                                            echo '<td class="text-center">'.$view->total.'</td>';
                                        }
                                        else{
                                            echo '<td class="text-center">0</td>';
                                            
                                        }
                                        $action = $wpdb->get_row("SELECT COUNT(*) as total FROM `".CXL_POPUP_IMPRESSION."` WHERE `pop_id` = '$popup->id' AND `type` = 'action'");
                                        if(!empty($action->total)){
                                            echo '<td class="text-center">'.$action->total.'</td>';
                                        }
                                        else{
                                            echo '<td class="text-center">0</td>';
                                            
                                        }
                                        $close = $wpdb->get_row("SELECT COUNT(*) as total FROM `".CXL_POPUP_IMPRESSION."` WHERE `pop_id` = '$popup->id' AND `type` = 'close'");
                                        if(!empty($close->total)){
                                            echo '<td class="text-center">'.$close->total.'</td>';
                                        }
                                        else{
                                            echo '<td class="text-center">0</td>';
                                            
                                        }
                                        if(!empty($action->total) && !empty($view->total)){
                                            echo '<td class="text-center">'.round($action->total/$view->total,2).'%'.'</td>';
                                        }
                                        else{
                                            echo '<td class="text-center">0%</td>';
                                        }
                                        
                                        if($stauts){
                                            echo '<td class="text-center"><span class="badge bg-success">Showing</span></td>';
                                        }
                                        else{
                                            echo '<td class="text-center"><span class="badge bg-danger">Hide</span></td>';
                                        }
                                        echo '<td class="text-center">
                                                <button type="button" class="btn btn-sm btn-success update_button" data-id="'.$popup->id.'" data-title="'.$popup->title.'" data-description="'.$popup->description.'"  data-bs-toggle="modal" data-bs-target=".update_popups">Edit</button>
                                                <button type="button" class="btn btn-sm btn-danger delete_button" data-id="'.$popup->id.'" data-title="'.$popup->title.'" data-bs-toggle="modal" data-bs-target=".delete_popups">Delete</button>
                                            </td>
                                    </tr>';
                                            
                                $count++;
                            }
                        } 
                    ?>
                </tbody>
              </table>
            </div>
            <div class="modal" id="popups">
              <div class="modal-dialog">
                <form class="modal-content modal-primary" method="post" enctype="multipart/form-data">
                  <div class="modal-header">
                    <h4 class="modal-title">Create Popup</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                  </div>
                  <div class="modal-body">
                    <div class="row justify-content-start mb-3">
                        <div class="col-3 col-form-label"><label>Title</label></div>
                        <div class="col-9">
                            <input class="form-control" name="title" type="text" required>
                        </div>
                    </div>
                    <div class="row justify-content-start mb-3">
                        <div class="col-3"><label>Description</label></div>
                        <div class="col-9">
                            <textarea class="form-control" name="description" rows="5" required></textarea>
                        </div>
                    </div>
                    <div class="row justify-content-start mb-3">
                        <div class="col-3 col-form-label"><label>Upload Video</label></div>
                        <div class="col-9">
                            <input  name="video" type="file" required>
                        </div>
                    </div>
                  </div>
                  <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" name="save_popup">Save</button>
                  </div>
                </form>
              </div>
            </div>
            <div class="modal update_popups">
              <div class="modal-dialog">
                <form class="modal-content modal-success" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="update_id">
                  <div class="modal-header">
                    <h4 class="modal-title">Update Popup</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                  </div>
                  <div class="modal-body">
                    <div class="row justify-content-start mb-3">
                        <div class="col-3 col-form-label"><label>Title</label></div>
                        <div class="col-9">
                            <input class="form-control" name="update_title" type="text" required>
                        </div>
                    </div>
                    <div class="row justify-content-start mb-3">
                        <div class="col-3"><label>Description</label></div>
                        <div class="col-9">
                            <textarea class="form-control" name="update_description" rows="5" required></textarea>
                        </div>
                    </div>
                    
                    <div class="row justify-content-start mb-3">
                        <div class="col-3 col-form-label"><label>Upload Video</label></div>
                        <div class="col-9">
                            <input  name="update_video" type="file">
                        </div>
                    </div>
                  </div>
                  <div class="modal-footer">
                    <button type="submit" class="btn btn-success" name="update_save_popup">Update</button>
                  </div>
                </form>
              </div>
            </div>
            <div class="modal delete_popups">
              <div class="modal-dialog">
                <form class="modal-content modal-danger" method="post">
                    <input type="hidden" name="update_id">
                  <div class="modal-header">
                    <h4 class="modal-title">Delete Popup</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                  </div>
                  <div class="modal-body">
                        <p>Are you sure you want to delete <strong class="title"></strong>.</p>
                  </div>
                  <div class="modal-footer">
                    <button type="submit" class="btn btn-danger" name="delete_save_popup">Delete</button>
                  </div>
                </form>
              </div>
            </div>
            <script>
                (function($){
                    $(document).on("click",".update_button", function(){
                        var id      = $(this).data("id");
                        var title   = $(this).data("title");
                        var description   = $(this).data("description");
                        $("input[name='update_id']").val(id);
                        $("input[name='update_title']").val(title);
                        $("textarea[name='update_description']").val(description);
                    });
                    $(document).on("click",".delete_button", function(){
                        var id      = $(this).data("id");
                        var title   = $(this).data("title");
                        $("input[name='update_id']").val(id);
                        $(".title").html(title);
                    });
                    $(document).on('click','.show_hide_popup',function(){
                        var current_row = $(this).closest('tr').find('.badge');
                        var status = $(this).is(":checked") ? true: false;
                        var value  = $(this).val();
                        jQuery.ajax({
                            "type" : "post",
                            "url"  : "<?=admin_url('admin-ajax.php' );?>",
                            "data" : {
                                "action" : "showing_and_hide",
                                "status" : status,
                                "value"  : value,
                            },
                            success : function(response){
                                if(response.status){
                                    if(status){
                                        $(current_row).removeClass('bg-danger').addClass('bg-success');
                                        $(current_row).text('Showing');
                                    }
                                    else{
                                        $(current_row).removeClass('bg-success').addClass('bg-danger');
                                        $(current_row).text('Hide');
                                    }
                                }
                                else{
                                    alert(response.msg);
                                }
                            },
                            error: function(resp){
                            }
                        });
                    });

                 })(jQuery);
            </script>
        <?php
        },'dashicons-bell');
    });
    
    //Wordpress message alert
    if(!function_exists('zpt_wordpress_admin_notice')){
        function zpt_wordpress_admin_notice($type, $msg){
            echo"<div class='notice notice-".$type." is-dismissible'>
                <p>".$msg."</p>
            </div>
            <script>
                jQuery(document).ready(function(){
                    jQuery('#message').remove();
                });
            </script>";
        }
    }
    
    add_action( 'wp_ajax_showing_and_hide', function(){
        global $wpdb;
        $id = $_POST['value'];
        $status = $_POST['status'] == 'true' ? 'Active': 'Deactive';
        $result = array();
        if(!empty($id)){
            $udpate = $wpdb->update(CXL_POPUP, 
                array(
                    "status" => $status
                ),
                array(
                    "id" => $id
                )
            );
            if($udpate){
                $result['status']   = true;
                $result['msg']      = 'Update successfully';
            }
            else{
                $result['status']   = false;
                $result['msg']      = 'Problem while updating';
            }
        }
        else{
            $result['status']   = false;
            $result['msg']      = 'Somthing went wrong please try again!';
        }
        wp_send_json($result);
    });
    
    
    add_action('wp_ajax_impressions','savingimpression');
    add_action('wp_ajax_nopriv_impressions','savingimpression');
    function savingimpression(){
        global $wpdb;
        $type  = $_POST['type'];
        $popid = $_POST['popid'];
        $ip    = $_POST['ip'];
        $result  = array();
        if(!empty($type) && !empty($popid) && !empty($ip)){
            $check = $wpdb->get_row("SELECT id FROM `".CXL_POPUP_IMPRESSION."` WHERE `pop_id` = '".$popid."' AND `type` = '".$type."' AND `ip` = '".$ip."'");
            if(empty($check)){
                $insert = $wpdb->insert(CXL_POPUP_IMPRESSION,
                    array(
                        "pop_id" => $popid,
                        "type" => $type,
                        "ip" => $ip,
                    )
                );
                if($insert){
                    $result['type'] = 'Success';
                    $result['msg'] = 'Saved Successfully.';
                }
                else{
                    $result['type'] = 'Error';
                    $result['msg'] = 'Problem in the backend.';
                }
            }
            else{
                $result['type'] = 'Error';
                $result['msg'] = 'Saved Already.';
            }
        }
        else{
            $result['type'] = 'Error';
            $result['msg'] = 'Something Went Wrong.';
        }
        echo json_encode($result);
    };

//enqueuing the scriptss===============================================================
function my_custom_script(){
$path = plugins_url('assets/myscript.js', __FILE__);
$dep = array('jquery');
$ver = filemtime(plugin_dir_path(__FILE__).'assets/myscript.js');
wp_enqueue_script('myscript', $path, $dep, $ver, true);

}
add_action('wp_enqueue_scripts', 'my_custom_script');

