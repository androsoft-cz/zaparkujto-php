/**
 * *****************************************************************************
 * INPUT BINDING ***************************************************************
 * *****************************************************************************
 */
$.nette.ext('binding', {
    init: function () {
        this.bind();
    },
    complete: function () {
        this.bind()
    }
}, {
    bind: function () {
        // Modals
        $('#modalWindow[data-show="true"]').modal('show');
    }
});

/**
 * *****************************************************************************
 * SPINNER *********************************************************************
 * *****************************************************************************
 */
$.nette.ext('spinner', {
    init: function () {
        this.spinner = this.createSpinner();
        this.spinner.appendTo('body');
    },
    before: function () {
        this.show();
    },
    complete: function () {
        this.hide();
    }
}, {
    createSpinner: function () {
        return $('<div>', {
            id: 'ajax-spinner',
            css: {
                'display': 'none'
            }
        });
    },
    show: function () {
        this.spinner.show();
    },
    hide: function () {
        this.spinner.delay(this.delay).hide();
    },
    spinner: null,
    delay: 200
});

var Helpers = {
    buildUrl: function (base, key, value) {
        var sep = (base.indexOf('?') > -1) ? '&' : '?';
        return base + sep + key + '=' + value;
    }
};

/**
 * *****************************************************************************
 * MOBILE DETECTION ************************************************************
 * *****************************************************************************
 */
if (/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|ipad|iris|kindle|Android|Silk|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i.test(navigator.userAgent)
    || /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(navigator.userAgent.substr(0, 4))) {
    window.isMobile = true;
} else {
    window.isMobile = false;
}

/**
 * *****************************************************************************
 * EFFECTS *********************************************************************
 * *****************************************************************************
 */
