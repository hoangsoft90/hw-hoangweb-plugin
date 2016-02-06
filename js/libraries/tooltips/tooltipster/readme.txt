- insert bellow code within head tag.
<code>
    <link rel="stylesheet" type="text/css" href="css/tooltipster.css" />
    <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.7.0/jquery.min.js"></script>
    <script type="text/javascript" src="js/jquery.tooltipster.js"></script>
    <script type="text/javascript" src="doc/js/scripts.js"></script>
</code>

+ more styles:
<code>
    <link rel="stylesheet" type="text/css" href="css/themes/tooltipster-light.css" />
    <link rel="stylesheet" type="text/css" href="css/themes/tooltipster-noir.css" />
    <link rel="stylesheet" type="text/css" href="css/themes/tooltipster-punk.css" />
    <link rel="stylesheet" type="text/css" href="css/themes/tooltipster-shadow.css" />
</code>

- tooltip HTML markup.
<code>
<span id="demo-default" title="Hi! This is a tooltip.">Hover</span>

<a href="http://calebjacob.com" class="ketchup tooltip" title="This is my link's tooltip message!">Link</a>
</code>

- JS:
<code>
<script>
//initial tooltip
$(function(){
     $('#demo-default,.tooltip').tooltipster({
          offsetY: 2
     });
});
</script>
</code>

- pretty tooltip:
<code>
<script type="text/javascript" src="doc/js/prettify.js"></script>
<script>
$(function(){
     .....initi tooltip.....
    prettyPrint();
});
</script>
</code>

- change tooltip theme.
<code>
$('#demo-multiple').tooltipster({
        //accept: tooltipster-light, tooltipster-shadow, tooltipster-light, tooltipster-punk, tooltipster-noir
          theme: 'tooltipster-punk'
     });
</code>

- content as HTML.
<code>
<script>
$(document).ready(function() {
            $('.tooltip').tooltipster({
                contentAsHTML: true
            });
        });
</script>
<img src="doc/images/browser-chrome.png" alt="Chrome" class="tooltip" title="&#x3C;strong&#x3E;sdfdsf&#x3C;/strong&#x3E; Support" />
</code>

=> note: HTML should encode entities. Use online tool to convert character code:

- custom tooltpip content.
<code>
<span id="demo-html">Hover</span> Fixed width, position, &amp; HTML.
<script>
$(function() {
     $('#demo-html').tooltipster({
          content: $('<img src="doc/images/spiderman.png" width="50" height="50" /><p style="text-align:left;"><strong>Soufflé chocolate cake powder.</strong> Applicake lollipop oat cake gingerbread.</p>'),
          // setting a same value to minWidth and maxWidth will result in a fixed width
          minWidth: 300,
          maxWidth: 300,
          position: 'right'
     });
});
//other example
$('#demo-html').tooltipster({
      content :'Hello world!'
});
</script>
</code>

- animation position.
<code>
<script>
$('.tooltip').tooltipster({
          animation: 'grow'  //ie: swing,East,fall,fade,slide
          position: 'top'   //ie: left,top,right,bottom
     });
</script>
</code>

- Multiple tooltips on a single element.
<code>
<script>
$('#demo-multiple').tooltipster({
          animation: 'swing',
          content: 'North',
          multiple: true,
          position: 'top'
     });
     $('#demo-multiple').tooltipster({
          content: 'East',
          multiple: true,
          position: 'right',
          theme: 'tooltipster-punk'
     });
     $('#demo-multiple').tooltipster({
          animation: 'grow',
          content: 'South',
          delay: 200,
          multiple: true,
          position: 'bottom',
          theme: 'tooltipster-light'
     });
     $('#demo-multiple').tooltipster({
          animation: 'fall',
          content: 'West',
          multiple: true,
          position: 'left',
          theme: 'tooltipster-shadow'
     });
</script>
</code>