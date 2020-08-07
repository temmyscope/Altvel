importScripts('https://www.gstatic.com/firebasejs/5.8.4/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/5.8.4/firebase-messaging.js');

/* Copy and paste ur configuration details from your firebase console dashboard into the config variable*/
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
messaging.setBackgroundMessageHandler(function(payload) {
  console.log('[firebase-messaging-sw.js] Received background message ', payload);
  var notificationTitle = payload.data.title;
  var notificationOptions = {
    body: payload.data.body,
    icon: "" /*insert direct link to ur website's icon over the web using https e.g. https://larafell.org/icon.png*/
  };
  return self.registration.showNotification(notificationTitle, notificationOptions);
});
self.addEventListener('install', function(event){
  event.waitUntil(
    caches.open('larafell-offline').then(function(cache){
      return cache.addAll([
        /*enter all the assets i.e. fonts, js, css etc. you'd like to cache*/
      ]);
    }).then(function(){
      return self.skipWaiting();
    })
  );
});
self.addEventListener('fetch', function(event) {
  event.respondWith(
    fetch(event.request).catch(function(error) {
      console.error( 'Network request Failed. Serving offline page ' + error );
      return caches.open('larafell-offline').then(function(cache) {
        return cache.match('offline.html');
      });
    }
  ));
});
self.addEventListener('refreshOffline', function(response) {
  return caches.open('larafell-offline').then(function(cache) {
    console.log('Offline page updated from refreshOffline event: '+ response.url);
    return cache.put(offlinePage, response);
  });
});