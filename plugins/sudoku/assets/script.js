/**
 * Debug class
 * @copyright Hoangweb.COM
 * @param line
 */
function get_debugger(line)
{
    var p=this;

    p.bound=document.createElement('div');
    p.bound.style.cssText='position:fixed; border:1px solid gray;background:#ffffff;opacity:0.5;filter:alpha(opacity=50);';

    p.title=document.createElement('div');
    p.title.style.cssText='background:gray;color:#ffffff;';
    jQuery(p.title).html('debugger');

    p.contents=document.createElement('div');
    p.contents.style.cssText='overflow:auto;width:300;height:400;';

    p.line_contents=[];

    p.active_line;

    p.bound.appendChild(p.title);
    p.bound.appendChild(p.contents);
    document.body.appendChild(p.bound);

    /**
     * set line
     * @param str
     */
    p.set_line=function(str)
    {
        var l=document.createElement('div');
        l.style.cssText="border-bottom:3px solid gray;";
        p.line_contents.push(l);
        p.active_line=p.line_contents[p.line_contents.length-1];
        p.content(str);
        p.init();
    }
    /**
     * init
     */
    p.init=function()
    {
        p.contents.innerHTML='';
        for(var i in p.line_contents)
            p.contents.appendChild(p.line_contents[i]);
    }
    /**
     * get line
     * @param i
     * @returns {get_debugger}
     */
    p.get_line=function(i){
        if(i<0) i=0;
        if(i>=p.line_contents.length) i=p.line_contents.length-1;
        p.active_line=p.line_contents[i];
        return p;
    }
    /**
     * update content
     * @param str
     * @returns {innerHTML|*|string|innerHTML|innerHTML|getData}
     */
    p.content=function(str){
        if(p.active_line)
        {
            if(str!==null) p.active_line.innerHTML=str;
            else return p.active_line.innerHTML;
        }else
            p.get_line(0).content(str);
    }


    if(line) while(line--) p.set_line('');
    p.init();
}
/**
 * set debug content
 * @param line
 */
function hwdoku_debug(line) {
    if(typeof hwdoku_debugger != 'undefined') {
        hwdoku_debugger.get_line(1).content(line);
    }
}