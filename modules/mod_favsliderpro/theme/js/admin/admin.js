
jQuery(document).ready(function() {

    favgeneratesets('#attrib-content');

    favparsesets();

    favcatchange();

    favgenerateonclicks();

    favgenerateonchanges();

});

function favgenerateonchanges() {

	jQuery('#jform_params_use_hikashop_category').change(function() {

		favcatchange();

	});

    jQuery('select[id^="jform_params_use_hikashop_product"]').change(function() {

        favprodchange(this.id);

    });

}

function favparsesets() {

    jQuery('select[id^="jform_params_use_hikashop_product"]').each(function() {

        var slctid = this.id;

        var cval = jQuery('#'+slctid).val();

        var ldisabled = [5,6,7,8,9,10,11,12,13,15,17,18,19,20,23,24];

        var cnumber = Number(slctid.replace(/[^\d]/g, ''));

        jQuery('.favcollapsec'+cnumber).each(function(k,v) {

            if (cval > 0) {

                if (jQuery.inArray(k, ldisabled) > -1) {

                    jQuery(this).hide();
                    jQuery(this).addClass('favkeephide');

                }

            }

        });


    });

}

function favprodchange(slctid) {

    var cval = jQuery('#'+slctid).val();

    var ldisabled = [5,6,7,8,9,10,11,12,13,15,17,18,19,20,23,24];

    var cnumber = Number(slctid.replace(/[^\d]/g, ''));

    jQuery('.favcollapsec'+cnumber).each(function(k,v) {

        if (cval > 0) {

            if (jQuery.inArray(k, ldisabled) > -1) {

                jQuery(this).hide();
                jQuery(this).addClass('favkeephide');

            }

        } else {

            if (jQuery.inArray(k, ldisabled) > -1) {

                jQuery(this).show();
                jQuery(this).removeClass('favkeephide');

            }

        }

    });

}

function favcatchange() {

		var cval = jQuery('#jform_params_use_hikashop_category').val();

		var cactive = -1;

		jQuery('.favcollapseh, .favcollapsec').each(function() {

			if (jQuery(this).hasClass('favcollapsehactive')) {

				cactive = jQuery(this).attr('favcollapse-order');

			}

			if (cval == 0) {

				if (jQuery(this).hasClass('favcollapsec0')) {

					if (jQuery(this).find('.favtitle').length > 0 || jQuery(this).find('#jform_params_use_hikashop_category').length > 0 || jQuery(this).find('#jform_params_hikashop_menu_id').length > 0) {

						if (!jQuery(this).hasClass('favkeephide')) { jQuery(this).show(); }

					} else {

						jQuery(this).hide();

					}

				} else if (jQuery(this).hasClass('favcollapsec'+cactive)) {

					if (!jQuery(this).hasClass('favkeephide')) { jQuery(this).show(); }

				} else if (!jQuery(this).hasClass('favcollapsec')) {

					if (!jQuery(this).hasClass('favkeephide')) { jQuery(this).show(); }

				}

			} else {

				if (jQuery(this).hasClass('favcollapsec0')) {

					if (!jQuery(this).hasClass('favkeephide')) { jQuery(this).show(); }

				} else {

					jQuery(this).hide();

				}

			}

		});

}

function favgenerateonclicks() {

    jQuery('.favcollapseh').click(function() {

        var cnumber = jQuery(this).attr('favcollapse-order');

        if (jQuery(this).hasClass('favcollapsehactive')) {

            jQuery(this).removeClass('favcollapsehactive');

            jQuery('.favcollapsec'+cnumber).slideUp( "slow", function() {});

        } else {

            jQuery('.favcollapseh').removeClass('favcollapsehactive');

            jQuery(this).addClass('favcollapsehactive');

            jQuery('.favcollapsec:not(.favcollapsec0)').css('display','none');

            jQuery('.favcollapsec'+cnumber).each(function() {

                if (!jQuery(this).hasClass('favkeephide')) {

                jQuery(this).slideDown( "slow", function() {});

                }

            });

            var cscrollto = Number(jQuery('.favcollapsehactive').offset().top) - Number(jQuery(this).height());

            jQuery('html, body').animate({ scrollTop: cscrollto }, 700, function() {});

        }

    });

}

function favgeneratesets(setel) {

    var nel = jQuery(setel+' .control-group').length;
    var lastel = 0;

    jQuery(setel+' .control-group').each(function(k,v) {

        var cprev = '';
        var ck = k+1;

        var chtml = jQuery(this)[0].outerHTML;
        var ch4 = jQuery(this).find('h4')[0];
        var clabel = jQuery(this).find('label')[0];

        if (typeof(ch4) !== "undefined") {

            var cnumber = Number(jQuery(ch4).text().replace(/[^\d]/g, ''));

            if (cnumber > lastel) {

                jQuery(this).addClass('favcollapseh');
                jQuery(this).attr('favcollapse-order',cnumber);

                if (lastel == 0) {

                    jQuery(this).addClass('favcollapsehactive');

                }

            } else {

                jQuery(this).addClass('favcollapsec'+cnumber);
                jQuery(this).addClass('favcollapsec');

                if (cnumber > 1) {

                    jQuery(this).css('display','none');

                }

            }

            lastel = cnumber;

        } else if (typeof(clabel) !== "undefined") {

            var cnumber = Number(jQuery(clabel).text().replace(/[^\d]/g, ''));

            jQuery(this).addClass('favcollapsec'+cnumber);
            jQuery(this).addClass('favcollapsec');

            if (cnumber > 1) {

                jQuery(this).css('display','none');

            }

        }

        if (ck == nel) {

            // nothing to do

        }

    });

}
