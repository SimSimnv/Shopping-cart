$( document ).ready(function() {
    setTimeout(function(){$('.alert-success').fadeOut()},2000);
    setTimeout(function(){$('.alert-danger').fadeOut()},4000);

    $("form").submit(function() {
        //$('button[type=submit], input[type=submit]').prop('disabled',true);
    });

});
