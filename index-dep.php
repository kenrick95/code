<?php
session_start();

$loggedIn = false;
$email = '';

if (isset($_SESSION['login_session'], $_COOKIE['login_session_cookie'])) {
    $loggedIn = true;
    $email = $_SESSION['login_session'];
}

?><!DOCTYPE html>
<html lang="en">
<meta charset="UTF-8">
<head>
    <title>Code!</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="css/bootstrap.min.css">
<link rel="stylesheet" href="css/codemirror.css">
<link rel="stylesheet" href="css/theme/solarized.css">
<style type="text/css" media="screen">
    @font-face {
      font-family: openSansRegular;
      src: url('fonts/OpenSans-Regular.eot');
      src: url('fonts/OpenSans-Regular.eot?iefix') format('eot'), url('fonts/OpenSans-Regular.woff') format('woff'), url('fonts/OpenSans-Regular.ttf') format('truetype'), url('fonts/OpenSans-Regular.svg#webfont') format('svg');
    }
    @font-face {
      font-family: openSansLight;
      src: url('fonts/OpenSans-Light.eot');
      src: url('fonts/OpenSans-Light.eot?iefix') format('eot'), url('fonts/OpenSans-Light.woff') format('woff'), url('fonts/OpenSans-Light.ttf') format('truetype'), url('fonts/OpenSans-Light.svg#webfont') format('svg');
    }
    @font-face {
      font-family: openSansBold;
      src: url('fonts/OpenSans-Bold.eot');
      src: url('fonts/OpenSans-Bold.eot?iefix') format('eot'), url('fonts/OpenSans-Bold.woff') format('woff'), url('fonts/OpenSans-Bold.ttf') format('truetype'), url('fonts/OpenSans-Bold.svg#webfont') format('svg');
    }
    * {
        padding: 0;
        margin: 0;
    }

    html, body {
        width: 100%;
        height: 100%;
    }
    body {
        padding-top: 50px;
    }
    #header {
        background: #0095DD;
        color: #fff;
        font-family: openSansRegular;
    }
    #header #title {
        font-weight: bold;
    }
    #header a {
        color: #fff;
    }
    #header a:hover {
        background-color: rgba(0,0,0,0.1);
    }
    /* see more colors here http://clrs.cc/ */
    #main {
        height: auto;
    }
    .code {
        font-family: monospace;
    }
    
    .button {
        cursor: pointer;
    }
    #menu {
        display: inline-block;
    }
    #menu>ul {
        display: block;
    }
    #menu>ul>li {
        display: inline-block;
        font-size: 0.7em;
        color: white;
    }
    #menu>ul>li>a {
        color: white;
        text-decoration: none;
        min-width: 4em;
        padding: 0 1em;
        cursor: pointer;
        display: inline-block;
    }
    #menu>ul>li>a:hover {
        background-color: rgba(0, 0, 0, 0.1);
    }
    .persona_logo {
        height: 18px;
    }
</style>
    
</head>
<body>
<nav id="header" class="navbar navbar-inverse navbar-fixed-top" role="navigation">
    <div class="container-fluid">
        <!-- Brand and toggle get grouped for better mobile display -->
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a id="brand" class="navbar-brand" href="#">Code!</a>
        </div>

        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
            <ul class="nav navbar-nav">
                <li><a id="share" title="Share" class="button" onclick="TogetherJS(this); return false" data-end-togetherjs-html="End Share">Share</a></li>
                <li><a id="save" title="Save" class="button" data-toggle="modal" data-target="#saveModal">Save</a></li>
                <li><a id="compile" title="Compile" class="button" data-toggle="modal" data-target="#compileModal">Compile</a></li>
                <li><a id="help" title="Help" class="button" data-toggle="modal" data-target="#helpModal">Help</a></li>
            </ul>
            <ul class="nav navbar-nav navbar-right">
                <li><?php
                    if ($loggedIn) {
                    ?><a id="username">
                    <?php
                        echo $email;
                    ?></a>
                    <!--<a id="sign_out">Sign out</a>-->
                    <?php
                    } else {
                    ?>
                    <a id="sign_in">
                        <img src="img/persona-logo-glyph.png" class="persona_logo"> Sign in
                    </a>
                    <?php
                    }
                    ?></li>
