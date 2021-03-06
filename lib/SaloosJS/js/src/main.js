// Code haye moshtarak beine tamame safahat
// Az jomle meqdar dehi haye avvalie
// va etesale roydad haye mokhtalef

$(document).ready(function()
{
  var regex = /(?:\?|&)?lang=\w*/g;
  if(regex.test(location.search)) {
    Navigate({
      url: location.pathname + location.search.replace(regex, '') + location.hash,
      replace: true
    });
  }

    /* Blur inputs with ESC key */
  $(document).keydown(function(e) {
    if(e.keyCode === 27) {
      $('input').blur();
    }
  });

  // Ajaxify links and forms
  $(document).on('submit', 'form', function(e)
  {
    if($(this).hasAttr('data-action')) return;

    e.preventDefault();
    $(this).ajaxify();

  });

  $(document).on('click', '[data-ajaxify]', function(e)
  {
    e.preventDefault();
    $(this).ajaxify({link: true});
  });


  // $(document).on('change', '#langlist', function() {
  //   var regex = /(?:\?|&)?lang=\w*/g;
  //   var srch = location.search.replace(regex, ''),
  //       url = location.pathname + (srch ? srch + '&' : '?') + 'lang='+this.value;

  //   location.replace(url);
  // });

  $(document).on('keypress', 'input[type="date"],\
                            input[type="datetime-local"],\
                            input[type="number"],\
                            input[type="tel"],\
                            input#mobile', function(e) {
                              if(this.getAttribute('data-allowpersian') !== null || e.which < 32) return;
                              e.preventDefault();

                              if(e.which === 32) return;

                              var val = '';
                              var key = String.fromCharCode(e.which);

                              if(e.which <= 1785 && e.which >= 1776) {
                                try {
                                  var start = this.selectionStart,
                                      end = this.selectionEnd;

                                  val = this.value.slice(0, start) + key.toEnglish() + this.value.slice(end);
                                } catch(e) {
                                  val = this.value + key.toEnglish();
                                }
                              } else {
                                val = this.value + key;
                              }

                              if(isNaN(+val)) {
                                $this = $(this);
                                $this.addClass('invalid');
                                setTimeout(function() {
                                  $this.removeClass('invalid');
                                }, 500);
                              } else {
                                this.value = val;
                              }
                              return false;
                            });



  // 'a:not([target="_blank"])\
  // :not([data-ajaxify])\
  // :not([data-action])\
  // :not([data-modal])',
  $(document.body).on('click', 'a', function(e)
  {
    var $this = $(this);

    if(
        $this.attr('target') === '_blank' ||
        $this.hasAttr('data-ajaxify') ||
        $this.hasAttr('data-action') ||
        $this.hasAttr('data-direct') ||
        $this.hasAttr('data-modal') ||
        $this.isAbsoluteURL()
      )
      {
        return;
      }

    e.preventDefault();

    if(!$this.attr('href') || $this.attr('href').indexOf('#') > -1) return;

    var href = $this.attr('href');

    if(href.indexOf('lang=') > -1) return location.replace(href);

    Navigate({
      url: href,
      fake: !!$this.attr('data-fake')
    });
  });
});

// set scroll to top before unload
$(window).on('beforeunload', function()
{
    $(window).scrollTop(0);
});


route('*', function()
{
  // $('input').prop('lang', 'en');
  /* MODALS */

  // Things to do after closing/opening modal
  $('.modal', this).on('close', function(_e, _obj)
  {
    var scrollTop = parseInt($('html').css('top'));
    $('html').removeClass('noscroll');
    $('html,body').scrollTop(-scrollTop);

    var $this = $(this);

    $this.removeClass('visible');

    $.each($this.data(), function(key)
    {
      if(key === 'modal') return;
      $(this).removeAttr(key);
    });
  });


  $('.modal', this).on('open', function()
  {
    // fix scroll on opening modal
    if($(document).height() > $(window).height())
    {
      var scrollTop = ($('html').scrollTop()) ? $('html').scrollTop() : $('body').scrollTop();
      $('html').addClass('noscroll').css('top',-scrollTop);
    }

    $(this).addClass('visible');

    var $send = $('[data-ajaxify]', this);

    if (!$send.length) return;

    $.each($send.data(), function(key)
    {
      if(key === 'modal') return;

      $send.removeAttr(key);
    });

    $send.copyData(this, ['modal']);
  });


  $(".panel .panel-heading", this).click(
    function ()
    {
      var el = $(this).parent();
      if(el.hasClass('closed'))
      {
        el.children('.panel-footer').slideDown(300);
        el.children('.panel-body').slideDown(600, function(){
          el.removeClass('closed');
        });
        // el.children('.panel-body').fadeIn();
      }
      else
      {
        el.children('.panel-footer').slideUp(300);
        el.children('.panel-body').slideUp(500, function(){
          el.addClass('closed');
        });
        // el.children('.panel-body').hide();
      }
    }
  );


  // run watchScroll func to watch all elements
  watchScroll(this);

}).once(function(_module)
{
  // run only one time after changing url
  var hashtag     = $(window.location.hash);
  var hashtagFake = urlParam('hashtag')? $('#'+ urlParam('hashtag')): '';
  // handle scroll
  if(hashtag.length)
  {
    // after 0.5s of loading page scroll to specefic area if exist
    scrollSmooth(hashtag, null, 1000);
  }
  else if(hashtagFake.length)
  {
    scrollSmooth(hashtagFake, null, 1000);
  }
  else if(_module !== undefined)
  {
    findPushStateScroll();
  }
});


