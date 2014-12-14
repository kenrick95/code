/*jslint browser: true, sloppy: true, plusplus: true */
/*global CodeMirror, jQuery, $, Firepad, Firebase, config */

$(document).ready(function () {
    // Loader
    $("#loading").css("margin-top", ($("#overlay").outerHeight() - $("#loading").outerHeight()) / 2  + "px");

    // Variables
    var loggedInUser = false, codeMirror, firepadRef, firepad, userRef, firepadUser,
        currentUser, displayName, userColor,
        defaultSource = '#include <iostream>'
        + '\nusing namespace std;'
        + '\n'
        + '\nint main () {'
        + '\n    cout << "Hello World!";'
        + '\n    return 0;'
        + '\n}';

    // Helper function declaration
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
    function pad(number) {
        if (number < 10) { return '0' + number; }
        return number;
    }
    function stringCleaner(value) {
        var lt = /</g, gt = />/g, ap = /'/g, ic = /"/g;
        value = value.toString().replace(lt, "&lt;").replace(gt, "&gt;").replace(ap, "&#39;").replace(ic, "&#34;");
        return value;
    }
    function trim(str, charlist) {
        // http://phpjs.org/functions/trim/
        var whitespace, l = 0, i = 0;
        str += '';
        if (!charlist) {
            // default list
            whitespace = ' \n\r\t\f\x0b\xa0\u2000\u2001\u2002\u2003\u2004\u2005\u2006\u2007\u2008\u2009\u200a\u200b\u2028\u2029\u3000';
        } else {
            // preg_quote custom list
            charlist += '';
            whitespace = charlist.replace(/([\[\]\(\)\.\?\/\*\{\}\+\$\^\:])/g, '$1');
        }
        l = str.length;
        for (i = 0; i < l; i++) {
            if (whitespace.indexOf(str.charAt(i)) === -1) {
                str = str.substring(i);
                break;
            }
        }
        l = str.length;
        for (i = l - 1; i >= 0; i--) {
            if (whitespace.indexOf(str.charAt(i)) === -1) {
                str = str.substring(0, i + 1);
                break;
            }
        }
        return whitespace.indexOf(str.charAt(0)) === -1 ? str : '';
    }
    function appendChat(author, time, message, color) {
        var chatMessage = '<div class="chatMessage list-group-item">'
                    + '<div class="chatHeader">'
                    + '<b style="color: ' + color + '">' + stringCleaner(author) + '</b>'
                    + ' [' + time + ']:'
                    + '</div>'
                    + '<div class="chatBody">' + stringCleaner(message) + '</div>'
                    + '</div>';
        $("#chatHistory").append(chatMessage);
        $("#chatHistory").prop('scrollTop', ($("#chatHistory").prop('scrollHeight')));
    }

    /**
     * Calculates and sets height of codeMirror, #sidebarBody, #chatHistory, and #compileHistory
     * @return none
     */
    function onresize() {
        var cm = codeMirror;
        cm.setSize(document.getElementsByTagName("html")[0].offsetWidth - 20, document.getElementsByTagName("html")[0].offsetHeight - document.getElementById("header").offsetHeight - 5);
        cm.refresh();

        $("#sidebarBody").height($(".CodeMirror").outerHeight() - 30);
        $("#chatHistory").height($("#sidebarBody").height() - $("#chatField").outerHeight() - $("#chatPresence").outerHeight());
        $("#compileHistory").height($("#sidebarBody").height() - $("#compileField").outerHeight());
    }

    /**
     * get FirepadRef of current pad
     * @return {Firebase} FirepadRef of current pad
     */
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

    // CodeMirror Initialization
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

    // FirepadRef Initialization
    firepadRef = getRef();

    // Firepad User
    currentUser  = readCookie("login_session_cookie");
    if (currentUser !== null) {
        loggedInUser = true;
        currentUser = currentUser.replace(/%40/g, "@"); // "@" is encoded as %40 and stored in the cookie
        firepadUser = currentUser.replace(/\./g, "dot");

        // get color assigned to this user and the display name saved
        userRef = firepadRef.child(firepadUser).once("value", function (snapshot) {
            userColor = snapshot.val().color;
            displayName = snapshot.val().displayName;
        });
    } else {
        userRef = firepadRef.child("users").push();
        currentUser = userRef.key();
        firepadUser = currentUser;

        // assign new color
        var COLORS = [
            "#8A2BE2", "#7FFF00", "#DC143C", "#00FFFF", "#8FBC8F", "#FF8C00", "#FF00FF",
            "#FFD700", "#F08080", "#90EE90", "#FF6347"];
        var DEFAULT_NICKNAMES = [
            "Friendly Fox",
            "Brilliant Beaver",
            "Observant Owl",
            "Gregarious Giraffe",
            "Wild Wolf",
            "Silent Seal",
            "Wacky Whale",
            "Curious Cat",
            "Intelligent Iguana"
        ];
        userColor = COLORS[Math.floor(Math.random() * COLORS.length)];
        displayName = DEFAULT_NICKNAMES[Math.floor(Math.random() * DEFAULT_NICKNAMES.length)];
    }
    console.log(currentUser);
    console.log(firepadUser);
    console.log(displayName);
    console.log(userColor);

    //Firepad Initialization
    firepad = Firepad.fromCodeMirror(firepadRef, codeMirror,
        {defaultText: defaultSource, userId: firepadUser, userColor: userColor });


    // on Events
    // Firepad
    firepad.on('ready', function () {
        $("#share_url").val(config.url + "/#" + config.hash);
        firepadRef.child("users").child(firepadUser).once('value', function (snapshot) {
            $("a[href='#" + snapshot.val().currentTab + "']").tab('show');
            if (snapshot.val().displayName) {
                displayName = snapshot.val().displayName;
            }
            if (snapshot.val().color) {
                userColor = snapshot.val().color;
            }
        });
        $("#chatName").val(displayName);
        onresize();
        $("#overlay").fadeOut();
    });

    // on Events
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
            $.ajax({
                type: 'POST',
                url: 'auth/logout.php',
                success: function (res, status, xhr) { window.location.reload(); },
                error: function (xhr, status, err) { console.error("Logout failure: " + err); }
            });
        }
    });

    // on Events
    // GitHub Gist
    $("#runExport").click(function () {
        $(this).prop('disabled', true);
        var source = firepad.getText(),
            extension = config.language.extension,
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
                $("#runExport").removeAttr('disabled');
            },
            error: function (xhr, status, err) {
                $("#runExport").removeAttr('disabled');
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

    // on Events
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
                    setTimeout(function () { checkIdeone(link, ideoneRef); }, 1000);
                }
            },
            error: function (xhr, status, err) {
                console.error("Error: " + err);
            }
        });
    }
    $("#runCompile").click(function () {
        $(this).prop('disabled', true);
        var source = firepad.getText(),
            language = config.language.val, // see https://ideone.com/faq
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
                $("#compileInput").val("");
                $("#compileInput").html("");
                $("#runCompile").removeAttr('disabled');
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
            },
            error: function (xhr, status, err) {
                $("#compileInput").val("");
                $("#compileInput").html("");
                $("#runCompile").removeAttr('disabled');
                console.error("Error: " + err);
            }
        });
    });
    firepadRef.child("ideone").on("child_added", function (snapshot) {
        var data = snapshot.val(),
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

        $("#compileHistory").prepend(compileMessage);
    });
    firepadRef.child("ideone").on("child_changed", function (snapshot) {
        var data = snapshot.val(), /*message = '', */compileMessage = '';
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

        $("#compileBody-ideone-" + data.link).html(compileMessage);
    });

    // on Events
    // resize
    window.addEventListener("resize", onresize);

    // on Events
    // Chat
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
            chatRef.set({author: displayName, time: time, message: message, color: userColor});
        }
    });
    firepadRef.child("chat").on("child_added", function (snapshot) {
        var data = snapshot.val();
        appendChat(data.author, data.time, data.message, data.color);
    });

    // on Events
    // Presence
    var presenceRef = new Firebase('https://code-kenrick95.firebaseio.com/.info/connected');
    presenceRef.on("value", function (snapshot) {
        if (snapshot.val() === true) {
            var con = firepadRef.child("connections").child(firepadUser);
            con.set(true);
            con.onDisconnect().remove();
            if (loggedInUser) {
                userRef.child("last_online").onDisconnect().set(Firebase.ServerValue.TIMESTAMP);
            } else {
                // remove display name, etc. for anonymous user from "user" tree
                // It won't affect "chat" history
                userRef.onDisconnect().remove();
            }
        }
    });
    firepadRef.child("connections").on("value", function (snapshot) {
        var len = Object.keys(snapshot.val()).length;
        $("#chatPresence").html("<strong>" + len + "</strong> people online (including you)");
    });

    // Settings save
    $("#chatName").focusout(function () {
        displayName = $(this).val();
        firepadRef.child("users").child(firepadUser).update({ displayName: displayName });
    });

    // Language
    function loadJS(src, callback) {
        var s = document.createElement('script');
        s.src = src;
        s.async = true;
        s.onreadystatechange = s.onload = function () {
            var state = s.readyState;
            if (!callback.done && (!state || /loaded|complete/.test(state))) {
                callback.done = true;
                callback();
            }
        };
        document.getElementsByTagName('body')[0].appendChild(s);
    }
    $("#editorLanguage").change(function loadModeForSelectedOption(e) {
        var selected = $("#editorLanguage option:selected"),
            script = selected.attr('data-script'),
            mode = selected.attr('data-mime-type'),
            extension = selected.attr('data-extension'),
            val = selected.val();
        config.language.script = script;
        config.language.mode = mode;
        config.language.extension = extension;
        config.language.val = val;
        config.language.name = trim(selected.text());
        firepadRef.child("language").set(config.language);
    });
    firepadRef.child("language").on("value", function (snapshot) {
        config.language = snapshot.val();
        console.log(config.language);
        if (config.language === null) {
            config.language = {
                script: "https://code-kenrick95.firebaseapp.com/js/mode/clike/clike.js",
                mode: "text/x-c++src",
                extension: "cpp",
                val: "1",
                name: "C++"
            };
            firepadRef.child("language").set(config.language);
        }

        loadJS(config.language.script, function () {
            codeMirror.setOption("mode", config.language.mode);
            $("#selectedLanguage").text(config.language.name);
            $("#editorLanguage").selectpicker('val', config.language.val);
        });
    });

    // Bootstrap keep-open class
    $('.dropdown-menu').click(function (event) {
        if ($(this).hasClass('keep-open')) {
            event.stopPropagation();
        }
    });

    // Bootstrap save current tab to Firebase
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