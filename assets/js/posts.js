/*const wp_host = 'https://www.cruise-volna.ru/';
const request_uri = window.location.href;
const request_path = window.location.pathname;
var post_slug = '';

if(request_uri.indexOf('#')!== -1) {
    post_slug = request_uri.split('#')[1];
    if(request_uri.indexOf('?')!== -1){
        post_slug = request_uri.split('?')[0];
    }
    if(request_uri.indexOf('&')!== -1){
        post_slug = request_uri.split('&')[0];
    }
}*/

jQuery(document).ready(function($) {

    /*** Page ****/
    /*if(typeof page_slug !== "undefined"){
        $.getJSON(wp_host + 'wp-json/wp/v2/pages?slug=' + page_slug).done(function (data) {
            $('.blog-item').append(data[0].content.rendered);
            $('table').addClass('table table-bordered');
        });
    }*/

    /*** News ****/
   /* if(request_path.indexOf('news') !== -1){
        if(post_slug.length > 0) {
            getPostSingle(post_slug);
        }else {
            getAllNews();
        }
    }*/

    /*** News ****/
    /*if(request_path.indexOf('post') !== -1){
        if(post_slug.length > 0) {
            getPostSingle(post_slug);
        }
    }

    $.getJSON(wp_host + 'wp-json/wp/v2/posts?categories=22&per_page=1').done(function (data) {
        const recent = $('#recent-post').empty();
        recent.append($('<p>', {
            'html': data[0].excerpt.rendered + '<a href="/news/#' + data[0].id + '">Подробнее..</a>'
        }));
    });*/



    /***** News all page ***********/

    /*function getAllNews() {
        const blog = $('#content').append('<div style="width: 250px; margin: 0 auto;"><img src="/assets/images/spinner.gif" width="250" /></div>');


        $.getJSON(wp_host + 'wp-json/wp/v2/posts?categories=22&_embed&per_page=10').done(function (data) {
            //console.log(data);
            blog.empty();
            $.each(data, function (index, post) {
                blog.append($('<div/>', {
                    'class': 'post type-post status-publish format-standard hentry category-news entry'
                })
                    .append('<h2><a href="/news/#' + post.id + '">' + post.title.rendered + '</a></h2>').on('click', function(){
                        location.reload();
                    })
                    .append($('<div/>', {
                            'class': 'entry-content',
                            'html': post.excerpt.rendered
                        })
                            .append($('<div/>', {
                                'class': 'post-meta'
                            })).append($('<span/>', {
                                'class': 'categories',
                                'html': 'Опубликовано в теме: Новости'
                        }))
                    ));
            });

        });
    }

    function getPostSingle(id) {
        const blog = $('#content').append('<div style="width: 250px; margin: 0 auto;"><img src="/assets/images/spinner.gif" width="250" /></div>');


        $.getJSON(wp_host + 'wp-json/wp/v2/posts/' + id).done(function (data) {
            blog.empty();
            //$.each(data, function (index, post) {
                blog.append($('<div/>', {
                    'class': 'post type-post status-publish format-standard hentry category-news entry'
                })
                    .append('<h2>' + data.title.rendered + '</h2>')
                    .append($('<div/>', {
                            'class': 'entry-content',
                            'html': data.content.rendered
                        })
                    ));
            });

        //});
    }*/

    //   Contact form------------------
    $(document).on('submit','#contactform',function(e){
        e.preventDefault();
        var a = $(this).attr("action");
        $("#submit").attr("disabled", "disabled");
        $.ajax({
            type : 'POST',
            url : a,
            data : $(this).serialize()
        }).done(function( msg ) {
            console.log(msg.result);
            var r = msg.result;
            if(r === 'success') {
                $(".email-response").html('Ваше письмо успешно отправлено. Менеджер свяжется с Вами в ближайшее время.').show();
                //$("#submit").removeAttr("disabled");
            }
        });
        return false;
    });

});
