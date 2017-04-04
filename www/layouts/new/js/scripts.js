/* modals */
$('.modals').click(function() {$(this).fadeOut(); $('.modal').css('display', 'none');});
$('.modal').click(function(event) {event.stopPropagation();});
$('.ui-show-modal').click(function() {
    target = $(this).data('target');
    $('.modals').css('display', 'flex').hide().fadeIn();
    $('.modal[data-name=' + target + ']').css('display', 'block');
});

/* cart */
$('.cart').click(function() {
    $('.summary').slideToggle();
});