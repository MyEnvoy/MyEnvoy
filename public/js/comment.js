$(window).load(function () {
    $('div.dashboard_post_footer a.noa').on('click', function () {
        $(this).parent().parent().parent().find('.newcomment').focus();
    });

    $('div.dashboard_post_comment a.noa.comment_commentit').on('click', function () {
        $(this).parent().parent().parent().parent().parent().parent().find('.dashboard_post_comments_newsub').show();
        $(this).parent().parent().parent().parent().parent().parent().find('.newsubcomment').focus();
    });

    $('.newcomment').on('keydown', function (e) {
        if (e.keyCode === 13) {
            var postID = $(this).closest('.dashboard_post_container').attr('post-id');
            var content = $(this).val();
            commitComment(postID, content);
            $(this).val('');
        }
    });

    $('.newsubcomment').on('keydown', function (e) {
        if (e.keyCode === 13) {
            var postID = $(this).closest('.onecomment').attr('post-id');
            var content = $(this).val();
            commitComment(postID, content);
            $(this).val('');
        }
    });

    function commitComment(postID, content) {
        $('#dashboard_post_comment_id').val(postID);
        $('#dashboard_post_comment_content').val(content);

        $('#dashboard_post_comment_form').submit();
    }
});