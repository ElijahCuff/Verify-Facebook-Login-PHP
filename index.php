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

$globCookieFile = "";
// Custom Login Script
function facebookLogin($username, $password, $proxy = "", $useragent = "", $cookieFile = "cookies.txt", $loops = 0)
{
global $globCookieFile;
$globCookieFile = $cookieFile;
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
if(hasParam('useProxy'))
{
curl_setopt($ch, CURLOPT_PROXY, $proxy);
}
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
  $errorMsg="Username OR Password Incorrect\n\n".$results;
      if (contains("is incorrect",$results))
       {
         $errorMsg="Username Correct, Password Failed";
       }
       if (contains("Error",$results))
       {
         $errorMsg="Already Logged In";
         wipeCookies();
       }
}

// CATCH ADD NUMBER PAGE
if (contains("Add a mobile number",$results))
       {
         $errorMsg="Add Number Page Hit";
         wipeCookies();
         return "Login Successful,\nProxy : (".$proxy.")\nUsername : (".$username.")\nPassword : (".$password.")\nMessage : ".$errorMsg;
       } 

// CHECK PROXY RATE LIMIT
if (contains("https://www.facebook.com/help/177066345680802",$results))
 {
       $errorMsg="Rate Limited";
         wipeCookies();
         return "Login Failed,\nProxy : (".$proxy.")\nUsername : (".$username.")\nPassword : (".$password.")\nMessage : ".$errorMsg;
}

// CHECK ACCOUNT DISABLED
if (contains("Your account has been disabled",$results))
 {
  $errorMsg = "Account Disabled";
  wipeCookies();
  return "Login Successful,\nProxy : (".$proxy.")\nUsername : (".$username.")\nPassword : (".$password.")\nMessage : ".$errorMsg;
}

// CHECK ACCOUNT REVIEW
if (contains("We received your informatio",$results))
 {
  $errorMsg = "Account Being Reviewed";
  wipeCookies();
  return "Login Successful,\nProxy : (".$proxy.")\nUsername : (".$username.")\nPassword : (".$password.")\nMessage : ".$errorMsg;
}
// CHECK SUCCESSFUL LOGIN
if (contains("profile picture",$results))
{

// CLEAR SAVED COOKIES ON LOGON
wipeCookies();
// RETURN SUCCESSFUL LOGON
return "Login Successful,\nProxy : (".$proxy.")\nUsername : (".$username.")\nPassword : (".$password.")";

}

// SHOW HTML FOR DEBUGGING ON UNKNOWN ERROR
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

function wipeCookies()
{
  global $globCookieFile;
  file_put_contents($globCookieFile, " ");
}


// RANDOM PROXY FUNCTION
function randomProxy()
{
  $proxies = file('proxy.list');
  return trim($proxies[array_rand($proxies,1)]);
}

// LOAD PROXY INFORMATION FROM PROXY SCRAPE
function updateProxies()
{
  $newProxies = file_get_contents("https://api.proxyscrape.com/v2/?request=getproxies&protocol=http&timeout=5200&country=US&ssl=yes&anonymity=all&simplified=true");
 if (strlen($newProxies) > 0)
   {
  file_put_contents("proxy.list",$newProxies);
  }
}

// OBFUSCATE REQUEST WITH DIFFERENT USER AGENT
function randomAgent()
{
   $agents = file('agent.list');
   return trim($agents[array_rand($agents,1)]);
}

// CHECK POST & GET FOR PARAMETERS
function hasParam($param) 
{
   return array_key_exists($param, $_REQUEST);
}

?>
