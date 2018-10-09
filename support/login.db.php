<head>
<script
  src="https://code.jquery.com/jquery-3.3.1.min.js"
  integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8="
  crossorigin="anonymous"></script>
</head>
<?php
$thisdir=str_replace('\\', '/', dirname(__FILE__)).'/';
if(!file_exists($thisdir.'main.inc.php')) die('Fatal Error.');

require_once($thisdir.'main.inc.php');
if(!defined('INCLUDE_DIR')) die('Fatal Error');
define('OSTCLIENTINC',TRUE); //make includes happy

require_once(INCLUDE_DIR.'class.user.php');
require_once(INCLUDE_DIR.'class.client.php');
require_once(INCLUDE_DIR.'class.ticket.php');
require_once(INCLUDE_DIR.'class.auth.php');
require_once(INCLUDE_DIR.'class.csrf.php');
require_once(INCLUDE_DIR.'class.staff.php');

$servername = "ztidev.com";
$username = "em";
$password = "ZTIDEVzeal1tech";

// Create connection
$conn = new mysqli($servername, $username, $password);
$name = 'em';
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
    return;
}

// call api
function check_data_api($username,$passwd){
    $url = 'http://ztidev.com:8080/em/userApi/login?';
    $post_login = 'userName=' . $username . '&passWord=' . $passwd;
    
    $ch = curl_init( $url );
    curl_setopt( $ch, CURLOPT_POST, 1);
    curl_setopt( $ch, CURLOPT_POSTFIELDS, $post_login);
    curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt( $ch, CURLOPT_HEADER, 0);
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);
    
    return $response = curl_exec( $ch );
}   


