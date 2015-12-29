// http://blog.oratronik.org/?p=457


window.setTimeoutOrig = window.setTimeout;
window.setTimeout = function (f, del) {
    var l_stack = Error().stack.toString();
    if (l_stack.indexOf('kis.scr.kaspersky-labs.com') > 0) {
        return 0;
    }

    window.setTimeoutOrig(f, del);
};
