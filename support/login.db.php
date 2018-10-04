<head>
<script
  src="https://code.jquery.com/jquery-3.3.1.min.js"
  integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8="
  crossorigin="anonymous"></script>
</head>
<?php
// require 'client.inc.php';
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

$servername = "ztidev.com";
$username = "em";
$password = "ZTIDEVzeal1tech";

// Create connection
$conn = new mysqli($servername, $username, $password);
$name = 'em';
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
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
    

    $data_api = check_data_api('admin','123456');
    $get_data = json_decode($data_api,true);
   
    if($get_data['code'] == 'OK'){ 
        $get_email = $get_data['data']['0']['email'];
        $get_pass = $get_data['data']['0']['passWord'];
        
        $sql = "SELECT * FROM em.ost_user_account
        inner join em.ost_user_email on 
        em.ost_user_account.user_id = em.ost_user_email.user_id
        join em.ost_user on em.ost_user.id = em.ost_user_account.user_id 
        where `address` = '$get_email'";

        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            // add condition if have value in db (auto login)
            $row = $result->fetch_assoc();
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
            $get_name = $get_data['data']['0']['firstName']." ".$get_data['data']['0']['lastName'];
            $get_userName = $get_data['data']['0']['userName'];
            $get_pass= $get_data['data']['0']['passWord'];
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
                "7c3e1010311909ce" => "$get_username",
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
        }
    }else if($get_data['code'] == '500'){
        // set response when fail
        echo "no result";
    }
  
  }else{
      // set response when fail
    // echo "No Request token";
  }
  
  
   
?>

<head>
<title>redirect..</title>
</head>
    <form action="login.db.php" method="post" >
        <input type="text" name="token">
        <input type="submit">
    </form>  
    