<!--                 <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">Dropdown <span class="caret"></span></a>
                    <ul class="dropdown-menu" role="menu">
                        <li><a href="#">Action</a></li>
                        <li><a href="#">Another action</a></li>
                        <li><a href="#">Something else here</a></li>
                        <li class="divider"></li>
                        <li><a href="#">Separated link</a></li>
                    </ul>
                </li> -->
            </ul>
        </div>
    </div>
</nav>
<div id="main" class="container-fluid">
<textarea id="editor" name="editor" class="code">
#include &lt;iostream&gt;
using namespace std;

int main () {
    cout &lt;&lt; "Hello World!";
    return 0;
}
</textarea>
</div>

<div class="modal fade" id="saveModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="saveModalLabel">Save result</h4>
            </div>
            <div class="modal-body">
                <p>Hmmm, GitHub Gist sounds like a good idea. Later use one paramter to get content from here. Besides source code, also save other things like ideone links, etc.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary">Save changes</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div class="modal fade" id="compileModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="compileModalLabel">Compile with Sphere Engine™</h4>
            </div>
            <div class="modal-body">
                <p>Put input box, for test, keep track of compiled codes also?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary">Save changes</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div class="modal fade" id="helpModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="helpModalLabel">Help</h4>
            </div>
            <div class="modal-body">
                <p>Code! uses Sphere Engine™, Bootstrap, CodeMirror (syntax highlighting), TogetherJS (real-time sync), Mozilla Persona (identity provider), GitHub Gist API, or consider Google Drive Real-time API? https://developers.google.com/drive/realtime/.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary">Save changes</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<!-- JavaScripts:
* JQuery
* Bootstrap
* Mozilla Persona
* Codemirror, language-specific highlighter, addon, sublime keymap
* TogetherJS -->
<script src="js/jquery-1.11.1.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="https://login.persona.org/include.js"></script>

<script src="js/codemirror.js" type="text/javascript" charset="utf-8"></script>
<script src="js/mode/clike/clike.js" type="text/javascript" charset="utf-8"></script>
<script src="addon/search/searchcursor.js"></script>
<script src="addon/search/search.js"></script>
<script src="addon/dialog/dialog.js"></script>
<script src="addon/edit/matchbrackets.js"></script>
<script src="addon/edit/closebrackets.js"></script>
<script src="addon/comment/comment.js"></script>
<script src="addon/wrap/hardwrap.js"></script>
<script src="addon/fold/foldcode.js"></script>
<script src="addon/fold/brace-fold.js"></script>
<script src="js/keymap/sublime.js" type="text/javascript" charset="utf-8"></script>

<script>
/*jslint browser: true, sloppy: true*/
/*global CodeMirror, jQuery, $, TogetherJS */
var config = {
    username: <?php if ($loggedIn) { echo '"'. $email . '"'; } else { echo "null"; } ?>,
    avatarUrl: <?php if ($loggedIn) { echo '"http://www.gravatar.com/avatar/'. md5($email) . '.png"'; } else { echo "null"; } ?>
};
var TogetherJSConfig_siteName = "Code!";
var TogetherJSConfig_toolName = "Code";
var TogetherJSConfig_dontShowClicks = true;
var TogetherJSConfig_disableWebRTC = true;
var TogetherJSConfig_autoStart = false;
var TogetherJSConfig_suppressJoinConfirmation = true;
var TogetherJSConfig_getUserName = function () {return config.username; };
var TogetherJSConfig_getUserAvatar = function () {return config.avatarUrl; };
var TogetherJSConfig_getUserColor = function () {return null; };
</script>
<script src="https://togetherjs.com/togetherjs-min.js"></script>

