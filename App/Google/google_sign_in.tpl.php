{% include 'templates/header.twig' %}

<my-awesome-app></my-awesome-app>

<script src="https://apis.google.com/js/platform.js" async defer></script>
<style>
.g-signout {
  margin-top: 10px;
}
</style>

<div class="g-signin2" data-onsuccess="onSignIn"></div>
<div class="g-signout"><a href="#" onclick="signOut();">Sign out</a></div>

<script>
function onSignIn(googleUser) {
  var profile = googleUser.getBasicProfile();
  console.log('ID: ' + profile.getId()); // Do not send to your backend! Use an ID token instead.
  console.log('Name: ' + profile.getName());
  console.log('Image URL: ' + profile.getImageUrl());
  console.log('Email: ' + profile.getEmail()); // This is null if the 'email' scope is not present.
}

function signOut() {
  var auth2 = gapi.auth2.getAuthInstance();
  auth2.signOut().then(function () {
    console.log('User signed out.');
  });
}
</script>


<script defer type="module">

class MyAwesomeApp extends LitElement {
  render() {
    return html`
      <div><h1>MyAwesomeApp</h1></div>
    `
  }
}
customElements.define('my-awesome-app', MyAwesomeApp)


import {
  LitElement,
  html
} from 'https://unpkg.com/lit-element@2.1.0/lit-element.js?module'

import Navigo from 'https://unpkg.com/navigo@7.1.2/lib/navigo.es.js'

let router = new Navigo('/home/test', true);

console.log('loaded');

router.on('/', function (match) {
  console.log(match)
  console.log('Home')
  // do something
});

router.on('/a', function (match) {
  console.log(match)
  console.log('a no param')
  // do something
});

router.on('/a/:param', function (match) {
  console.log(match)
  console.log('a with param')
  // do something
});

router.on('/b', function (match) {
  console.log(match)
  console.log('b')
  // do something
});

/*
router.on('*', function (match) {
  console.log(match)
  console.log('ALL *')
  // do something
});
*/
router.resolve();

</script>

<a href="/" data-navigo>Home</a>
<a href="/a" data-navigo>A</a>
<a href="/a/bla" data-navigo>A (P)</a>
<a href="/b" data-navigo>B</a>

{% include 'templates/footer.twig' %}