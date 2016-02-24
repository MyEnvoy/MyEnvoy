function jsconfirm(url, text) {
    if (confirm(text) === true) {
        window.location.href = url;
    }
}