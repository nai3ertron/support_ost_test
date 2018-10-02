<head>


<title>redirect..</title>
</head>
<?php 
require_once('login.db.php');

$get_result = $result;
if(isset($_REQUEST['token'])){
  // echo $_REQUEST['token'];
  $get_token =  $_REQUEST['token'];
  // echo $get_token;

  if ($get_result->num_rows > 0) {
    // output data of each row
    while($row = $get_result->fetch_assoc()) {
       if($row["address"] == $get_token){
          $pass = $row['passwd'];
          echo $pass." ".$row['username'];
       }else{
         echo "no result";
       }
    }
} else {
    echo "0 results";
}
}else{
  // alert("No Request token");
  
}
?>
  <form action="login.mongo.php?token" method="post" >
    <input type="text" name="token">
    <input type="submit">
  </form>  

<?php 

  // function current_url(){
  //  $url = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
  //  $validUrl = str_replace("&","&amp",$url);
  //  return $validUrl; 
  // }
  // echo "url is : ". current_url() ;
?>