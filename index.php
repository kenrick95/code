<?php
session_start();
include "auth/config.php";

$loggedIn = false;
$email = '';

if (isset($_SESSION['login_session'], $_COOKIE['login_session_cookie'])) {
    $loggedIn = true;
    $email = $_SESSION['login_session'];
}
if (isset($_SESSION['login_session']) xor isset($_COOKIE['login_session_cookie'])) {
    ob_start();
    include "auth/logout.php";
    ob_end_clean();
}
?><!DOCTYPE html>
<html lang="en">
<meta charset="UTF-8">
<head>
    <title>Code!</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://code-kenrick95.firebaseapp.com/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://code-kenrick95.firebaseapp.com/css/codemirror.css">
    <link rel="stylesheet" href="https://code-kenrick95.firebaseapp.com/css/theme/solarized.css">
    <link rel="stylesheet" href="https://cdn.firebase.com/libs/firepad/1.1.0/firepad.css" />
    <link rel="stylesheet" href="css/style.css">

</head>
<body>
<div id="overlay">
    <div id="loading"></div>
</div>
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
            <a id="brand" class="navbar-brand pointer" data-toggle="modal" data-target="#helpModal">Code!</a>
        </div>

        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
            <ul class="nav navbar-nav">
                <li><a id="share" title="Share" class="pointer dropdown-toggle pointer" data-toggle="dropdown">Share <span class="caret"></span></a>
                <ul class="dropdown-menu keep-open" role="menu">
                    <li class="col-md-5"><small style="white-space: nowrap;">Share this URL to your friend:</small><input type="text" id="share_url" value="..."></li>
                </ul></li>
                <li><a id="export" title="Export" class="pointer" data-toggle="modal" data-target="#exportModal">Export</a></li>
                <!-- <li><a id="compile" title="Compile" class="pointer" data-toggle="modal" data-target="#compileModal">Compile</a></li> -->
                <li><a id="help" title="Help" class="pointer" data-toggle="modal" data-target="#helpModal">Help</a></li>
            </ul>
            <ul class="nav navbar-nav navbar-right">
            <?php
                if ($loggedIn) {
            ?><li class="dropdown" id="user_menu"><a id="username" class="dropdown-toggle pointer" data-toggle="dropdown">
                <?php
                    echo $email;
                ?> <span class="caret"></span></a>
                <ul class="dropdown-menu" role="menu">
                    <li><a id="sign_out" class="pointer">Sign out</a></li>
                </ul>
                </li>
                <?php
                } else {
                ?>
                <li id="user_menu"><a id="sign_in" class="pointer">
                    <img src="img/persona-logo-glyph.png" class="persona_logo"> Sign in
                </a></li>
            <?php
                }
            ?>
        <div id="sidebar">
        <div id="sidebarContainer" class="panel panel-primary">
        <!-- Nav tabs -->
        <ul id="sidebarHeader" class="nav nav-tabs panel-heading" role="tablist">
            <li role="presentation" class="active"><a href="#tabChat" role="tab" data-toggle="tab">Chat</a></li>
            <li role="presentation"><a href="#tabCompile" role="tab" data-toggle="tab">Compile</a></li>
            <li role="presentation"><a href="#tabSettings" role="tab" data-toggle="tab">Settings</a></li>
        </ul>

        <!-- Tab panes -->
        <div id="sidebarBody" class="tab-content panel-body">
            <div role="tabpanel" class="tab-pane active" id="tabChat">
            <!-- CHAT -->
                <div class="panel panel-default">
                <div id="chatPresence" class="panel-body">
                    N people online
                </div>
                <div id="chatHistory" class="list-group">
                    <!-- <div class="chatMessage list-group-item">
                        <div class="chatHeader">
                            [Author] at [time]:
                        </div>
                        <div class="chatBody">Lorem ipsum dolor sit amet</div>
                    </div> -->
                </div>
                <div id="chatField" class="panel-body">
                    <textarea id="chatInput" placeholder="Enter your chat message here" rows="1" class="form-control"></textarea>
                </div>
                </div>
            </div>
            <div role="tabpanel" class="tab-pane" id="tabCompile">
            <!-- INPUT/OUTPUT -->    
                <div class="panel panel-default">
                <div id="compileField" class="panel-body">
                    <p>You can compile and run the current source with Sphere Engine™</p>
                    <p>Standard input: </p>
                    <textarea id="compileInput" name="compileInput" class="code form-control"></textarea>
                    <br>
                    <button type="button" class="btn btn-primary" id="runCompile">Run</button>
                 </div>
                <div id="compileHistory" class="list-group">
                    <!-- <div class="compileMessage list-group-item">
                        <div class="compileHeader">
                            [link] at [time]:
                        </div>
                        <div class="compileBody">
                            Input:
                            <div class="input code"></div>
                            Output:
                            <div class="output code">Hello World!</div>
                        </div>
                    </div> -->
                </div>
                </div>
            </div>
            <div role="tabpanel" class="tab-pane" id="tabSettings">
            <!-- SETTINGS -->

                <div class="panel panel-default">
                <div id="settingsBody" class="panel-body">
                    Configure settings here.
                </div>
                <div id="settingsList" class="list-group">
                    <div class="settings list-group-item">
                        <div class="form-group">
                            <p>Chat:</p>
                            <label for="chatName">Display name</label>
                            <input type="text" class="form-control" id="chatName" placeholder="Name displayed at chat" pattern="[a-zA-Z0-9 ]">
                        </div>
                    </div>
                </div>
                </div>
            </div>
        </div>
        </div>
    </div>
            </ul>



        </div>
    </div>
