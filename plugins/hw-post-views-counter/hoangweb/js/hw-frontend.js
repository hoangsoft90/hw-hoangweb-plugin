jQuery(function($){
    if(!__hw_post_views_count.options || !__hw_post_views_count.options.use_firebase
        || typeof Firebase == 'undefined' /*|| !__hw_post_views_count.postID*/  //since we count views for any object and get their information from that element (HTML5)
        )
        return;   //valid
    $.each($('span.hw-post-views-count'), function(i, e) {

        var element = $(e).addClass('preloading'),
            post_views_icon = $(e).parent().find('.post-views-icon'),
            postID = null;  //post id

        //post id
        if(element.data('id')) postID = element.data('id');
        else if(__hw_post_views_count.postID) postID = __hw_post_views_count.postID;


        //valid
        if(postID == null) return;      //required at least param postID

        post_views_icon.hide();  //hide post views icon

        var blogStats = new Firebase(__hw_post_views_count.get_postviews_firebase_url(postID));

        blogStats.once('value', function(snapshot)
        {
            var data = snapshot.val(),
                isnew = false,
                //post permalink
                permalink = null,
                post_title = '',    //post title
                count = 1; //update new count?

            //get permalink
            if(__hw_post_views_count.post_permalink) {
                permalink = __hw_post_views_count.post_permalink;
            }
            else if(element.data('permalink')) permalink = element.data('permalink');
            else permalink = window.location.href;

            //post title
            if(__hw_post_views_count.post_title) post_title = __hw_post_views_count.post_title;
            else if(element.data('title')) {
                post_title = element.data('title');
            }
            else  post_title = element.attr('title');

            //update count?
            if(typeof element.data('count') != 'undefined'
                && (element.data('count') == 'false' || element.data('count') == false) ){
                count = 0;  //disable count for this post
            }

            post_views_icon.fadeIn().removeClass('hw-hidden');    //show post views icon

            if(data == null) {

                data= {};

                data.value = 0;

                data.url = permalink;

                data.id = postID;

                data.title = post_title;

                isnew = true;
                //console.log(data);
            }

            element.removeClass('preloading').addClass('post-views').text(data.value);
            //save new views count to this post
            data.value++;

            //if(window.location.pathname!='/')
            if(count)
            {
                if(isnew) blogStats.set(data);

                else blogStats.child('value').set(data.value);
            }
            console.log(blogStats.toString(),'update:', count);
        });

    });
});
/**
 * get firebase db url for current post
 * @param id post id to count views
 */
__hw_post_views_count.get_postviews_firebase_url = function(id){
    var fbdb_name = __hw_post_views_count.options.firebase_db,
        fbpath = __hw_post_views_count.options.firebase_path.replace(/^[\/\s]+|[\/\s]+$/g, ''),
        post_id = id? id : __hw_post_views_count.postID;

    return 'https://'+fbdb_name+'.firebaseio.com/'+fbpath+'/posts/id/'+post_id;
}