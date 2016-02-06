/**
 * Sudoku
 * @param enabled_valid
 * @constructor
 */
function HW_Sudoku(options) {
    var __item = [];
    var _enabled_valid = false;
    var matrix_data = "";
    var size = 3;   //game size
    var win_msg = 'Congratulation, You are win ! ^_^';
    var fail_msg = 'Wrong table.';

    /**
     * parse result that got from server
     * @param html
     */
    function parse_text(html)
    {
        var r=document.createElement('div');
        jQuery(r).html(html);
        return r.getElementsByTagName('textarea')[0].value;

        //return r.value.replace(/(\r\n|\n|\r|\s+)/gm, "");
    }
    /**
     * disabled all inputs
     * @param opt
     */
    function disabled_all(opt)
    {
        var items=document.getElementById('sudoku').getElementsByTagName('input');
        for(var i=0;i<items.length;i++)
        {
            items[i].disabled=opt;
        }
    }

    /**
     * valid input
     * @param O
     */
    function valid_input_item(O){
        if(!jQuery.trim(O.value)
            ||!parseInt(O.value)
            ||parseInt(O.value)<0 || parseInt(O.value)> size
            ){
            O.value='';
            return false;
        }
        return true;
    }
    /*
     event for each item
     */
    this._input_item_event = function(O){
        if(!valid_input_item(O)) return;
        if(_enabled_valid)
        {
            var loader, ij, url, grid;

            ij = O.getAttribute('pos').split('-');
            grid=document.getElementById('matrix_string').value;//jQuery('#sudoku').html();

            url= __hwdoku.check_item_url+ '&matrix='+grid+'&i='+ij[0]+'&j='+ij[1]+'&a='+O.value+'&size='+ size;

            disabled_all(true);

            jQuery.get(url,function(dt){
                var r=parse_text(dt);

                if(r.match(/\[FALSE\]/g))
                {
                    jQuery('#matrix_string').text(r.replace(/\[FALSE\]/g,''));

                    O.value='';
                    alert('invalid !');
                    disabled_all(false);
                    return;
                }
                if(r.match(/\[DONE\]/g))
                {
                    jQuery('#matrix_string').text(r.replace(/\[DONE\]/g,''));
                    alert(win_msg);

                    return;
                }

                jQuery('#matrix_string').text(r);

                disabled_all(false);
            });
        }
        else
        {
            var i, items=document.getElementById('sudoku').getElementsByTagName('input'),
                items_string='',
                url;

            for(i=0;i<items.length;i++){
                if(!items[i].value) return;
                else items_string+=items[i].getAttribute('pos')+':'+items[i].value+',';
            }

            items_string=items_string.substr(0,items_string.length-1);

            url= __hwdoku.valid_game_url+ '&matrix='+ matrix_data +'&items_string='+items_string+'&size='+ size;

            disabled_all(true);

            jQuery.get(url,function(dt){
                if(!parseInt(parse_text(dt))) {
                    disabled_all(false);
                    alert(fail_msg);
                }
                else
                    alert(win_msg);
            });

        }
    }
    /**
     * suggest item
     */

    this.suggest_item = function(){
        if(!__item.length) return;

        var ij=__item[0].split('-');

        var matrix=document.getElementById('matrix_string').value;

        var matrix_origin= matrix_data;

        var url= __hwdoku.suggest_item_url+ "&matrix_origin="+matrix_origin+"&matrix="+matrix+"&i="+ij[0]+"&j="+ij[1]+'&size=' + size;

        disabled_all(true);

        jQuery.get(url,function(dt){
            var rs=parse_text(dt).split('*');

            __item[1].value=rs[0];
            jQuery('#matrix_string').text(rs[1]);

            if(rs.length==3) alert(win_msg);
            else
                disabled_all(false);
        });
    }
    /**
     * get focus item
     */
    this.focus_item = function(p)
    {
        __item[0]=p.getAttribute('pos');
        __item[1]=p;
    }

    /**
     * set game settings
     * @param opts
     */
    this.options = function(opts) {
        if(typeof opts['enabled_valid'] != undefined) _enabled_valid = opts['enabled_valid']? true : false; //enable validation
        if(typeof opts['matrix_data'] != undefined) matrix_data = opts['matrix_data'];  //matrix origin
        if(typeof opts['size'] != undefined) size = opts['size'];  //game size
        if(typeof opts['win_msg'] != undefined) win_msg = opts['win_msg'];  //game done message
        if(typeof opts['fail_msg'] != undefined) fail_msg = opts['fail_msg'];  //game fail message

    }
    //initialize
    if(options) this.options(options);
}