</nav>
<div id="main" class="container-fluid">
    <textarea id="editor" name="editor" class="code"></textarea>
</div>

<div class="modal fade" id="exportModal" tabindex="-1" role="dialog" aria-labelledby="exportModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="exportModalLabel">Export</h4>
            </div>
            <div id="exportModalBody" class="modal-body">
                <p>You can export the current source to be hosted at GitHub Gist.</p>
                <button type="button" class="btn btn-primary" id="runExport">Export</button>
                <table class="table">
                <thead>
                    <tr>
                        <th>id</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody id="exportTBody">
                </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<?php
// <div class="modal fade" id="compileModal" tabindex="-1" role="dialog" aria-labelledby="compileModalLabel" aria-hidden="true">
//     <div class="modal-dialog">
//         <div class="modal-content">
//             <div class="modal-header">
//                 <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
//                 <h4 class="modal-title" id="compileModalLabel">Compile with Sphere Engine™</h4>
//             </div>
//             <div class="modal-body">
//                 <p>You can compile and run the current source to Ideone.com.</p>
//                 <p>Standard input: </p>
//                 <textarea id="compileInput" name="compileInput" class="code form-control"></textarea>
//                 <br>
//                 <button type="button" class="btn btn-primary" id="runCompile">Run</button>

