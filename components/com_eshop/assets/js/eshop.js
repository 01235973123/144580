/**
 * 
 * Function to update quantity of product in popout cart or popout quote
 * @param sign
 * @param quantityId
 * @param quantityStep
 */
function quantityUpdate(sign, quantityId, quantityStep)
{
	quantityId		= quantityId.toString();
	quantityStep	= parseInt(quantityStep);

	if (isNaN(quantityStep))
	{
		quantityStep = 1;
	}
	
	var oldQuantity = parseInt(jQuery('#' + quantityId).val());
	
	if (sign == '+')
	{
		newQuantity = oldQuantity + quantityStep;
	}
	else if (sign == '-')
	{
		var newQuantity = oldQuantity - quantityStep;
	}

	if (newQuantity <= 0)
	{
		newQuantity = 0;
	}
	
	jQuery('#' + quantityId).val(newQuantity);
}

// Function to add a product to the cart
function addToCart(productId, quantity, site, lang, popout, cartUrl, message)
{
	var date = new Date();
	quantity = typeof(quantity) != 'undefined' ? quantity : 1;
	lang = typeof(lang) != 'undefined' ? lang : '';
	if (typeof(jQuery('#quantity_' + productId)) != 'undefined')
		quantity = jQuery('#quantity_' + productId).val();
	jQuery.ajax({
		url: site + 'index.php?option=com_eshop&task=cart.add' + lang,
		type: 'POST',
		data: 'id=' + productId + '&quantity=' + quantity,
		dataType: 'json',
		beforeSend: function() {
			jQuery('#add-to-cart-' + productId).attr('disabled', true);
			jQuery('#add-to-cart-' + productId).after('<span class="wait-' + productId + '">&nbsp;<img src="' + site + 'media/com_eshop/assets/images/loading.gif" alt="" /></span>');
		},
		success: function(json) {
			if (json['redirect'])
			{
				jQuery('#add-to-cart-' + productId).attr('disabled', false);
				jQuery('.wait-' + productId).remove();
				window.location.href = json['redirect'];
			}
			if (json['success'])
			{
				if (popout == 'message' && typeof(message) !== 'undefined')
				{
					jQuery.ajax({
						url: site + 'index.php?option=com_eshop&view=cart&layout=mini&format=raw' + lang + '&pt=' + date.getTime(),
						dataType: 'html',
						success: function(html) {
							jQuery('#add-to-cart-' + productId).attr('disabled', false);
							jQuery('.wait-' + productId).remove();
							jQuery('#eshop-cart').html(html);
							jQuery('.eshop-content').hide();
							jQuery('.alert-success').remove();
							jQuery('#add-to-cart-' + productId).before('<div class="alert-success">' + message + '</div>');
						},
						error: function(xhr, ajaxOptions, thrownError) {
							alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
						}
					});
				}
				else if (popout == 'redirect')
				{
					window.location.href = cartUrl;
				}
				else
				{
					jQuery.ajax({
						url: site + 'index.php?option=com_eshop&view=cart&layout=popout&format=raw' + lang + '&pt=' + date.getTime(),
						dataType: 'html',
						success: function(html) {
							jQuery('#add-to-cart-' + productId).attr('disabled', false);
							jQuery('.wait-' + productId).remove();
							jQuery.colorbox({
								overlayClose: true,
								opacity: 0.5,
								width: '90%',
								maxWidth: '800px',
								href: false,
								html: html
							});
							jQuery.ajax({
								url: site + 'index.php?option=com_eshop&view=cart&layout=mini&format=raw' + lang + '&pt=' + date.getTime(),
								dataType: 'html',
								success: function(html) {
									jQuery('#eshop-cart').html(html);
									jQuery('.eshop-content').hide();
								},
								error: function(xhr, ajaxOptions, thrownError) {
									alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
								}
							});
						},
						error: function(xhr, ajaxOptions, thrownError) {
							alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
						}
					});
				}
			}
		},
		error: function(xhr, ajaxOptions, thrownError) {
			alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
		}
	});
}

