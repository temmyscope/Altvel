importScripts('https://www.gstatic.com/firebasejs/5.8.4/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/5.8.4/firebase-messaging.js');

//enter the appropriate values
var config = {
    apiKey: "",
    authDomain: "",
    databaseURL: "",
    projectId: "",
    storageBucket: "",
    messagingSenderId: ""
};
firebase.initializeApp(config);
const messaging = firebase.messaging();
messaging.requestPermission().then(function(){
    console.log('Notification permission granted.');
    if(isTokenSentToServer()){ console.log('Token already saved'); }else{ getUserToken(); } 
}).catch(function(err){ console.log('Unable to get permission to notify.', err); });
function getUserToken(){
    messaging.getToken().then(function(currentToken){
      if(currentToken){ setTokenSentToServer(true);
        return currentToken;
      }else{ console.log('No Instance ID token available. Request permission to generate one.'); setTokenSentToServer(false); }
    }).catch(function(err){  console.log('An error occurred while retrieving token. ', err); setTokenSentToServer(false); });
}
function setTokenSentToServer(sent){
    window.localStorage.setItem('sentToServer', sent ? '1' : '0');
}
function isTokenSentToServer(){
    return window.localStorage.getItem('sentToServer') === '1';
}
function saveToken(url, user_id){
    $.post("profile/saveToken", { 'push_token': getUserToken(), 'user_id': user_id },
        function(data){ console.log(currentToken); }).fail(function(xhr, ajaxOptions, thrownError){
        alert(thrownError);
    });
}
messaging.onMessage(function(payload){
	console.log("Message received", payload);
	notificationTitle= payload.data.title;
	notificationOptions= {body: payload.data.body, icon: "" };
	var notification= new Notification(notificationTitle, notificationOptions);
});