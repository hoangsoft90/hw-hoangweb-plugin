- Sticky is a jQuery plugin that gives you the ability to make any element on your page always stay visible.
<code>
<script src="jquery.js"></script>
<script src="jquery.sticky.js"></script>
<script>
  $(document).ready(function(){
    $("#sticker").sticky({topSpacing:0});
  });
</script>
</code>

- HTML markup:
<CODE>
<div id="sticker">
<div class="main-your-content">....</div>
</div>
</CODE>

- note: you should create outer tag to hold sticky content, don't set sticket to main content inside.
<code>
//right way
$('#sticker').sticky({topSpacing:100});

//don't please
$('.main-your-content').sticky({topSpacing:100});
</code>

- To unstick an object.
<code>
<script>
  $("#sticker").unstick();
</script>
</code>