function multipleProductsAddToCart(site, lang, popout, cartUrl)
{	
	var products = jQuery("input[name='product_id[]']");
	var date = new Date();
	
	if (products.length > 0)
	{
		var productIds = '';
		var productQuantities = '';
		for (var i = 0; i < products.length; i++)
		{
			if (parseInt(jQuery('#quantity_' + products[i].value).val()) > 0)
			{
				productIds += products[i].value;
				productQuantities += jQuery('#quantity_' + products[i].value).val();
			}
			if (i < products.length - 1)
			{
				productIds += ',';
				productQuantities += ',';
			}
		}
		jQuery.ajax({
			url: site + 'index.php?option=com_eshop&task=cart.addMultipleProducts' + lang,
			type: 'POST',
			data: 'product_ids=' + productIds + '&product_quantities=' + productQuantities,
			dataType: 'json',
			beforeSend: function() {
				jQuery('#multiple-products-add-to-cart').attr('disabled', true);
				jQuery('#multiple-products-add-to-cart').after('<span class="wait-multiple-products-add">&nbsp;<img src="' + site + 'media/com_eshop/assets/images/loading.gif" alt="" /></span>');
			},
			success: function(json) {
				if (json['redirect'])
				{
					jQuery('#add-to-cart-' + productId).attr('disabled', false);
					jQuery('.wait-' + productId).remove();
					window.location.href = json['redirect'];
				}
				if (json['success'])
				{
					if (popout == 'popout')
					{
						jQuery.ajax({
							url: site + 'index.php?option=com_eshop&view=cart&layout=popout&format=raw' + lang + '&pt=' + date.getTime(),
							dataType: 'html',
							success: function(html) {
								jQuery('#multiple-products-add-to-cart').attr('disabled', false);
								jQuery('.wait-multiple-products-add').remove();
								jQuery.colorbox({
									overlayClose: true,
									opacity: 0.5,
									width: '90%',
									maxWidth: '800px',
									href: false,
									html: html
								});
								jQuery.ajax({
									url: site + 'index.php?option=com_eshop&view=cart&layout=mini&format=raw' + lang + '&pt=' + date.getTime(),
									dataType: 'html',
									success: function(html) {
										jQuery('#eshop-cart').html(html);
										jQuery('.eshop-content').hide();
									},
									error: function(xhr, ajaxOptions, thrownError) {
										alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
									}
								});
							},
							error: function(xhr, ajaxOptions, thrownError) {
								alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
							}
						});
					}
					else if (cartUrl != '')
					{
						window.location.href = cartUrl;
					}
				}
			},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	}
	else
	{
		alert('there is no products to add to cart');
	}
}

//Function to add a product to the quote
function addToQuote(productId, quantity, site, lang)
{
	var date = new Date();
	quantity = typeof(quantity) != 'undefined' ? quantity : 1;
	lang = typeof(lang) != 'undefined' ? lang : '';
	if (typeof(jQuery('#quantity_' + productId)) != 'undefined')
		quantity = jQuery('#quantity_' + productId).val();
	jQuery.ajax({
		url: site + 'index.php?option=com_eshop&task=quote.add' + lang,
		type: 'POST',
		data: 'id=' + productId + '&quantity=' + quantity,
		dataType: 'json',
		beforeSend: function() {
			jQuery('#add-to-quote-' + productId).attr('disabled', true);
			jQuery('#add-to-quote-' + productId).after('<span class="wait-' + productId + '">&nbsp;<img src="' + site + 'media/com_eshop/assets/images/loading.gif" alt="" /></span>');
		},
		success: function(json) {
			if (json['redirect'])
			{
				jQuery('#add-to-quote-' + productId).attr('disabled', false);
				jQuery('.wait-' + productId).remove();
				window.location.href = json['redirect'];
			}
			if (json['success'])
			{
				jQuery.ajax({
					url: site + 'index.php?option=com_eshop&view=quote&layout=popout&format=raw' + lang + '&pt=' + date.getTime(),
					dataType: 'html',
					success: function(html) {
						jQuery('#add-to-quote-' + productId).attr('disabled', false);
						jQuery('.wait-' + productId).remove();
						jQuery.colorbox({
							overlayClose: true,
							opacity: 0.5,
							width: '90%',
							maxWidth: '800px',
							href: false,
							html: html
						});
						jQuery.ajax({
							url: site + 'index.php?option=com_eshop&view=quote&layout=mini&format=raw' + lang + '&pt=' + date.getTime(),
							dataType: 'html',
							success: function(html) {
								jQuery('#eshop-quote').html(html);
								jQuery('.eshop-content').hide();
							},
							error: function(xhr, ajaxOptions, thrownError) {
								alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
							}
						});
					},
					error: function(xhr, ajaxOptions, thrownError) {
						alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
					}
				});
			}
		}
	});
}

//Function to show make Notify about product popout
function makeNotify(productId, site, lang)
{
	lang = typeof(lang) != 'undefined' ? lang : '';
	jQuery.ajax({
		url: site + 'index.php?option=com_eshop&view=product&layout=notify&format=raw' + lang,
		type: 'POST',
		data: 'id=' + productId,
		dataType: 'html',
		success: function(html) {
			jQuery.colorbox({
				overlayClose: true,
				opacity: 0.5,
				width: '90%',
				maxWidth: '800px',
				href: false,
				html: html
			});
		}
	});
}

// Function to add a product to the wish list
function addToWishList(productId, site, lang)
{
	lang = typeof(lang) != 'undefined' ? lang : '';
	jQuery.ajax({
		url: site + 'index.php?option=com_eshop&task=wishlist.add' + lang,
		type: 'post',
		data: 'product_id=' + productId,
		dataType: 'json',
		success: function(json) {
			if (json['success']) {
				jQuery.colorbox({
					overlayClose: true,
					opacity: 0.5,
					width: '90%',
					maxWidth: '800px',
					href: false,
					html: json['success']['message']
				});
			}
		}
	});
}

//Function to remove a product from the wish list
function removeFromWishlist(productId, site, lang)
{
	lang = typeof(lang) != 'undefined' ? lang : '';
	jQuery.ajax({
		url: site + 'index.php?option=com_eshop&task=wishlist.remove' + lang,
		type: 'post',
		data: 'product_id=' + productId,
		dataType: 'json',
		success: function(json) {
			window.location.href = json['redirect'];
		}
	});
}

// Function to add product to compare
function addToCompare(productId, site, lang)
{
	lang = typeof(lang) != 'undefined' ? lang : '';
	jQuery.ajax({
		url: site + 'index.php?option=com_eshop&task=compare.add' + lang,
		type: 'post',
		data: 'product_id=' + productId,
		dataType: 'json',
		success: function(json) {
			if (json['success']) {
				jQuery.colorbox({
					overlayClose: true,
					opacity: 0.5,
					width: '90%',
					maxWidth: '800px',
					href: false,
					html: json['success']['message']
				});
			}
		}
	});
}

//Function to remove a product from compare
function removeFromCompare(productId, site, lang)
{
	lang = typeof(lang) != 'undefined' ? lang : '';
	jQuery.ajax({
		url: site + 'index.php?option=com_eshop&task=compare.remove' + lang,
		type: 'post',
		data: 'product_id=' + productId,
		dataType: 'json',
		success: function(json) {
			window.location.href = json['redirect'];
		}
	});
}

//Function to show ask question about product popout
function askQuestion(productId, site, lang)
{
	lang = typeof(lang) != 'undefined' ? lang : '';
	jQuery.ajax({
		url: site + 'index.php?option=com_eshop&view=product&layout=askquestion&format=raw' + lang,
		type: 'POST',
		data: 'id=' + productId,
		dataType: 'html',
        success: function(html) {
            jQuery.colorbox({
                overlayClose: true,
                opacity: 0.5,
                width: '90%',
				maxWidth: '800px',
                href: false,
                html: html
            });
        }
	});
}

//Function to show email a friend about product popout
function emailAFriend(productId, site, lang)
{
	lang = typeof(lang) != 'undefined' ? lang : '';
	jQuery.ajax({
		url: site + 'index.php?option=com_eshop&view=product&layout=emailafriend&format=raw' + lang,
		type: 'POST',
		data: 'id=' + productId,
		dataType: 'html',
		success: function(html) {
			jQuery.colorbox({
				overlayClose: true,
				opacity: 0.5,
				width: '90%',
				maxWidth: '800px',
				href: false,
				html: html
			});
		}
	});
}

if("undefined"===typeof Eshop) {
	var Eshop={};	
}

Eshop.updateStateList = function(countryId, stateInputId) {	
	// First of all, we need to empty the state dropdown	
	var list = document.getElementById(stateInputId);		
	// empty the list
	for (i = 1; i < list.options.length; i++) {
		list.options[i] = null;
	}
	list.length = 1;
	var stateNames = stateList[countryId];
	if (stateNames) {
		var arrStates = stateNames.split(',');
		i = 0;
		var state = '';
		var stateName = '';
		for ( var j = 0; j < arrStates.length; j++) {
			state = arrStates[j];
			stateName = state.split(':');
			opt = new Option();
			opt.value = stateName[0];
			opt.text = stateName[1];
			list.options[i++] = opt;
		}
		list.length = i;
	}
}

EShopInitReCaptcha2 = function()
{
    var item = document.getElementById('dynamic_recaptcha_1'),
        option_keys = ['sitekey', 'theme', 'size', 'tabindex', 'callback', 'expired-callback', 'error-callback'],
        options = {},
        option_key_fq
    ;

    if (item.dataset) {
        options = item.dataset;
    } else {
        for (var j = 0; j < option_keys.length; j++) {
            option_key_fq = ('data-' + option_keys[j]);
            if (item.hasAttribute(option_key_fq)) {
                options[option_keys[j]] = item.getAttribute(option_key_fq);
            }
        }
    }

    // Set the widget id of the recaptcha item
    item.setAttribute(
        'data-recaptcha-widget-id',
        grecaptcha.render(item, options)
    );
};

EShopInitReCaptchaInvisible = function()
{
    var item = document.getElementById('dynamic_recaptcha_1'),
        option_keys = ['sitekey', 'badge', 'size', 'tabindex', 'callback', 'expired-callback', 'error-callback'],
        options = {},
        option_key_fq
    ;

    if (item.dataset) {
        options = item.dataset;
    } else {
        for (var j = 0; j < option_keys.length; j++) {
            option_key_fq = ('data-' + option_keys[j]);
            if (item.hasAttribute(option_key_fq)) {
                options[option_keys[j]] = item.getAttribute(option_key_fq);
            }
        }
    }
    // Set the widget id of the recaptcha item
    item.setAttribute(
        'data-recaptcha-widget-id',
        grecaptcha.render(item, options)
    );
    // Execute the invisible reCAPTCHA
    grecaptcha.execute(item.getAttribute('data-recaptcha-widget-id'));
};