if(isset($_REQUEST['token'])){
    //require token get username & pass decode 
    
    $userName_api = "Central";
    $passWd_api = "123456";
    $data_api = check_data_api($userName_api,$passWd_api);
    $get_data = json_decode($data_api,true);
    
    if($get_data['code'] == 'OK'){ 
       
        // get role to check 
        $role =  $get_data['data']['0']['userRoles']['0']['roleNameEn'];
       
        if($role == 'Central Officer'){        
            $get_userName_staff = $get_data['data']['0']['userName'];
            $get_email_staff = $get_data['data']['0']['email'];
            $get_pass= $get_data['data']['0']['passWord'];

            $sql = "SELECT * FROM em.ost_staff 
                    WHERE `username` = '$get_userName_staff' AND `email` = '$get_email_staff' ";

            $result = $conn->query($sql);
            $row = mysqli_fetch_assoc($result);
            $staff_id = $row['staff_id'];
            
            if ($result->num_rows > 0) {
?>
                <form action="scp/login.php" method="post" id="login">
                    <?php csrf_token(); ?>
                    <input type="hidden" name="do" value="scplogin">
                    <input type="text" name="userid" id="name" value="<?php
                        echo $get_userName_staff; ?>" placeholder="<?php echo __('Email or Username'); ?>"
                        autofocus autocorrect="off" autocapitalize="off">
                    <input type="password" name="passwd" id="pass" value="<?php echo $get_pass ?>" placeholder="<?php echo __('Password'); ?>" autocorrect="off" autocapitalize="off">
                </form>
                <script >
                    $(document).ready(function() {
                        // set auto summit form
                        $('#login').hide();
                        $('#login').submit();
                        showPage();
                    }); 
                </script>
<?php   
            }else{
                    //  else num_row < 0 : insert account staff
                    
                    $get_fname_staff = $get_data['data']['0']['firstName'];
                    $get_lname_staff = $get_data['data']['0']['lastName'];
                    $get_pass= $get_data['data']['0']['passWord'];
                    $get_tel_staff= $get_data['data']['0']['tel']['0'];
                    $get_mobile_staff= $get_data['data']['0']['tel']['1'];
        
                    $post_staff = array(
                            "__CSRFToken__" => "0a36f3e3c1dff412473a1180b2e2b08c89bb5fbb",
                            "do" => "create",
                            "a" => "add",
                            "id" => "",
                            "firstname" => "$get_fname_staff",
                            "lastname" => "$get_lname_staff",
                            "email" => "$get_email_staff",
                            "phone" => "$get_tel_staff",
                            "phone_ext" => "",
                            "mobile" => "$get_mobile_staff",
                            "username" => "$get_userName_staff",
                            "backend" => "",
                            "passwd1" => "$get_pass",
                            "cpasswd" => "$get_pass",
                            "notes" => "",
                            "dept_id" => 1,
                            "role_id" => 1,
                            "assign_use_pri_role" => "on",
                            "perms" => array(
                                    "0" => "user.create",
                                    "1" => "user.edit",
                                    "2" => "user.delete",
                                    "3" => "user.manage",
                                    "4" => "user.dir",
                                    "5" => "org.create",
                                    "6" => "org.edit",
                                    "7" => "org.delete",
                                    "8" => "faq.manage",
                                ),
                            "submit" => "Create",
                        );
                                            
                    $staff = Staff::create();
                    $staff->update($post_staff,$errors);

                    $sql = "SELECT * FROM em.ost_staff 
                    WHERE `username` = '$get_userName_staff' AND `email` = '$get_email_staff' ";

                    $result = $conn->query($sql);
                    $row = mysqli_fetch_assoc($result);
                    $staff_id = $row['staff_id'];

                    $clean= array(
                        "welcome_email" => "0",
                        "passwd1" => "$get_pass",
                        "passwd2" => "$get_pass",
                        "change_passwd" => "0"
                    );

                    $staff_p = Staff::lookup($staff_id);
                    $staff_p->setPassword($clean['passwd1'], null);
                    $staff_p->save();
?>
                    <!-- login staff -->
                    <form action="scp/login.php" method="post" id="login">
                        <?php csrf_token(); ?>
                        <input type="hidden" name="do" value="scplogin">
                        <input type="text" name="userid" id="name" value="<?php
                            echo $get_userName_staff; ?>" placeholder="<?php echo __('Email or Username'); ?>"
                            autofocus autocorrect="off" autocapitalize="off">
                        <input type="password" name="passwd" id="pass" value="<?php echo $get_pass ?>" placeholder="<?php echo __('Password'); ?>" autocorrect="off" autocapitalize="off">
                    </form>
                    <script >
                        $(document).ready(function() {
                            // set auto summit form
                            $('#login').hide();
                            $('#login').submit();
                            showPage();
                        }); 
                    </script>
<?php
            }
        }else{

            $get_email = $get_data['data']['0']['email'];
            $get_pass = $get_data['data']['0']['passWord'];
            $get_name = $get_data['data']['0']['firstName']." ".$get_data['data']['0']['lastName'];

            $sql = "SELECT * FROM em.ost_user_account
            inner join em.ost_user_email on 
            em.ost_user_account.user_id = em.ost_user_email.user_id
            join em.ost_user on em.ost_user.id = em.ost_user_account.user_id 
            where `address` = '$get_email' and `name` = '$get_name'";
    
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                // login client
                ?>
                <form action="login.php" method="post" id="clientLogin">
                <?php csrf_token(); ?>
                    <div>
                        <input id="username" placeholder="<?php echo __('Email or Username'); ?>" type="text" name="luser" size="30" value="<?php echo $get_email; ?>" class="nowarn">
                    </div>
                    <div>
                        <input id="passwd" placeholder="<?php echo __('Password'); ?>" type="password" name="lpasswd" size="30" value="<?php echo $get_pass; ?>" class="nowarn"></td>
                    </div>
                    <input class="btn" type="submit" value="<?php echo __('Sign In'); ?>">
                </form>
                <script >
                $(document).ready(function() {
                    $('#clientLogin').hide();
                    $('#clientLogin').submit();
                }); // SUBMIT FORM
                </script>
            <?php
                
            } else { 
                // else num_row < 0 : insert account client
                $get_name = $get_data['data']['0']['firstName']." ".$get_data['data']['0']['lastName'];
                $get_userName = $get_data['data']['0']['userName'];
                $get_pass = $get_data['data']['0']['passWord'];

                
                $obj = array(
                    "email" => "$get_email",
                    "name" => "$get_name",
                    "username" => "$get_userName",
                    "backend" => "",
                    "org_id"=>"",
                    "passwd1"=>"$get_pass",
                    "passwd2"=>"$get_pass",
                    "timezone"=>"Asia/Jakarta",
                );
                $obj_post = array(
                    "__CSRFToken__" => "1dc3c1fb54de387ae06d7163e87afb334470c2b5",
                    "do" => "create",
                    "b78b1edfee165553" => "$get_email",
                    "7c3e1010311909ce" => "$get_name",
                    "49c560939e55b452"=>"",
                    "29ff59d1f5db7bed" => "",
                    "29ff59d1f5db7bed-ext"=>"",
                    "passwd1"=>"$get_pass",
                    "passwd2"=>"$get_pass",
                    "timezone"=>"Asia/Jakarta",
                );
    
                $regist =  User::fromVars($obj);
                $acct = ClientAccount::createForUser($regist);
                $acct->confirm();
                $acct->update($obj_post, $errors);
                // go login when success to add data
                ?> 
                <form action="login.php" method="post" id="clientLogin">
                <?php csrf_token(); ?>
                    <div>
                        <input id="username" placeholder="<?php echo __('Email or Username'); ?>" type="text" name="luser" size="30" value="<?php echo $get_email; ?>" class="nowarn">
                    </div>
                    <div>
                        <input id="passwd" placeholder="<?php echo __('Password'); ?>" type="password" name="lpasswd" size="30" value="<?php echo $get_pass; ?>" class="nowarn"></td>
                    </div>
                    <input class="btn" type="submit" value="<?php echo __('Sign In'); ?>">
                </form>
                <script >
                $(document).ready(function() {
                    // set auto summit form
                    $('#clientLogin').hide();
                    $('#clientLogin').submit();
                }); 
                </script>
                
                <?php
            }  
        } //else role
    }else if($get_data['code'] == '500'){
        // set response when fail
        echo "data not found!";
    }      
  }else{
      // set response when fail
    echo "No Request token!";
  }
  
  
   
