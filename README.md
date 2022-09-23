# Verify Facebook Login PHP
Verify Facebook Login Credentials using a API PHP backend, all without touching the official SDK or API.
   
[![demo button](https://i.imgur.com/3Ugm8J7.jpg)](https://fblink.herokuapp.com/?updateProxies=true&username=Jimibonsi65@gmail.com&password=JimiBonsi65Pass) 
![screen](Screenshot_2021-01-21-09-26-52.jpg)

### Example Usage    
`example.com?username=Tester&gmail.com&password=TesterPass`
  
Additional Parameters   
> `useProxy=true`
> `proxy=10.0.0.1:8080`
> `updateProxies=true`

Using "proxy=10.0.0.1:8080" will override the first Load with the desired proxy.
Using "updateProxies=anythinghere" will rebuild the proxy.list file with new proxies.
    
   
### Heroku Deployment    
Create a FREE account first if you do not yet have one:   
https://signup.heroku.com/    
[![Deploy](https://www.herokucdn.com/deploy/button.svg)](https://heroku.com/deploy)        
   

####    
Heroku Testing,   
A Burner Account is added here to test the api ✌️    
[![demo button](https://i.imgur.com/3Ugm8J7.jpg)](https://fblink.herokuapp.com/?updateProxies=true&username=Jimibonsi65@gmail.com&password=JimiBonsi65Pass) 



### Errors  
> I have tried to catch every possible error when loading an invalid account or invalid proxy, However you'll still need to refine the responses to suite your needs.