<script>
/*jslint browser: true, sloppy: true*/
/*global CodeMirror, jQuery, $, TogetherJS */
var myCodeMirror;
function onresize() {
    var cm = myCodeMirror;
    cm.setSize(document.getElementsByTagName("html")[0].offsetWidth - 20, document.getElementsByTagName("html")[0].offsetHeight - document.getElementById("header").offsetHeight - 5);
    cm.refresh();
    //document.getElementById("main").style.height = document.getElementsByTagName("html")[0].offsetHeight - document.getElementById("header").offsetHeight - 5 + "px";
}
$(document).ready(function () {
    myCodeMirror = CodeMirror.fromTextArea(document.getElementById('editor'), {
        theme: "solarized",
        indentUnit: 4,
        lineNumbers: true,
        tabindex: -1,
        autofocus: true,
        keyMap: "sublime",
        showCursorWhenSelecting: true,
        viewportMargin: Infinity
    });

    onresize();
    // TogetherJS
    //TogetherJS();
    //TogetherJS.config("siteName", "Code!");
    TogetherJS.on("form-update", function () {
        console.log("something");
    });

    // Mozilla Persona
    function readCookie(name) {
        var nameEQ = name + "=", ca = document.cookie.split(';'), i;
        for (i = 0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) === ' ') { c = c.substring(1,c.length); }
            if (c.indexOf(nameEQ) === 0) { return c.substring(nameEQ.length,c.length); }
        }
        return null;
    }
    $("#sign_in").click(function () {
        navigator.id.request();
    });
    $("#sign_out").click(function () {
        navigator.id.logout();
    });
    var currentUser = readCookie("login_session_cookie");
    if (currentUser !== null) {
        currentUser = currentUser.replace(/%40/g, "@"); // "@" is encoded as %40 and stored in the cookie
    } else {
        console.log(currntUser);
        navigator.id.logout();
    }
    //console.log(currentUser);

    navigator.id.watch({
        loggedInUser: currentUser,
        onlogin: function(assertion) {
            // A user has logged in! Here you need to:
            // 1. Send the assertion to your backend for verification and to create a session.
            // 2. Update your UI.
            $.ajax({ /* <-- This example uses jQuery, but you can use whatever you'd like */
                type: 'POST',
                url: 'auth/login.php', // This is a URL on your website.
                data: {assertion: assertion},
                success: function (res, status, xhr) { 
                    window.location.reload();
                },
                error: function (xhr, status, err) {
                    navigator.id.logout();
                    console.error("Login failure: " + err);
                }
            });
        },
        onlogout: function() {
            // A user has logged out! Here you need to:
            // Tear down the user's session by redirecting the user or making a call to your backend.
            // Also, make sure loggedInUser will get set to null on the next page load.
            // (That's a literal JavaScript null. Not false, 0, or undefined. null.)
            $.ajax({
                type: 'POST',
                url: 'auth/logout.php', // This is a URL on your website.
                success: function (res, status, xhr) { window.location.reload(); },
                error: function (xhr, status, err) { console.error("Logout failure: " + err); }
            });
        }
    });
});
window.addEventListener("resize", onresize);
/*
TODO
- Save source code to GitHub Gist
https://developer.github.com/v3/gists/
-- or etherpad?
https://pad.riseup.net/api
http://etherpad.org/doc/v1.3.0/
-- or Google Drive Real time API?
https://developers.google.com/drive/realtime/

-- remember to save all the states, including ideone history

- Use ideone API (Sphere Engine™)
-- PHP to proxy the communication
http://ideone.com/sphere-engine

- Tabs, for viewing past input and outputs?

- Save chat history, name, etc?
https://togetherjs.com/docs/#setting-identity-information

- Login via Mozilla Persona
https://developer.mozilla.org/en-US/Persona/Quick_setup
For giving identity to TogetherJS
Or simply ask for identity?

- Improve TogetherJS
-- Support code selection sharing



// OPEN ISSUE OF TOGETHERJS or CodeMirror? https://github.com/mozilla/togetherjs/issues/989
TogetherJS on Event "In"
parse the message
use doc.setCursor and doc.getCursor to fix this

In short, a lot of bug and unimplemented features exists in TogetherJS;
It will be great if they are complete
But right now, many things are broken, like the abovementioned issue, which is quite critical for coding application.

Maybe the best one is to move on from TogetherJS at all
Let's explore Google Drive Realtime API

https://developers.google.com/drive/realtime/realtime-quickstart

 */
</script>
</body>
</html>