/**
 * [urlParam description]
 * @param  {[type]} _request [description]
 * @return {[type]}          [description]
 */
function urlParam(_request)
{
  var sPageURL = decodeURIComponent(window.location.search.substring(1)),
    sURLVariables = sPageURL.split('&'),
    sParameterName,
    i;
  var result = null;
  var params = [];

  // detect and set key and value into params
  for(i = 0; i < sURLVariables.length; i++)
  {
    var cParameter   = sURLVariables[i].split('=');
    var sParameterValue = cParameter[1] === undefined ? true : cParameter[1];
    params[cParameter[0]] = sParameterValue;
  }

  // if params is requested, return it
  // else return all of params into object
  if(_request)
  {
    if(params[_request])
    {
      result = params[_request];
    }
  }
  else
  {
    result = params;
  }

  return result;
};


/**
 * [watchScroll description]
 * @param  {[type]} _this [description]
 * @return {[type]}       [description]
 */
function watchScroll(_this)
{
  // watch simple links
  $('a[href*="#"]:not([href="#"])', _this).click(function()
  {
    if (location.pathname.replace(/^\//,'') == this.pathname.replace(/^\//,'') && location.hostname == this.hostname)
    {
      var hash   = this.hash;
      var target = $(hash);
      target = target.length ? target : $('[name=' + hash.slice(1) +']');
      if (target.length)
      {
        scrollSmooth(target, hash);
        return false;
      }
    }
  });

  $('[data-scroll-to]', _this).click(function()
  {
    var hashtag = $(this).attr('data-scroll-to');
    var target  = $('#'+ hashtag);
    scrollSmooth(target, hashtag);
  });
}


/**
 * find last element with data-scroll attr and goto this position
 * @return {[type]} [description]
 */
function findPushStateScroll()
{
  var target = $('[data-scroll]:last');
  scrollSmooth(target, null, 200, target.attr('data-scroll'));
}


/**
 * [scrollSmooth description]
 * @param  {[type]} _target  [description]
 * @param  {[type]} _hashtag [description]
 * @param  {[type]} _timing  [description]
 * @return {[type]}          [description]
 */
function scrollSmooth(_target, _hashtag, _timing, _arg)
{
  if(_target && _target.length == 1)
  {
    if(_timing)
    {
      setTimeout(function()
      {
        scrollSmoothTo(_target, _hashtag, (_timing*2), _arg);
      }, _timing);
    }
    else
    {
      scrollSmoothTo(_target, _hashtag, 0, _arg);
    }
  }
}


/**
 * [scrollSmoothTo description]
 * @param  {[type]} _target  [description]
 * @param  {[type]} _hashtag [description]
 * @param  {[type]} _timing  [description]
 * @return {[type]}          [description]
 */
function scrollSmoothTo(_target, _hashtag, _timing, _arg)
{
  if(_arg === 'off')
  {
    return false;
  }
  // if wanna goto near top of page or exactly top, set target to zero
  var targetOffset = _target.offset().top - 10;
  // if target is near top of page, scroll to top
  if(targetOffset<100 || _arg === 'top')
  {
    targetOffset = 0;
  }
  // get curent pos and calc diff to that location
  var currentPos = (window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop || 0);
  var diff       = Math.abs(currentPos - targetOffset);
  // do not run scroll on little ones
  if(diff < 50)
  {
    return false;
  }
  // if timing is more than 0.5s then replace with smaller one
  if(_timing>500)
  {
    _timing = 300;
  }
  // calc timing with size of scroll
  _timing  = Math.round(diff/2);
  // if timing is very short, replace with 0.2s
  if(_timing<200)
  {
    _timing = 200;
  }

  var page = $("html, body");
  // add event to page to allow to stop scroll
  page.on("scroll mousedown wheel DOMMouseScroll mousewheel keyup touchmove", function()
  {
    page.stop();
  });
  // animate to target position
  page.animate(
  {
    scrollTop: targetOffset
  }, _timing, function()
  {
    if(_hashtag)
    {
      window.location.hash = _hashtag;
    }
    // remove events form page
    page.off("scroll mousedown wheel DOMMouseScroll mousewheel keyup touchmove");
  });
}