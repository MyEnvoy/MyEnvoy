function picturePreview(input) {

    if (input.files && input.files[0]) {
        var reader = new FileReader();

        reader.onload = function (e) {
            $('img#register_profile_pic').attr('src', e.target.result);
        };

        reader.readAsDataURL(input.files[0]);
    }
}