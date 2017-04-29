$( document ).ready(function() {
    $("form").submit(function() {
        $('button[type=submit], input[type=submit]').prop('disabled',true);
    });
});
