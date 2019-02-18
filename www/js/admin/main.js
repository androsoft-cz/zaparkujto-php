$(function () {
    $.nette.init();

    initOnStartupAdmin();
	$.nette.ext('init-on-startup-admin', {
		load: function() {
			initOnStartupAdmin();
		}
	});

    $('#modalWindow[data-show="true"]').modal('show');
//	$('textarea').autosize();
});

function initOnStartupAdmin()
{

    moment().format('DD.MM.YYYY');
    $('.grido').grido({
        ajax: false
    });
}

function getTextDate() {
    var mydate = new Date();
    var datum = mydate.getDate() + '.' + (mydate.getMonth() + 1) + '.' + mydate.getFullYear();
    return datum;
}
