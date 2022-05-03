$(document).ready(function(){
	$('.content_item_big_ban').each(function(){
		$(this).parent().children('a').html($(this).find("a:first-child").html()).attr('href',$(this).find("a:first-child").attr('href'));
		$(this).find("a:first-child").remove();
	});

	$('#sw_brand_sw_brand_1').owlCarousel({
		items: 6
	});
	$('#sw_reponsive_post_slider_1105650 .owl-carousel').owlCarousel({
		responsive:{
                    0:{
                        items: 1,
                    },
                    575:{
                        items: 1,
                    },
                    768:{
                    	items: 3,

                    },
                    991:{
                    	items: 3,
                    }
                  }
	});
	$('.my_slider').owlCarousel({
		items: 1,
		nav: true,
		navText: ['','']
	});
	$('#fashion_sw_countdown_sw_woo_slider_countdown_widget_2 .slider').owlCarousel({
		responsive:{
                    0:{
                        items: 1,
                    		nav: true,
                    		navText: ['','']
                    },
                    575:{
                        items: 1,
	                    	nav: true,
	                    	navText: ['','']
                    },
                    768:{
                    	items: 3,
	                    nav: true,
	                    navText: ['','']

                    },
                    991:{
                    	items: 5,
                    	nav: true,
                    	navText: ['','']
                    }
                  }
	/*	items: 5,
		nav: true,
		navText: ['','']*/
	});
	$('.rev_slider').owlCarousel({
		items: 1,
		autoplay:true,
		autoplayTimeout:5000,
		 loop: true,
    lazyLoad:true
	});
	$('.slider_prod_upsel').owlCarousel({
		responsive:{
      0:{
          items: 1
      },
      575:{
          items: 2
      },
			768:{
          items: 4
      }
    }
	});
	$('.wiev_prod_bl .slider_prod_upsel_2').owlCarousel({
		margin: 20,
		responsive:{
      0:{
          items: 1
      },
      575:{
          items: 2
      },
			768:{
          items: 4
      }
    }
	});
	$('.slider_prod_upsel_2').owlCarousel({
		margin: 20,
		responsive:{
      0:{
          items: 1
      },
      575:{
          items: 2
      },
			768:{
          items: 3
      }
    }
	});
	$('.slider.product-responsive').slick({
	 slidesToShow: 1,
	 slidesToScroll: 1,
	 arrows: false,
	 fade: true,
	 asNavFor: '.slider.product-responsive-thumbnail'
	});
	$('.slider.product-responsive-thumbnail').slick({
	 slidesToShow: 5,
	 slidesToScroll: 1,
	 asNavFor: '.slider.product-responsive',
	 dots: false,
	 centerMode: true,
	 focusOnSelect: true,
	 slickNext: '<a></a>'
	});

	$('.lazy').Lazy();

	$('.gift_menu_right').click(function(){
		$('.right_menu_index').toggleClass('active');
		$('.over_right').toggle();
	})
	$('.over_right').click(function(){
		$('.right_menu_index').removeClass('active');
		$('.over_right').hide();
	})

	$('.slick-slide a').fancybox();

	$('a[href="#pokupat"]').click(function(event){
		event.preventDefault();
	});

	$('.reviews_tab  a').click(function(){
		$('.blog-add-comment .bx_medium.bx_bt_button').trigger('click');
	});

	// плавыный скролл
  $('.item_left_menu_index').click( function(event){
    event.preventDefault();
    var scroll_el = $(this).attr('href');
		var posit_block = $(scroll_el).offset().top - 100;
    $('html, body').animate({ scrollTop: posit_block }, 500);
    return false;
  });

	// скрол главной для меню левого
	var count=0;
	var block_arr = [];
	var block_id = [];
	$('.sw-wootab-slider.sw-ajax.sw-woo-tab-default').each(function(){
		block_arr[count] = $(this).offset().top;
		block_id[count] = $(this).attr('id');
		count++;
	});
	console.log(block_arr);
	console.log(block_id);
	$(document).scroll(function(){
		var documentTopScroll = $(document).scrollTop() + 200;
		if(documentTopScroll > block_arr[0]){
			$('.left_menu_index').addClass('active');
		}else{
			$('.left_menu_index').removeClass('active');
		}
		for(var i = 0; i<count; i++){
			if(documentTopScroll > block_arr[i] && documentTopScroll < block_arr[i+1]){
				$('.item_left_menu_index').removeClass('active');
				$('.left_menu_index').find('a[href="#'+block_id[i]+'"]').addClass('active');
			}else if(documentTopScroll > block_arr[i] && !block_arr[i+1]){
				$('.item_left_menu_index').removeClass('active');
				$('.left_menu_index').find('a[href="#'+block_id[i]+'"]').addClass('active');
			}
		}
	});

	$('#revo-totop').click(function(event){
		event.preventDefault();
		var top = 0;
		$('body,html').animate({scrollTop: top}, 1500);
	})

	var offsetHeader = $('.header-mid').offset().top;
	function scroll_doc(){
		var documentTopScroll = $(document).scrollTop();
		if(documentTopScroll > offsetHeader){
				$('.header-mid').addClass('scroll');
				$('header').addClass('scroll');
				$('.contact-us').hide();
		}else{
			$('.header-mid').removeClass('scroll');
			$('header').removeClass('scroll');
			$('.contact-us').show();
		}
	}
	scroll_doc();
	$(document).scroll(function(){
		scroll_doc();
		if($(document).scrollTop() > 500){
			$('#revo-totop').css('transform','translateY(0)');
		}else{
			$('#revo-totop').css('transform','translateY(150px)');
		}
	})
	$(document).resize(function(){
		offsetHeader = $('.header-mid').offset().top;
		scroll_doc();
	})

	$('input[name="phone"], input[name="PHONE"], [name="ORDER_PROP_3"]').mask("+375 (99) 999-99-99");

	$('#tab-instr a').each(function(){
		$(this).attr('target','_blank');
	})

	$('.btn_mobile_list_catalog').click(function(){
		$('.menu_catalog_sidebar').addClass('active')
		$('.overflow_list_cat').css('display','block').animate({ opacity: 1 }, 300);
	})
	$('.overflow_list_cat, .close_list_cat').click(function(){
		$('.menu_catalog_sidebar').removeClass('active')
		$('.overflow_list_cat').animate({ opacity: 0 }, 300, function() {
	    $('.overflow_list_cat').css('display','none');
	  });
	})

	$('.mobile_menu_button').click(function(){
		$(this).addClass('active')
		$('.mobile_menu').addClass('active')
		$('.overflow_mobile_menu').css('display','block').animate({ opacity: 1 }, 300);
		$('.lk_user, .top-form-minicart').hide(300);
	})
	$('.overflow_mobile_menu, .close_mob_menu').click(function(){
		$('.mobile_menu_button').removeClass('active')
		$('.mobile_menu').removeClass('active')
		$('.overflow_mobile_menu').animate({ opacity: 0 }, 300, function() {
	    $('.overflow_mobile_menu').css('display','none');
	  });
		$('.lk_user, .top-form-minicart').show(300);
	})

	$("#form_opt").submit(function(){ // перехватываем все при событии отправки
		var form = $(this); // запишем форму, чтобы потом не было проблем с this
		var data = form.serialize(); // подготавливаем данные
		$.ajax({ // инициализируем ajax запрос
			type: 'POST', // отправляем в POST формате, можно GET
			url: '/form_opt.php', // путь до обработчика, у нас он лежит в той же папке
			data: data, // данные для отправки
			success: function(data){ // событие после удачного обращения к серверу и получения ответа
				if (data == 'ok') { // если обработчик вернул ошибку
					$.fancybox.open('<div class="alert_form">Ваше сообщение успешно отправлено!</div>');
					setTimeout(function(){
						$.fancybox.close();
						form.find('.close_form').trigger('click');
					}, 3000);
				}else{
					$.fancybox.open('<div class="alert_form">Ошибка! Сообщение не отправлено!</div>');
					setTimeout(function(){
						$.fancybox.close();
						form.find('.close_form').trigger('click');
					}, 3000);
				}
			},
			error: function (xhr, ajaxOptions, thrownError) { // в случае неудачного завершения запроса к серверу
				alert(xhr.status); // покажем ответ сервера
				alert(thrownError); // и текст ошибки
			}
		});
		return false; // вырубаем стандартную отправку формы
	});

	$('.menu_catalog_sidebar i').click(function () {
		$(this).parent().children('ul').slideToggle();
	})

	$('.basket-item-amount-btn-minus, .basket-item-amount-btn-plus, .basket-item-actions-remove').click(function(){
		// setTimeout(function(){
		// 	location.reload();
		// }, 500);
	});
});