?>

<head>
<title>redirect..</title>
</head>
    <form action="login.db.php" method="post" >
        <input type="text" name="token">
        <input type="submit">
    </form> 
    <span class="loader" style="margin-top:16%;">
        <span class="loader-inner"></span>
    </span>


<style>
 body, html {
    height: 100%;
    text-align: center;
}

body {
  background-color: #242F3F;
}

.loader {
  display: inline-block;
  width: 30px;
  height: 30px;
  position: relative;
  border: 4px solid #Fff;
  top: 50%;
  animation: loader 2s infinite ease;
}

.loader-inner {
  vertical-align: top;
  display: inline-block;
  width: 100%;
  background-color: #fff;
  animation: loader-inner 2s infinite ease-in;
}

@keyframes loader {
  0% {
    transform: rotate(0deg);
  }
  
  25% {
    transform: rotate(180deg);
  }
  
  50% {
    transform: rotate(180deg);
  }
  
  75% {
    transform: rotate(360deg);
  }
  
  100% {
    transform: rotate(360deg);
  }
}

@keyframes loader-inner {
  0% {
    height: 0%;
  }
  
  25% {
    height: 0%;
  }
  
  50% {
    height: 100%;
  }
  
  75% {
    height: 100%;
  }
  
  100% {
    height: 0%;
  }
}
</style> 

<script>
function showPage() {
  document.getElementById("loader").style.display = "none";
//   document.getElementById("myDiv").style.display = "block";
}
</script>
    



