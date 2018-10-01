<?php 
require_once('client.inc.php');

define('OSTCLIENTINC',TRUE);

$_POST['luser'];
$_POST['lpasswd'];

?>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <script
  src="https://code.jquery.com/jquery-3.3.1.min.js"
  integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8="
  crossorigin="anonymous"></script>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
  <!-- <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script> -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
  <title>Document</title>
</head>
<body>
<div id="test">
    <input id="luser" value="<?php echo "admin"; ?>"/>
    <input id="lpasswd" value="<?php  echo "1234"; ?>"/>
</div>

</body>
<script>
  $(document).ready(function () {
    var uName = [];
    var uPass = [];
    $('#test').hide();

    var u = $('#luser').val();
    var p = $('#lpasswd').val();
    console.log(u,p);
    var up = {userName: u, passWord: p}
     
      $.ajax({
          type: 'POST',
          url: 'http://ztidev.com:8080/em/userApi/login?userName='+u+'&passWord='+p+'',
          data: JSON.stringify(up), //'userName="asd"&passWord="asd"',
          success: function(msg,data) {
            console.log(msg['code']);
            if(msg['code'] == "500" ){
                // window.location = "http://localhost/support_ost_test/support/login.php";
                alert('username or password invalid!')
            }else if(msg['code'] == "OK" ){
                console.log(msg['data']["0"].email);
            }
           
          },
          contentType: "application/json",
          dataType: 'json'
      });
  });
</script>