//                 <table class="table">
//                 <thead>
//                     <tr>
//                         <th>Link</th>
//                         <th>Time</th>
//                         <th>Status</th>
//                     </tr>
//                 </thead>
//                 <tbody id="compileTBody">
//                 </tbody>
//                 </table>
//             </div>
//             <div class="modal-footer">
//                 <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
//             </div>
//         </div><!-- /.modal-content -->
//     </div><!-- /.modal-dialog -->
// </div><!-- /.modal -->
?>
<div class="modal fade" id="helpModal" tabindex="-1" role="dialog" aria-labelledby="helpModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="helpModalLabel">Help</h4>
            </div>
            <div class="modal-body">
                <p>Welcome to <b>Code</b>, a collaborative coding web-based application built to suit your coding needs. Built using CodeMirror with Sublime keymap binding, it serves as a powerful code editor. Moreover, data persistency is achived powered by Firebase and Firepad. The code can be directly run at the cloud via Ideone (Sphere Engine) and exported to GitHub Gist. To enhance the collaboration experience, a chat application is up there. Enjoy your time here and tell it to your friends.</p>
                <p>Do coding at the large area on the left. Do chatting, run codes (via Ideone), or change settings at sidebar on the right. Sign in (via Mozilla Persona) to achieve settings persistency. Export to GitHub Gist by "Export" button. Share the link by copying the URL of the page, or by getting the URL at "Share" button. "Help" button pops this window up.</p>
                <p>If you find this application useful, please consider donating.
                <!-- Donate -->
                <form class="pp-donate" action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank">
                    <input type="hidden" name="cmd" value="_donations">
                    <input type="hidden" name="business" value="kenrick95@gmail.com">
                    <input type="hidden" name="item_name" value="Donation to Kenrick (@kenrick95) for Code!">
                    <input type="hidden" name="no_note" value="0">
                    <button name="submit" class="btn btn-primary"><img src="img/icon_pp.svg" alt="Donate"> Donate</button>
                    <img alt="" border="0" src="https://www.paypalobjects.com/en_GB/i/scr/pixel.gif" style="width:1px;height:1px">
                </form>
                <!-- /.Donate -->
                </p>
                <p>Credits: Code! uses Sphere Engine™ (compile and run), Bootstrap, CodeMirror (syntax highlighting), Firebase and Firepad (real-time sync of code, chat, and presence), Mozilla Persona (identity provider), GitHub Gist API (exporting source code).</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<!-- JavaScripts:
* JQuery
* Bootstrap
* Mozilla Persona
* Codemirror, language-specific highlighter, addon, sublime keymap
* Firepad
-->
<script src="https://code-kenrick95.firebaseapp.com/js/jquery-1.11.1.min.js"></script>
<script src="https://code-kenrick95.firebaseapp.com/js/bootstrap.min.js"></script>
<script src="https://login.persona.org/include.js"></script>
<script src="https://cdn.firebase.com/js/client/2.0.2/firebase.js"></script>

<script src="https://code-kenrick95.firebaseapp.com/js/codemirror.js" type="text/javascript" charset="utf-8"></script>
<script src="https://code-kenrick95.firebaseapp.com/js/mode/clike/clike.js" type="text/javascript" charset="utf-8"></script>
<script src="https://code-kenrick95.firebaseapp.com/addon/search/searchcursor.js"></script>
<script src="https://code-kenrick95.firebaseapp.com/addon/search/search.js"></script>
<script src="https://code-kenrick95.firebaseapp.com/addon/dialog/dialog.js"></script>
<script src="https://code-kenrick95.firebaseapp.com/addon/edit/matchbrackets.js"></script>
<script src="https://code-kenrick95.firebaseapp.com/addon/edit/closebrackets.js"></script>
<script src="https://code-kenrick95.firebaseapp.com/addon/comment/comment.js"></script>
<script src="https://code-kenrick95.firebaseapp.com/addon/wrap/hardwrap.js"></script>
<script src="https://code-kenrick95.firebaseapp.com/addon/fold/foldcode.js"></script>
<script src="https://code-kenrick95.firebaseapp.com/addon/fold/brace-fold.js"></script>
<script src="https://code-kenrick95.firebaseapp.com/js/keymap/sublime.js" type="text/javascript" charset="utf-8"></script>

<script src="https://cdn.firebase.com/libs/firepad/1.1.0/firepad.min.js"></script>

<script>
/*jslint browser: true, sloppy: true*/
/*global CodeMirror, jQuery, $ */
var config = {
    username: <?php if ($loggedIn) { echo '"'. $email . '"'; } else { echo "null"; } ?>,
    avatarUrl: <?php if ($loggedIn) { echo '"http://www.gravatar.com/avatar/'. md5($email) . '.png"'; } else { echo "null"; } ?>,
    hash: null,
    domain: <?php echo "'". $_config['domain']. "'"; ?>,
    url: <?php echo "'". $_config['url']. "'"; ?>
};
</script>
<script src="js/index.js"></script>
</body>
</html>