/*jslint browser: true, sloppy: true, plusplus: true */
/*global CodeMirror, jQuery, $, Firepad, Firebase, config */
var codeMirror;
var firepadRef;
var firepad;
var userRef;
var firepadUser;

$(document).ready(function () {
    function createCookie(name, value, days) {
        var date, expires;
        if (days) {
            date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            expires = "; expires=" + date.toGMTString();
        } else { expires = ""; }
        document.cookie = name + "=" + value + expires + "; path=/";
    }
    function readCookie(name) {
        var nameEQ = name + "=", ca = document.cookie.split(';'), i, c;
        for (i = 0; i < ca.length; i++) {
            c = ca[i];
            while (c.charAt(0) === ' ') { c = c.substring(1, c.length); }
            if (c.indexOf(nameEQ) === 0) { return c.substring(nameEQ.length, c.length); }
        }
        return null;
    }
    var loggedInUser = false;

    //CodeMirror
    function onresize() {
        var cm = codeMirror;
        cm.setSize(document.getElementsByTagName("html")[0].offsetWidth - 20, document.getElementsByTagName("html")[0].offsetHeight - document.getElementById("header").offsetHeight - 5);
        cm.refresh();

        //$("#sidebar").height($(".CodeMirror").height());
        //$("#sidebarContainer").height($("#sidebar").height());
        //$("#sidebarBody").height($(".CodeMirror").outerHeight() - $("#sidebarHeader").outerHeight() - 30);
        $("#sidebarBody").height($(".CodeMirror").outerHeight() - 30);
        $("#chatHistory").height($("#sidebarBody").height() - $("#chatField").outerHeight());
        $("#compileHistory").height($("#sidebarBody").height() - $("#compileField").outerHeight());
    }
    codeMirror = CodeMirror.fromTextArea(document.getElementById('editor'), {
        theme: "solarized",
        indentUnit: 4,
        lineNumbers: true,
        tabindex: -1,
        autofocus: true,
        keyMap: "sublime",
        mode: "text/x-c++src",
        matchBrackets: true,
        showCursorWhenSelecting: true,
        viewportMargin: Infinity
    });

    //Firepad
    function getRef() {
        var ref = new Firebase('https://code-kenrick95.firebaseio.com/'),
            hash = window.location.hash.replace(/#/g, '');
        if (hash) {
            ref = ref.child(hash);
        } else {
            ref = ref.push(); // generate unique location.
            //window.location = window.location + '#' + ref.key(); // add it as a hash to the URL.
            history.pushState({hash: hash}, "", window.location + '#' + ref.key());
        }
        config.hash = hash;
        return ref;
    }
    firepadRef = getRef();

    var currentUser = readCookie("login_session_cookie");
    if (currentUser !== null) {
        loggedInUser = true;
        currentUser = currentUser.replace(/%40/g, "@"); // "@" is encoded as %40 and stored in the cookie
    } else {
        userRef = firepadRef.child("users").push();
        currentUser = userRef.key();
    }
    console.log(currentUser);

    //Firepad
    var defaultSource = '#include <iostream>'
        + '\nusing namespace std;'
        + '\n'
        + '\nint main () {'
        + '\n    cout << "Hello World!";'
        + '\n    return 0;'
        + '\n}';
    firepadUser = currentUser;
    if (currentUser !== null) {
        firepadUser = currentUser.replace(/\./g, "dot");
        //firepad.setUserId(firepadUser);
        firepadRef.child("users/" + firepadUser).once("value", function (snapshot) {
            console.log(snapshot.val());
        });
    }
    firepad = Firepad.fromCodeMirror(firepadRef, codeMirror,
        {defaultText: defaultSource, userId: firepadUser });
    firepad.on('ready', function () {
        //firepad.setText(defaultSource);
        // codeMirror.setOption("extraKeys", {
        //     'Ctrl-Z': function () {
        //         firepad.undo();
        //         console.log("a");
        //     },
        //     'Ctrl-Y': function () {
        //         firepad.redo();
        //         console.log("b");
        //     }
        // });
        $("#share_url").val(config.url + "/#" + config.hash);
        firepadRef.child("users").child(firepadUser).once('value', function (snapshot) {
            $("a[href='#" + snapshot.val().currentTab + "']").tab('show');
        });
        onresize();
    });
    // firepad.on('synced', function (isSynced) {
    //     console.log(isSynced);
    //     console.log(firepad.getText());
    // });

    // Mozilla Persona
    $("#sign_in").click(function () {
        navigator.id.request();
    });
    $("#sign_out").click(function () {
        navigator.id.logout();
    });

    navigator.id.watch({
        loggedInUser: loggedInUser ? currentUser : null,
        onlogin: function (assertion) {
            // A user has logged in! Here you need to:
            // 1. Send the assertion to your backend for verification and to create a session.
            // 2. Update your UI.
            $.ajax({
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
        onlogout: function () {
            // A user has logged out! Here you need to:
            // Tear down the user's session by redirecting the user or making a call to your backend.
            // Also, make sure loggedInUser will get set to null on the next page load.
            // (That's a literal JavaScript null. Not false, 0, or undefined. null.)
            $.ajax({
                type: 'POST',
                url: 'auth/logout.php',
                success: function (res, status, xhr) { window.location.reload(); },
                error: function (xhr, status, err) { console.error("Logout failure: " + err); }
            });
        }
    });

    // GitHub Gist
    $("#runExport").click(function () {
        var source = firepad.getText(),
            extension = 'cpp',
            url = config.url + "/#" + config.hash;
        $.ajax({
            type: 'POST',
            url: 'auth/gist.php',
            data: {
                source: source,
                extension: extension,
                description: "Exported source code from " + url,
                public: false,
            },
            success: function (res, status, xhr) { 
                var data = JSON.parse(res),
                    realData = JSON.parse(data.message),
                    gistUrl = realData.html_url,
                    gistId = realData.id,
                    time = realData.created_at,
                    gistRef = firepadRef.child("gists");
                gistRef = gistRef.push();
                gistRef.set({url: gistUrl, id: gistId, time: time});
            },
            error: function (xhr, status, err) {
                console.error("Error: " + err);
            }
        });
    });
    firepadRef.child("gists").on("child_added", function (snapshot) {
        var data = snapshot.val(),
            message = '<tr><td><a href="' + data.url + '">' + data.id + '</a></td>'
                    + '<td>' + data.time + '</td></tr>';
        $("#exportTBody").prepend(message);
    });

    // Ideone.com
    function checkIdeone(link, ideoneRef) {
       //console.log(link, ideoneRef);
        $.ajax({
            type: 'POST',
            url: 'auth/ideone.php',
            data: {
                action: 'check',
                link: link
            },
            success: function (res, stat, xhr) {
                var data = JSON.parse(res),
                    status = data.message.status,
                    details = data.message.details,
                    time = data.message.date;
                //console.log(data);
                ideoneRef.update({link: link, status: status, time: time, details: details});
                if (status !== "Done") {
                    setTimeout(function () { checkIdeone(link, ideoneRef); }, 500);
                }
            },
            error: function (xhr, status, err) {
                console.error("Error: " + err);
            }
        });
    }
    $("#runCompile").click(function () {
        var source = firepad.getText(),
            language = 1, // see https://ideone.com/faq
            action = 'submit',
            input = $("#compileInput").val();
        $.ajax({
            type: 'POST',
            url: 'auth/ideone.php',
            data: {
                source: source,
                language: language,
                action: action,
                input: input
            },
            success: function (res, stat, xhr) {
                var data = JSON.parse(res),
                    status = data.message.status,
                    details = data.message.details,
                    link = data.message.link,
                    time = data.message.date,
                    ideoneRef = firepadRef.child("ideone");
                ideoneRef = ideoneRef.push();
                //console.log(data);
                ideoneRef.set({link: link, status: status, time: time, details: details});
                checkIdeone(link, ideoneRef);
                $("#compileInput").val("");
                $("#compileInput").html("");
            },
            error: function (xhr, status, err) {
                $("#compileInput").val("");
                $("#compileInput").html("");
                console.error("Error: " + err);
            }
        });
    });
    firepadRef.child("ideone").on("child_added", function (snapshot) {
        var data = snapshot.val(),
            message = '<tr id="ideone-' + data.link + '"><td><a href="https://ideone.com/' + data.link + '">' + data.link + '</a></td>'
                    + '<td>' + data.time + '</td>'
                    + '<td>' + data.status + '</td></tr>',
            compileMessage = '<div class="compileMessage list-group-item">'
                    + '<div class="compileHeader">'
                    + '<a href="https://ideone.com/' + data.link + '">' + data.link + '</a> at ' + data.time + ':'
                    + '</div>'
                    + '<div class="compileBody" id="compileBody-ideone-' + data.link + '">';
        if (!data.details) {
            compileMessage += 'Status: <div class="status code">' + data.status + '</div>';
        } else {
            if (data.details.result_string === "Success") {
                compileMessage += 'Input: <pre class="input code">' + data.details.input + '</pre>'
                            + 'Output: <pre class="output code">' + data.details.output + '</pre>';
            } else {
                compileMessage += 'Status: <div class="status code">' + data.details.result_string + '</div>';
            }
        }
        compileMessage += '</div></div>';

        $("#compileTBody").prepend(message);
        $("#compileHistory").prepend(compileMessage);
    });
    firepadRef.child("ideone").on("child_changed", function (snapshot) {
        var data = snapshot.val(), message = '', compileMessage = '';
        // console.log(data);
        message = '<td><a href="https://ideone.com/' + data.link + '">' + data.link + '</a></td>'
                    + '<td>' + data.time + '</td>'
                    + '<td>' + data.status + '</td>';
        if (!data.details) {
            compileMessage += 'Status: <div class="status code">' + data.status + '</div>';
        } else {
            if (data.details.result_string === "Success") {
                compileMessage += 'Input: <pre class="input code">' + data.details.input + '</pre>'
                            + 'Output: <pre class="output code">' + data.details.output + '</pre>';
            } else {
                compileMessage += 'Status: <div class="status code">' + data.details.result_string + '</div>';
            }
        }

        $("#ideone-" + data.link).html(message);
        $("#compileBody-ideone-" + data.link).html(compileMessage);
    });

    window.addEventListener("resize", onresize);

    // Chat
    function appendChat(author, time, message) {
        var chatMessage = '<div class="chatMessage list-group-item">'
                    + '<div class="chatHeader">'
                    + author + ' [' + time + ']:'
                    + '</div>'
                    + '<div class="chatBody">' + message + '</div>'
                    + '</div>';
        $("#chatHistory").append(chatMessage);
        $("#chatHistory").prop('scrollTop', ($("#chatHistory").prop('scrollHeight')));
    }
    function pad(number) {
        if (number < 10) {
            return '0' + number;
        }
        return number;
    }
    $("#chatInput").keydown(function (event) {
        if (event.which === 13 && event.shiftKey === false) {
            event.preventDefault();
            if ($(this).val().trim() === "") {
                return false;
            }
            var date = new Date(),
                time = date.getUTCFullYear() +
                    '-' + pad(date.getUTCMonth() + 1) +
                    '-' + pad(date.getUTCDate()) +
                    'T' + pad(date.getUTCHours()) +
                    ':' + pad(date.getUTCMinutes()) +
                    ':' + pad(date.getUTCSeconds()) +
                    //'.' + (date.getUTCMilliseconds() / 1000).toFixed(3).slice(2, 5) +
                    'Z',
                chatRef = firepadRef.child("chat").push(),
                message = $(this).val();
            $(this).val("");
            $(this).html("");
            chatRef.set({author: currentUser, time: time, message: message});
        }
    });
    firepadRef.child("chat").on("child_added", function (snapshot) {
        var data = snapshot.val();
        appendChat(data.author, data.time, data.message);
    });

    // Twitter Bootstrap keep-open class
    $('.dropdown-menu').click(function (event) {
        if ($(this).hasClass('keep-open')) {
            event.stopPropagation();
        }
    });
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        // e.target // newly activated tab
        // e.relatedTarget // previous active tab
        onresize();
        if (loggedInUser) {
            var tabRef = e.target.href.substring(e.target.href.indexOf("#") + 1, e.target.href.length);
            firepadRef.child("users").child(firepadUser).update({currentTab: tabRef});
        }
    });
    // Auto-select share URL
    $("#share_url").focus(function () {
        this.select();
    });
});
/*
Firebase!
- Source code, cursor, OT --> done by Firepad
-- Undo and redo: no need, for now
- DONE Compilation history
- TODO Chatting and history
- TODO Presence: how  many people are online, who are online

-- remember to save all the states, including ideone history

- DONE Use ideone API (Sphere Engine™)
-- PHP to proxy the communication
http://ideone.com/sphere-engine

- TODO Tabs, for viewing past input and outputs?
 */