$(function () {
    var $body = $('body');

    // Clear buttons
    $('a.clear-input').each(function (i, e) {
        var $el = $(e);
        var $target = $($(this).data('target'));
        $el.css('display', $target.val().length > 0 ? 'block' : 'none');
        $target.change(function (e) {
            $el.css('display', $(this).val().length > 0 ? 'block' : 'none');
        });
        $el.on('click', function (e) {
            e.preventDefault();
            $target.val('');
            $target.trigger('change');
        });
    });

    // Search form
    (function () {
        var $searchform = $('.search-form form');

        if ($searchform.length) {
            var $input = $searchform.find('input.suggest');
            var $defaultValue = $input.val();

            $input.on('keydown', function (e) {
                if (e.keyCode == 13) {
                    e.preventDefault();
                    return false;
                }
            });

            $input.on('focus focused', function (e) {
                if (!$input.val() || $input.val() == $defaultValue) {
                    e.stopImmediatePropagation();
                }
            });

            var google = new Bloodhound({
                datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
                queryTokenizer: Bloodhound.tokenizers.whitespace,
                remote: {
                    url: $input.data('handle'),
                    wildcard: '--query--'
                }
            });

            $input.typeahead({
                hint: false,
                highlight: true,
                minLength: 2
            }, {
                name: 'google',
                source: google,
                display: 'name',
                limit: 10,
                templates: {
                    empty: [
                        '<div class="empty-message">',
                        'Bohužel jsme nic nenalezli :(',
                        '</div>'
                    ].join('\n')
                }
            });

            $input.on('typeahead:select', function (e, s) {
                $($input.data('place-input')).val(s.place_id);
                $($input.data('lnglat-input')).val(s.lng + ',' + s.lat);
            });

            $input.on('typeahead:asyncrequest', function (e, q, d) {
                $.nette.ext('spinner').show();
            });

            $input.on('typeahead:asyncreceive typeahead:asynccancel typeahead:autocomplete', function (e, q, d) {
                $.nette.ext('spinner').hide();
                $input.typeahead('open');
            });

            // Usage user location
            if (navigator.geolocation && !$input.val().length && $searchform.data('autocoords')) {
                navigator.geolocation.getCurrentPosition(function (position) {
                    // Submit coords to URL
                    var url = $searchform.data('autocoords');
                    url = Helpers.buildUrl(url, $searchform.data('ns') + 'lng', position.coords.longitude);
                    url = Helpers.buildUrl(url, $searchform.data('ns') + 'lat', position.coords.latitude);
                    window.location.href = url;
                });
            }
        }
    })();

    // Reserve form
    (function () {
        var $form = $('form.time-form');
        if ($form.length) {

            var $rf = {};
            $rf.els = {
                switch: $form.find('.time-control'),
                timeFrom: $form.find('.time-from-control'),
                dateFrom: $form.find('.date-from-control'),
                customTime: $form.find('.custom-time-control'),
                customDate: $form.find('.custom-date-control'),
                price: $form.find('.price-control')
            };

            $rf.time = {
                getDateFrom: function () {
                    var d = new Date();
                    var df = $rf.els.dateFrom.val();
                    var tf = $rf.els.timeFrom.val();
                    d.setTime(Date.parse(tf + ' ' + df));

                    return d;
                }
            };

            $rf.customTime = {
                defaults: function () {
                    if (!$rf.els.customTime.val()) {
                        var d = $rf.time.getDateFrom();
                        $rf.els.customTime.val(d.getHours() + 1 + ':' + ('0' + d.getMinutes()).slice(-2));
                    }
                },
                getDate: function () {
                    var d = new Date();
                    var ct = $rf.els.customTime.val();

                    if ($rf.els.customDate.val()) {
                        var cd = $rf.els.customDate.val();
                        d.setTime(Date.parse(ct + ' ' + cd));
                    } else {
                        var df = $rf.els.dateFrom.val();
                        d.setTime(Date.parse(ct + ' ' + df));
                    }

                    return d;
                }
            };

            $rf.price = {
                setPrice: function (ratio) {
                    $rf.els.price.val(Math.ceil(ratio * $rf.els.price.data('price')));
                }
            };

            $rf.logic = {
                update: function () {
                    var t = $rf.els.switch.filter(':checked').val();

                    if (t != 0) {
                        // Pre defined
                        $rf.price.setPrice(t / 60);
                    } else {
                        // User given
                        $rf.customTime.defaults();
                        var d1 = $rf.time.getDateFrom();
                        var d2 = $rf.customTime.getDate();
                        var diff = d2 - d1;

                        if (diff > 0) {
                            $rf.price.setPrice(diff / (60 * 60 * 1000));
                        } else {
                            $rf.price.setPrice(0)
                        }
                    }
                },
                syncDates: function () {
                    $rf.els.customDate.val($rf.els.dateFrom.val());
                }
            };

            $rf.els.dateFrom.on('change', function () {
                if ($(this).val()) {
                    $rf.logic.update();
                    $rf.logic.syncDates();
                }
            });

            $rf.els.timeFrom.on('change', function () {
                if ($(this).val()) {
                    $rf.logic.update();
                }
            });

            $rf.els.customTime.on('change', function () {
                if ($(this).val()) {
                    $rf.logic.update();
                }
            });

			$rf.els.customTime.focus(function () {
				$rf.els.switch.filter('[value=0]').click();
			});

            $rf.els.customDate.on('change', function () {
                if ($(this).val()) {
                    $rf.logic.update();
                }
            });

            $rf.els.switch.on('change', function () {
                $rf.logic.update();
            });
        }
    })();

    // Order form
    (function () {
        $body.on('click', 'div.reserve-form .submit', function (e) {
            e.preventDefault();
            var $form = $(this);

            $.nette.ajax({
                url: $form.attr('action'),
                success: function (payload) {
                    if (payload.gopay) {
                        _gopay.checkout({gatewayUrl: payload.gopay.gw_url, inline: true});
                    } else {
                        $.nette.ext('snippets').updateSnippets(payload.snippets);
                    }
                }
            }, this, e);
        });
    })();

    // Google Maps
    $('div.google-map-detail').each(function (i, e) {
        var $widget = $(e);

        // Skip invalid div(s)
        if (!$widget.data('lat') || !$widget.data('lng')) return;

        // Create map
        map = new google.maps.Map(e, {
            center: {lat: $widget.data('lat'), lng: $widget.data('lng')},
            zoom: 12
        });

        // Add markers
        if ($widget.data('markers')) {
            markers = $widget.data('markers');
            $.each(markers, function (i, m) {
                var marker = new google.maps.Marker({
                    map: map,
                    position: {lat: m.lat, lng: m.lng},
                    title: m.title
                });

                marker.addListener('click', function () {
                    if (window.isMobile) {
                        // For the future maybe..
                        window.location.href = 'https://www.google.com/maps/dir/Current+Location/' + m.lat + ',' + m.lng;
                    } else {
                        window.location.href = 'https://www.google.com/maps/dir/Current+Location/' + m.lat + ',' + m.lng;
                    }
                });
            });
        }
    });

    $('input.time-control').on('click', function() {
        var $this = $(this),
            $inputs = $('.custom-time-group input');

        if ($this.hasClass('custom-time')) {
            $inputs.prop('disabled', false);
        } else {
            $inputs.prop('disabled', true);
        }
    });

    // Nette.ajax
    $.nette.init();
});
