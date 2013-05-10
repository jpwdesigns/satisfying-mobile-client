$(function(){
    var hash = window.location.hash;
    if (hash && $(hash).size() == 0){
        alert('Sorry, page not found :(');
        $.mobile.changePage('');//Redirect to the first page.
    } 
    $('.editbutton').live("click",function(e){
        e.preventDefault();
        var contentId = $(this).attr("rel");
        var content = $('#'+contentId).html().trim();
        var contentEsc = encodeURIComponent(content);
        var name = $(this).attr("data-name");
        var url = $(this).attr('data-url');
        var redirUrl = $(this).closest('[data-role="page"]').attr('data-url');
        $('#'+contentId).replaceWith('<form action="'+redirUrl+'">\n\
                                        <textarea id="'+contentId+'" cols="40" rows="8" name="'+name+'" rel="'+contentEsc+'">'+content+'</textarea>\n\
                                        <fieldset class="ui-grid-a">\n\
                                        <div class="ui-block-a">\n\
                                        <button type="submit" class="canceledit" data-theme="c" rel="'+contentId+'">Cancel</button></div>\n\
                                        <div class="ui-block-b">\n\
                                        <button class="submitedit" type="submit" rel="'+contentId+'" data-theme="b">Update</button></div>\n\
                                        </fieldset><input type="hidden" id="url_'+contentId+'" name="url" value="'+url+'" />\n\
                                    </form>\n\
                                    ');
        $(this).closest('.toolbox').hide();
        $('#'+contentId).textinput();
        $('button').button();
    });
    $('.canceledit').live("click", function(e) {
        e.preventDefault();
        var contentId = $(this).attr("rel");
        var content = decodeURIComponent($('#'+contentId).attr("rel"));
        var name = $('#'.contentId).attr("data-name");
        var url = $('#url_'+contentId).val();
        $('#'+contentId).parent('form').replaceWith('<div class="toolbox"><div><a href="" data-role="button" data-icon="qo-edit" data-iconpos="notext" data-theme="c" class="editbutton" rel="'+contentId+'" data-name="'+name+'" data-url="'+url+'">Edit</a></div><div>Edit</div></div><p id="'+contentId+'">'+content+'</p>');
        $('.editbutton').button();
    });
    $('.submitedit').live("click", function(e) {
        e.preventDefault();
        $.mobile.showPageLoadingMsg();
        var contentId = $(this).attr("rel");
        var content = $('#'+contentId).val();
        var name = $('#'.contentId).attr("data-name");
        var url = $('#url_'+contentId).val();
        var redirUrl = $(this).closest('[data-role="page"]').attr('data-url');
        var theForm = $(this).closest('form');
        var dataString = theForm.serialize();
        try {
            _gaq.push(['_trackEvent', "Help Action Button", "Edit", redirUrl]);  
        } catch(err) {}
        $.ajax({
              url: '/ajax/edit',
              data: dataString,
              type: 'POST',
              success: function() {                    
                    theForm.replaceWith('<div class="toolbox"><div><a href="" data-role="button" data-icon="qo-edit" data-iconpos="notext" data-theme="c" class="editbutton" rel="'+contentId+'" data-name="'+name+'" data-url="'+url+'">Edit</a></div><div>Edit</div></div><p id="'+contentId+'"></p>');
                    $('#'+contentId).text(content);
                    $('.editbutton').button();
                    $.mobile.hidePageLoadingMsg();
              },
              statusCode: {
                400: function() {
                    $.mobile.hidePageLoadingMsg();
                    alert("Unable to edit at this time");
                },
                 401: function() {
                    $.mobile.changePage( "/alerts/login?redir=" + redirUrl, {
                            transition: "pop",
                            changeHash: false,
                            reverse: false
                    });  
                },
                404: function() {
                  $.mobile.hidePageLoadingMsg();
                  alert("Page Not Found");
                },
                 500: function() {
                  $.mobile.hidePageLoadingMsg();
                  alert("Internal Server Error");
                },
                 503: function() {
                  $.mobile.hidePageLoadingMsg();
                  alert("Service Unavailable");
                }
              }
         });
    });
});
$(document).bind("pageinit", function(){ 
    $(function() {
        $('.type-interior').css('min-height','auto');
        var sb = $('.content-secondary').height();
        var wh = $(window).height();
        var hh = $('.ui-header').height();
        var fh = $('.ui-footer').height();
        var newheight = (wh - (fh + hh))-30;
        var ww = $(window).width(); 
        if (ww >= 650) {
            $('.content-secondary').css('min-height', newheight);
            $('.content-primary').css('min-height', newheight-70);
        }
    });
    $(window).resize(function() {
        $('.type-interior').css('min-height','auto');
        var sb = $('.content-secondary').height();
        var wh = $(window).height();
        var hh = $('.ui-header').height();
        var fh = $('.ui-footer').height();
        var newheight = (wh - (fh + hh))-30;
        var ww = $(window).width();
        if (ww >= 650) {
            $('.content-secondary').css('min-height', newheight);
            $('.content-primary').css('min-height', newheight-70);
        }
    });
   
    $('a#user_guide').click(function(){
        $.mobile.showPageLoadingMsg();
    });
    $('a#my_topics').click(function(){
        $.mobile.showPageLoadingMsg();
    });
    $('.submit').click(function(){
        $.mobile.showPageLoadingMsg();
    });
    $(function() { 
        var prod = $.cookie('product');
        if (typeof prod === undefined) {
            prod = '';
        } else {
            $('select#choose-product').each(function(){
                $(this).val(prod).selectmenu();
                $(this).val(prod).selectmenu("refresh");
            });
        }
                
        $('select#choose-product').change(function(){
            var prod = $(this).val();
            var redirUrl = $(this).closest('[data-role="page"]').attr('data-url');
            $.mobile.showPageLoadingMsg();
            $.cookie('product', null);
            $.cookie('product', prod, {expires: 30, path: '/'});
            try {
                _gaq.push(['_trackEvent', "Help Action Button", "ChangeProduct", redirUrl]);  
            } catch(err) {} 
            window.location = redirUrl;
        });
    });

        $('.metoobutton').bind( "change", function(e, ui) {
            e.preventDefault();
            $.mobile.showPageLoadingMsg();
            var topicId = $(this).attr('rel');
            var redirUrl = $(this).closest('[data-role="page"]').attr('data-url');
            if ($(this).hasClass('ui-btn-active')) {
                $.mobile.hidePageLoadingMsg();
            } else {
                try {
                    _gaq.push(['_trackEvent', "Help Action Button", "MeToo", redirUrl]);  
                } catch(err) {}
                $.ajax({
                      url: '/ajax/metoo?topic=' + topicId,
                      success: function() {                    
                            var currentValue = parseInt($("b.meTooCount_" + topicId).text(),10);
                            var newValue = currentValue + 1;
                            $("b.meTooCount_"+ topicId).text(newValue);
                            var currentText = $("small.meTooCount_"+ topicId).text();
                            var newText = currentText + " including you";
                            $("small.meTooCount_"+ topicId).text(newText);
                            $("#metoobutton-"+topicId).checkboxradio('disable');
                            $.mobile.hidePageLoadingMsg();
                      },
                      statusCode: {
                        400: function() {
                            $("#metoobutton-"+topicId).checkboxradio('disable');
                            $.mobile.hidePageLoadingMsg(); 
                        },
                         401: function() {
                            $.mobile.changePage( "/alerts/login?redir=" + redirUrl, {
                                    transition: "pop",
                                    changeHash: false,
                                    reverse: false
                            });  
                        },
                        404: function() {
                          $.mobile.hidePageLoadingMsg();
                          alert("Page Not Found");
                        },
                         500: function() {
                          $.mobile.hidePageLoadingMsg();
                          alert("Internal Server Error");
                        },
                         503: function() {
                          $.mobile.hidePageLoadingMsg();
                          alert("Service Unavailable");
                        }
                      }
                 });
            }
            
        });
        
        $('.followbutton').bind( "change", function(e, ui) {
            $.mobile.showPageLoadingMsg();
            var checkboxId = $(this).attr('id');
            var topicId = $(this).attr('rel');
            var redirUrl = $(this).closest('[data-role="page"]').attr('data-url');
            
            if ($(this).attr('checked')) {
                 try {
                    _gaq.push(['_trackEvent', "Help Action Button", "Follow", redirUrl]);  
                } catch(err) {} 
                $.ajax({
                  url: '/ajax/follow?topic=' + topicId,
                  success: function() {  
                    $("Label[for='" + checkboxId + "']").find('span.ui-btn-text').text('Following');
                    var currentValue = parseInt($("b.followerCount_" + topicId).text(),10);
                    var newValue = currentValue + 1;
                    $("b.followerCount_"+ topicId).text(newValue);
                    var currentText = $("small.followerCount_"+ topicId).text();
                    var newText = currentText + " including you";
                    $("small.followerCount_"+ topicId).text(newText);
                    $.mobile.hidePageLoadingMsg();
                  },
                  statusCode: {
                    400: function() {
                        $("Label[for='" + checkboxId + "']").find('span.ui-btn-text').text('Following');
                        var currentValue = parseInt($("b.followerCount_" + topicId).text(),10);
                        var newValue = currentValue + 1;
                        $("b.followerCount_"+ topicId).text(newValue);
                        var currentText = $("small.followerCount_"+ topicId).text();
                        var newText = currentText + " including you";
                        $("small.followerCount_"+ topicId).text(newText);
                        $.mobile.hidePageLoadingMsg(); 
                    },
                     401: function() {
                        $.mobile.changePage( "/alerts/login?redir=" + redirUrl, {
                                transition: "pop",
                                changeHash: false,
                                reverse: false
                        });              
                    },
                    404: function() {
                      $.mobile.hidePageLoadingMsg();
                      alert("Page Not Found");
                    },
                     500: function() {
                      $.mobile.hidePageLoadingMsg();
                      alert("Internal Server Error");
                    },
                     503: function() {
                      $.mobile.hidePageLoadingMsg();
                      alert("Service Unavailable");
                    }
                  }
                });
            } else { 
                 try {
                    _gaq.push(['_trackEvent', "Help Action Button", "UnFollow", redirUrl]);  
                } catch(err) {} 
                $.ajax({
                  url: '/ajax/unfollow?topic=' + topicId,
                  success: function() {   
                    $("Label[for='" + checkboxId + "']").find('span.ui-btn-text').text('Follow');
                    var currentValue = parseInt($("b.followerCount_" + topicId).text(),10);
                    var newValue = currentValue - 1;
                    $("b.followerCount_"+ topicId).text(newValue);
                    var currentText = $("small.followerCount_"+ topicId).text();
                    var newText = currentText.replace("including you","");
                    $("small.followerCount_"+ topicId).text(newText);
                    $.mobile.hidePageLoadingMsg();
                  },
                  statusCode: {
                    400: function() {
                    $("Label[for='" + checkboxId + "']").find('span.ui-btn-text').text('Follow');
                    var currentValue = parseInt($("b.followerCount_" + topicId).text(),10);
                    var newValue = currentValue - 1;
                    $("b.followerCount_"+ topicId).text(newValue);
                    var currentText = $("small.followerCount_"+ topicId).text();
                    var newText = currentText.replace("including you","");
                    $("small.followerCount_"+ topicId).text(newText);
                    $.mobile.hidePageLoadingMsg(); 
                    },
                     401: function() {
                        $.mobile.changePage( "/alerts/login?redir=" + redirUrl, {
                                transition: "pop",
                                changeHash: false,
                                reverse: false
                        });  
                    },
                    404: function() {
                      $.mobile.hidePageLoadingMsg();
                      alert("Page Not Found");
                    },
                     500: function() {
                      $.mobile.hidePageLoadingMsg();
                      alert("Internal Server Error");
                    },
                     503: function() {
                      $.mobile.hidePageLoadingMsg();
                      alert("Service Unavailable");
                    }
                  }
                });
            }      
        });

        $('.starbutton').bind( "change", function(e, ui) {
            $.mobile.showPageLoadingMsg();
            
            var checkboxId = $(this).attr('id');
            var replyId = $(this).attr('rel');
            var redirUrl = $(this).closest('[data-role="page"]').attr('data-url');
            var obj = $.mobile.path.parseUrl(redirUrl); 
            if ($(this).attr('checked')) {
                try {
                    _gaq.push(['_trackEvent', "Help Action Button", "Star", redirUrl]);  
                } catch(err) {} 
                $.ajax({
                  url: '/ajax/star.php' + obj.search + '&reply=' + replyId,
                  success: function() {   
                    $.mobile.hidePageLoadingMsg();
                  },
                  statusCode: {
                    400: function() {
                        $("#"+checkboxId).closest('form').remove();
                        $.mobile.hidePageLoadingMsg(); 
                    },
                     401: function() {
                        $.mobile.changePage( "/alerts/login?redir=" + redirUrl, {
                                transition: "pop",
                                changeHash: false,
                                reverse: false
                        });  
                    },
                    404: function() {
                      $("#"+checkboxId).closest('form').remove();  
                      $.mobile.hidePageLoadingMsg();
                    },
                     500: function() {
                      $.mobile.hidePageLoadingMsg();
                      alert("Internal Server Error");
                    },
                     503: function() {
                      $.mobile.hidePageLoadingMsg();
                      alert("Service Unavailable");
                    }
                  }
                });
            } else {
                try {
                    _gaq.push(['_trackEvent', "Help Action Button", "UnStar", redirUrl]);  
                } catch(err) {} 
                $.ajax({
                  url: '/ajax/unstar.php' + obj.search + '&reply=' + replyId,
                  success: function() {  
                    $.mobile.hidePageLoadingMsg();
                  },
                  statusCode: {
                    400: function() {
                        $("#"+checkboxId).closest('form').remove();
                        $.mobile.hidePageLoadingMsg(); 
                    },
                     401: function() {
                        $.mobile.changePage( "/alerts/login?redir=" + redirUrl, {
                                transition: "pop",
                                changeHash: false,
                                reverse: false
                        }); 
                    },
                    404: function() {
                        $("#"+checkboxId).closest('form').remove();
                        $.mobile.hidePageLoadingMsg();
                    },
                     500: function() {
                        $.mobile.hidePageLoadingMsg();
                        alert("Internal Server Error");
                    },
                     503: function() {
                        $.mobile.hidePageLoadingMsg();
                        alert("Service Unavailable");
                    }
                  }
                });          
            }      
        });

        $('a.loadmore-topics').click(function(e) {
            e.preventDefault();
            $.mobile.showPageLoadingMsg();
            var linkid = $(this).attr('id');
            var id = linkid.split("-",3);
            var ulid = 'topics-list-' + id[2];
            var params = $(this).attr('rel');
            var page = (parseInt($(this).attr('alt')) + 1);
            var nextpage = 'page='+page;
            var newparams = params.replace(/(?:&page=[0-9]*)/, '');
            newparams = newparams + '&' + nextpage;
            var redirUrl = $(this).closest('[data-role="page"]').attr('data-url');
            try {
                _gaq.push(['_trackEvent', "Help Action Button", "LoadMoreTopics", redirUrl]);  
            } catch(err) {} 
            $.ajax({
                  url: '/ajax/topics?' + newparams,
                  success: function(data) {  
                    
                    $('#'+ulid).append(data);
                    if (data.length > 3) {
                        $('#'+linkid).attr('rel',newparams);
                        $('#'+linkid).attr('alt',page);
                    } else {
                        $('#'+linkid).replaceWith('no more topics');
                    }
                    
                    $('#'+ulid).listview("refresh");
                    $.mobile.hidePageLoadingMsg();
                  },
                  error: function() {
                      $.mobile.hidePageLoadingMsg();
                      alert('Woops, there was a problem loading more topics. Please try again.');
                  }
            });
        });
});
