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

    $key="embypass";
    $token_de = base64_decode($_REQUEST['token']);
    $decrypted_string = openssl_decrypt($token_de,"AES-128-ECB",$key);

    $needle = '&';
    $userName_api = substr($decrypted_string, 0, strpos($decrypted_string, $needle));
    $passWd_api = strstr($decrypted_string, '&');
    $passWd_api = substr($passWd_api,1);
    $data_api = check_data_api($userName_api,$passWd_api);
    $get_data = json_decode($data_api,true);

    if($get_data['code'] == 'OK'){ 
       
        // get role to check 
        $role =  $get_data['data']['0']['userRoles']['0']['roleNameEn'];
       
        if($role == 'Super Admin' || $role == 'Admin' || $role == 'Monitor Office'){        
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
                <form action="scp/login.php" method="post" id="login" style="display:none">
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
                        $('#login').css("display", "none");
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
                    <form action="scp/login.php" method="post" id="login" style="display:none"> 
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
                            $('#login').css("display", "none");
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
                <form action="login.php" method="post" id="clientLogin" style="display:none">
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
                    $('#clientLogin').css("display", "none");
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
                <form action="login.php" method="post" id="clientLogin" style="display:none">
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
                    $('#clientLogin').css("display", "none");
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
    // echo "No Request token!";
  }
  
  
   
?>

<head>
<title>redirect..</title>
</head>
  
    <div class="cs-loader">
        <div class="cs-loader-inner" style=" font-family:Comic Sans MS, cursive, sans-serif;">
            <label>	L</label>
            <label>	O</label>
            <label>	A</label>
            <label>	D</label>
            <label>	I</label>
            <label>	N</label>
            <label>	G</label>
            <label>	.</label>
            <label>	.</label>
        </div>
    </div>


<style>
body {
  margin: 0;
  padding: 0;
  background:#3498db;
}

.cs-loader {
  position: absolute;
  top: 0;
  left: 0;
  height: 100%;
  width: 100%;
}

.cs-loader-inner {
  transform: translateY(-50%);
  top: 50%;
  position: absolute;
  width: calc(100% - 200px);
  color: #FFF;
  padding: 0 100px;
  text-align: center;
}

.cs-loader-inner label {
  font-size: 20px;
  opacity: 0;
  display:inline-block;
}

@keyframes lol {
  0% {
    opacity: 0;
    transform: translateX(-300px);
  }
  33% {
    opacity: 1;
    transform: translateX(0px);
  }
  66% {
    opacity: 1;
    transform: translateX(0px);
  }
  100% {
    opacity: 0;
    transform: translateX(300px);
  }
}

@-webkit-keyframes lol {
  0% {
    opacity: 0;
    -webkit-transform: translateX(-300px);
  }
  33% {
    opacity: 1;
    -webkit-transform: translateX(0px);
  }
  66% {
    opacity: 1;
    -webkit-transform: translateX(0px);
  }
  100% {
    opacity: 0;
    -webkit-transform: translateX(300px);
  }
}

.cs-loader-inner label:nth-child(7) {
  -webkit-animation: lol 3s infinite ease-in-out;
  animation: lol 3s infinite ease-in-out;
}

.cs-loader-inner label:nth-child(9) {
  -webkit-animation: lol 3s 100ms infinite ease-in-out;
  animation: lol 3s 100ms infinite ease-in-out;
}

.cs-loader-inner label:nth-child(8) {
  -webkit-animation: lol 3s 200ms infinite ease-in-out;
  animation: lol 3s 200ms infinite ease-in-out;
}

.cs-loader-inner label:nth-child(7) {
  -webkit-animation: lol 3s 300ms infinite ease-in-out;
  animation: lol 3s 300ms infinite ease-in-out;
}

.cs-loader-inner label:nth-child(6) {
  -webkit-animation: lol 3s 400ms infinite ease-in-out;
  animation: lol 3s 400ms infinite ease-in-out;
}

.cs-loader-inner label:nth-child(5) {
  -webkit-animation: lol 3s 500ms infinite ease-in-out;
  animation: lol 3s 500ms infinite ease-in-out;
}
.cs-loader-inner label:nth-child(4) {
  -webkit-animation: lol 3s 600ms infinite ease-in-out;
  animation: lol 3s 600ms infinite ease-in-out;
}
.cs-loader-inner label:nth-child(3) {
  -webkit-animation: lol 3s 700ms infinite ease-in-out;
  animation: lol 3s 700ms infinite ease-in-out;
}
.cs-loader-inner label:nth-child(2) {
  -webkit-animation: lol 3s 800ms infinite ease-in-out;
  animation: lol 3s 800ms infinite ease-in-out;
}
.cs-loader-inner label:nth-child(1) {
  -webkit-animation: lol 3s 900ms infinite ease-in-out;
  animation: lol 3s 900ms infinite ease-in-out;
}
</style> 

<script>
function showPage() {
  document.getElementById("loader").style.display = "none";
//   document.getElementById("myDiv").style.display = "block";
}
</script>
    



