/**
 * Created by Hoang on 23/10/2015.
 */
/**
 * HW.inherit(ChildClass, BaseClass);
 */
HW = new function () {
    /**
     * inherit between two class
     * @param target
     * @param base
     */
    this.inherit = function (target, base) {
        base = new base();
        var inherited = $.extend({}, base, target.prototype, {parent: base});
        $.extend(target.prototype, inherited);
    }
};
/**
 * WP CLI Manager
 * @Param _module
 * @param ref
 * @constructor
 */
function HW_CLI_Commands_Manager (_module, ref) {
    var holder = jQuery('<div/>');

    //command selector
    var cmd_select = jQuery('<select/>');
    //sub commands selector
    var subcmd_select = jQuery('<select/>');
    //params input
    var params_input = jQuery('<input/>');
    var cmd_btn = jQuery('<a/>'). addClass('button').attr('href', 'javascript:void(0)').html("Run");

    var $this = this;
    var installer = ref;    //reference object
    var cmds_data = null;

    holder.append(cmd_select);
    holder.append(subcmd_select);
    holder.append(params_input);
    holder.append(cmd_btn);

    //change main command
    cmd_select.bind('change', onChangeCommand);
    cmd_btn.bind('click', onClick_to_run_clicmd);
    /**
     * process events
     * @param e
     */
    function onChangeCommand(e) {
        if(cmds_data && cmds_data[this.value]) {
            load_subcmds_data(this.value);
        }
    }

    /**
     * run cli command
     * @param e
     */
    function onClick_to_run_clicmd(e) {
        var cmd=cmd_select.val(),
            subcmd = subcmd_select.val(),
            params = params_input.val();

        //valid
        if(!cmd || !subcmd) return; //invalid
        //confirm
        if(!confirm('Are you sure to run command: '+cmd+' '+subcmd)) return;
        if(jQuery(e.target).hw_is_ajax_working({loadingText: "Running.."})) {
            return;
        }

        var url = __hw_global_object.main_ajax_url + '&ajax_name=hw_run_cli&module='+ _module+ '&_cmd=' + cmd+'&_subcmd=' + subcmd + '&_params='+params;
        __hw_installer.ajax.request({
            type: 'POST',
            url: url,
            data: {},
            //async: false,
            success: function(data){
                installer.get_logger().add_log(data);
                cmd_btn.hw_reset_ajax_state();  //reset ajax state
            }
        });
    }
    /**
     * load cmds data
     */
    function load_cmds_data() {
        if(!cmds_data) return;
        cmd_select.empty(); //clear old items
        for(var cmd in cmds_data) {
            cmd_select.append(jQuery('<option>', {
                value: cmd,
                text: cmd
            }));
        }
    }

    /**
     * load subcommands data
     * @param cmd
     */
    function load_subcmds_data(cmd) {
        if(cmds_data && cmds_data[cmd]) {
            //clear old items
            subcmd_select.empty();
            jQuery.each(cmds_data[cmd], function(subcmd, params) {
                subcmd_select.append(jQuery('<option>', {
                    value: subcmd,
                    text: subcmd
                }));
            });
        }
    }

    /**
     * get UI for container
     * @returns {jQuery|*}
     */
    $this.get = function() {
        return holder;
    }
    /**
     * load cli commands data
     * @param data
     */
    $this.load =function() {
        jQuery(cmd_btn).hw_is_ajax_working({loadingText: 'Loading..'})    ;//html('Loading...');
        var url = __hw_global_object.main_ajax_url + '&ajax_name=hw_load_wpcli_commands&module=' +_module;
        __hw_installer.ajax.request({
            type: 'POST',
            url: url,
            data: {},
            //async: false,   //asynchronous option to be false to get a synchronous Ajax request
            dataType: 'json',
            success: function(data){
                if(data && data.data) {
                    cmds_data = data.data;
                    load_cmds_data();   //load main commands
                    cmd_btn.hw_reset_ajax_state();
                }
            }
        });
    }
}
/**
 * Class HW_Logger
 * @constructor
 */
function HW_Logger() {
    var logs = jQuery('<div />').addClass('hw-logger'),
        p= this;    //this instance
    //messages data
    var data = {};
    /**
     * add message to log viewport
     * @param str
     */
    p.add_log =function(str, id,level) {
        if(!level) level = 'success';
        if(!id || !data[id]) {
            var item = jQuery('<div/>', {class: "item-log msg-"+ level});
            item.append(str);
            logs.append(item);
            //add to manager
            if(id) data[id] = str;
        }
    }
    /**
     * clear all messages
     */
    p.clear = function() {
        logs.empty();
    }
    /**
     * logs ui
     * @returns {*|addClass|addClass|addClass|addClass|addClass}
     */
    p.get =function() {
        return logs;
    }
    /**
     * append logger to UI
     * @param container
     */
    p.show = function(container) {
        if(jQuery(container).length) jQuery(container).append(p.get());
    }
}
/**
 * installation class
 * http://www.dave-bond.com/blog/2010/01/JQuery-ajax-progress-HMTL5/
 * @param pass module to constructor
 * @param module_slug module slug
 * @param name module display name
 * @constructor
 */
function HW_ProgressInstaller(module_slug, name) {
    /**
     * current instance
     * @type {HW_ProgressInstaller}
     */
    var $this = this;
    /**
     * for module
     * @type {null}
     */
    var _module = null;
    /**
     * progressbar object
     * @type {null}
     */
    var progressbar = null;
    var progressLabel = null;

    /**
     * count percetage
     * @type {number}
     */
    var total = 0;
    /**
     * Ajax handler
     * @type {XMLHttpRequest}
     */
    var xhr = new window.XMLHttpRequest();
    /**
     * HW_Logger object
     */
    var logger = new HW_Logger();
    /**
     * cli manager
     * @type {HW_CLI_Commands_Manager}
     */
    var cli_manager = new HW_CLI_Commands_Manager(module_slug, $this);
    /**
     * command buttons
     */
    var buttons_wrapper;
    //count number of instances from this class
    HW_ProgressInstaller.numInstances = (HW_ProgressInstaller.numInstances || 0) + 1;

    if(module_slug) $this.module_slug = _module = module_slug;
    $this.module_name = name;

    //init ui
    var message = jQuery(document.createElement('div')),
        result = jQuery('<div/>'),
        container = jQuery('<div/>').append(message);
    var track_global_logs;  //request ajax to get logs data in every time with interval specifying

    //progressbar element
    progressbar = jQuery('<div id="hw-installer-'+HW_ProgressInstaller.numInstances+'"/>').append('<div class="progress-label">Loading...</div>');
    progressLabel = progressbar.find('.progress-label');    //progress label
    progressbar.progressbar({
        value: false,
        change: function() {
            progressLabel.text( Math.round(progressbar.progressbar( "value" )) + "%" );
        },
        complete: function() {
            progressLabel.text( "Complete!" );
        }
    });
    container.append(progressbar);
    container.append(result);
    /**
     * update progressbar
     * @param percent
     */
    function update_progressbar(percent) {
        jQuery( progressbar ).progressbar({value:percent});
    }
    /**
     * uncomputable process
     * @callback addEventListener("progress")
     * @param evt
     */
    function in_uncomputable_progress(evt) {
        /*if (evt.lengthComputable) {
            var percentComplete = evt.loaded / evt.total;     //for computable
        }
        */
        if(evt.loaded && (!total || total+100<evt.loaded)) total = evt.loaded*10;
        var percent=(evt.loaded/total)*100;
        update_progressbar(percent);
    }

    /**
     * complete event for ajax
     * @param data
     */
    function complete_process(data) {
        message.html('Hoàn tất !');
        if(data) result.fadeTo("fast",1).html(data.toString());
        progressbar.progressbar({value:100});
        logger.add_log("Hoàn tất !", 'sucess');
        end_tracking_logs(5000);
    }
    /**
     * init ajax invoking
     * @param command
     * @param success success callback
     * @param args
     * @param post_data
     */
    function start(command, success, args, post_data) {
        var url = __hw_global_object.main_ajax_url + '&ajax_name=hw_installer&module='+ _module;
        if(command) url += '&command='+ command;
        message.html("Đang cài đặt...");
        result.fadeTo("slow",0.5);

        __hw_installer.ajax.request({
            xhr: function()
            {
                //Download or upload progress
                xhr.addEventListener("progress", in_uncomputable_progress, false);
                return xhr;
            },
            type: 'POST',
            url: url,
            data: post_data? post_data:{},
            //async: false,   //asynchronous option to be false to get a synchronous Ajax request
            success: function(data){
                var segments=null, result={};
                try {   //api output
                    result = JSON.parse(data);
                    if(result && result.segments) segments = result.segments;
                }
                catch (e){}
                //Do something success-ish
                if(args && !args.display) complete_process();else complete_process(data);
                if(typeof success == 'function') success(data, result);
            }
        });
        //tracking messages
        if(args && args.reset_log) logger.clear(); //reset logs
        end_tracking_logs(0, 1);  //end logs tracking
        //track_logging_messages(100);  //since getting mesasge direct from each ajax request for the module
    }

    /**
     * track logging messages
     * @param time
     * @param callback
     */
    function track_logging_messages(time, callback) {
        var locked=1;
        //valid
        if(!time) time=100;
        __hw_installer.ajax.request({
            url: __hw_global_object.main_ajax_url + '&ajax_name=hw_logger&clear=0&module='+ _module,
            type: 'POST',
            dataType:"json",
            data: {},
            //async: false,   //asynchronous option to be false to get a synchronous Ajax request
            success: function(data) {
                locked=0;//console.log(data.data);
                if(!data) return;   //json error
                for(var id in data.data) {
                    logger.add_log(data.data[id].message, id,data.data[id].level);
                }
                if(typeof callback=='function') callback(data.data);
            }
        });
        if(!track_global_logs)
        track_global_logs = setInterval(function(){
            if(!locked) track_logging_messages();
        }, time);
    }

    /**
     * end tracking logs while complete installation
     * @param time
     * @param force
     */
    function end_tracking_logs(time, force) {
        if(!time) time= 100;
        if(!force) setTimeout(function(){end_tracking_logs(time,1)}, time);    //wait until 5s
        else if(track_global_logs) clearInterval(track_global_logs);
    }
    /**
     * display installer
     * @param ele
     */
    $this.show = function(ele){
        jQuery(ele).append(container);
    }
    /**
     * display logger to container
     * @param ele
     */
    $this.show_logs =function(ele) {
        jQuery(ele).append(logger.get());
    }
    /**
     * display cli manager for the module
     * @param ele
     */
    $this.show_cli_manager = function(ele) {
        jQuery(ele).append(cli_manager.get());
    }
    /**
     * return cli_manager
     * @returns {HW_CLI_Commands_Manager}
     */
    $this.get_cli = function() {
        return cli_manager;
    }
    /**
     * initial buttons in tray
     * @param ele
     */
    $this.init_buttons = function(ele) {
        buttons_wrapper = jQuery(ele);
    }
    /**
     * locked buttons keyboard
     */
    $this.disable_buttons = function(opt) {
        if(buttons_wrapper) {
            if(opt || typeof opt=='undefined') buttons_wrapper.block({ message: null });   //block ui with no messages
            else buttons_wrapper.unblock();
        }
    }
    /**
     * return logger object
     * @returns {HW_Logger}
     */
    $this.get_logger = function() {
        return logger;
    }
    /**
     * start module as command
     * @param command
     * @param success_callback
     * @param args
     * @param post_data
     */
    $this.start = function(command, success_callback, args, post_data) {
        progressbar.progressbar({value:0}); //reset progressbar
        jQuery(this).delay(1000).queue(function(next) {
            start(command, success_callback, args, post_data);
            jQuery(this).remove();
            next();
        });

    }
    //start("http://localhost/1.php");
}
/**
 * Command segments
 * @constructor
 */
function HW_Module_Command_Segments(module, command) {
    var $this=this,
        content_holder = jQuery('<div/>'),
        run_btn = jQuery('<input/>').attr({type:'button', value:'Execute'});

    var __segments;   //segments data
    var _segments_in_queue;  //execute segments in queue
    var _list_to_do = {segments:[]};

    var dialog = jQuery('<div/>').dialog({
        title: "List to do for ("+ module.module_name+ ")",
        autoOpen: false,
        modal: true ,         //show cover
        //effects
        show: {
            effect: "blind",
            duration: 1000
        },
        hide: {
            effect: "blind",//explode
            duration: 1000
        },
        //closeOnEscape: false,
        open: function(event, ui) {
            //jQuery(".ui-dialog-titlebar-close", ui.dialog | ui).hide();  //hide close button
            var dlg= jQuery(ui.dialog |ui);
            if(!dlg.data('setup-dlg-titlebar-close')) {
                jQuery(".ui-dialog-titlebar-close", ui.dialog | ui).bind('click', function() {
                    module.disable_buttons(false);
                });
                dlg.data('setup-dlg-titlebar-close', 1);
            }

        },

        close: function (event, ui) { //re-create close button event.
            jQuery(dialog.data('trigger_button')).hw_reset_ajax_state();
            //dialog.dialog('close');

        }
    }).css({'z-index': '1000'});
    dialog.append([content_holder, run_btn]);

    /**
     * create list to do item
     * @param item
     * @returns {jQuery|*}
     */
    function create_list_to_do_item(item) {
        var container= jQuery('<div/>'),
            label =jQuery('<label/>').css({width:'100%', display:'inline-block'}),
            atts = {name: (item.name? item.name : ''), 'data-name': item.display, 'data-type': 'other'};

        if(item.field !='select' && item.field != 'textarea' ) atts.type = item.field? item.field: 'text';
        if(item.hide) container.hide();

        label.append("<strong>"+item.display + "</strong>");
        if(item.type =='segment') {
            _list_to_do.segments.push(item.value);
            var cb;
            if(item.field=='checkbox') {
                atts['data-type'] = 'segment';
                atts.value= item.value;
                if(!item.name) item.name = 'list_to_do[]';
                cb = jQuery('<input/>').attr(atts);
                cb.prop('checked', true);   //set checked default
                label.append(cb);
            }
        }
        else if(item.type == 'sample_data') {
            _list_to_do['sample_data']='';
            var select;
            if(item.field=='select') {
                atts['data-type'] = 'sample-data';
                if(!item.name) item.name = 'sample-data';
                select = jQuery('<select/>').attr(atts);
                for(var id in item.value)
                select.append(jQuery('<option/>', {
                    value: id,
                    text: item.value[id]
                }));
                _list_to_do['sample_data'] = select;
                label.append(select);
            }
        }
        else if(typeof item !='function') {
            var field;
            if(item.field=='checkbox') {
                atts.value = item.value? item.value: item.name;
                if(!item.name) item.name = item['data-type'];
                field = jQuery('<input/>').attr(atts);
                field.prop('checked', true);   //set checked default
                label.append(field);
            }

        }
        //else return ;
        container.append([label]);
        return container;
    }
    /**
     * list segments on list to do table
     * @param segments|list_to_do
     * @param start_cb
     */
    function show_segments_list(list_to_do, start_cb) {
        //__segments = segments;   //store segments

        var item;
        content_holder.empty(); //clear dialog components
        for(var i in list_to_do) {
            item = create_list_to_do_item(list_to_do[i]);
            if(item) content_holder.append(item);
        }
        /*for(var id in segments) {
            label= jQuery('<label/>').css({width:'100%', display:'inline-block'});
            cb = jQuery('<input/>').attr({value: id, type:"checkbox", name: 'list_to_do[]', 'data-name': segments[id] });
            cb.prop('checked', true);   //set checked default
            label.append(cb);
            label.append("<strong>"+segments[id] + "</strong>");
            content_holder.append(label);
        }*/
        if(item) { //already exists
            dialog.dialog('open');//.attr('title');
            if(typeof start_cb =='function') _segments_in_queue = start_cb;
        }
    }


    /**
     * execute selected segments
     * @param e
     */
    function execute_btn_event(e) {
        //get user choose segments
        var selected_segments = content_holder.find('input[type=checkbox][data-type=segment]:checked'),
            segments = {},
            data = {};
        jQuery( selected_segments).each(function(i,v) {
            segments[jQuery(v).val()] = jQuery(v).data('name');
        });
        //other data
        content_holder.find('input[type=checkbox]:checked,select').each(function(i, v) {
            var key =jQuery(v).attr('name');
            if(!key && jQuery(v).data('type')) key = jQuery(v).data('type');
            var val = jQuery(v).val();

            data[key] = val;
        });

        if(typeof _segments_in_queue=='function') _segments_in_queue(segments,  data);
        //hide dialog
        dialog.dialog('close');
    }
    run_btn.bind('click', execute_btn_event);
    /**
     * execute command for the module
     * @param callback whenever done segment job
     * @param complete when all segments done
     */
    $this.execute = function(callback, complete, obj) {
        var loop_segment_time = 100;
        dialog.data('trigger_button', obj);
        module.disable_buttons(true);    //disable all buttons event

        module.start(command, function(data, _segments) {//console.log(_segments);
            //get command segments
            if(_segments && _segments.main_segments) {
                var execute_segments= {}, segments_keys,   //save executed segment
                    main_segments=segments = _segments.main_segments;

                //show list to do
                show_segments_list(_segments.list_to_do, function(new_segments, addition_data) {
                    if(new_segments) main_segments=segments = new_segments;
                    segments_keys = jQuery(Object.keys(segments)).hw_unique();
                    if(_segments.result) jQuery.extend(addition_data, _segments.result);
                    segments_in_queue(addition_data);
                });
                /**
                 * segments in queue
                 * @param complete
                 * @param segments
                 */
                function segments_in_queue(/*complete, segments*/addition_data) {
                    var segment= segments_keys.shift();
                    if(!segment ) {
                        if(typeof complete =='function') complete();
                        module.disable_buttons(false);   //rebind buttons event
                        return; //empty queue
                    }
                    if(execute_segments[segment]) {
                        setTimeout(function(){segments_in_queue(addition_data) },loop_segment_time); //next segment
                        return;
                    }
                    else execute_segments[segment]=1;
                    module.get_logger().add_log("Installing... segment "+ segments[segment]);
                    //for(var i in segments) {
                    module.start(command+'&cmd_segment='+segment+'&_display=0' , function(data1, next_segments) {console.log('result', next_segments);
                            if(next_segments.result) {
                                jQuery.extend(addition_data, {result:next_segments.result});
                                module.get_logger().add_log(next_segments.result);
                            }
                            if(next_segments.segments) {
                                segments = jQuery.extend(segments, next_segments.segments);
                                segments_keys = jQuery(Object.keys(segments)).hw_unique();//console.log('next-keys:',segments_keys);
                            }
                            module.get_logger().add_log("Installed segment "+segments[segment]);
                            //console.log(_segments);
                            setTimeout(function(){segments_in_queue(addition_data)} , loop_segment_time);   //remain
                            if(typeof callback == 'function') callback(data1);  //callback for segment from command
                        },
                        {display:0}, {segments: Object.keys(main_segments), addition_data: addition_data});
                    //}
                }
                //segments_in_queue();
            }
            else {
                if(typeof complete =='function') complete();
            }
            if(typeof callback == 'function') callback(data);   //first callback
        }, {reset_log:true});
    }

}
/**
 * dispose instance
 * @returns {number}
 */
HW_ProgressInstaller.prototype.dispose=function(){
    return HW_ProgressInstaller.numInstances -= 1;
}
/**
 * alias
 * @type {{}|*}
 */
__hw_installer.modules_installer = __hw_installer.modules_installer || {};
__hw_installer.modules_alias = __hw_installer.modules_alias || {};
__hw_installer.ajax_requests = __hw_installer.ajax_requests || [];  //manage ajax requests

/**
 * create new installer
 * @param module module slug
 * @Param title module title
 * @returns {HW_ProgressInstaller}
 */
__hw_installer.create = function(module, title){
    if(typeof __hw_installer.modules_installer[module]=='undefined') {
        var inst = new HW_ProgressInstaller(module, title);
        //var module_cli = new HW_CLI_Commands_Manager(module);
        __hw_installer.modules_installer[module] = {object: inst};
    }
};
/**
 * return installer for given module
 * @param module module slug or alias
 * @param container
 * @param logger
 */
__hw_installer.get = function(module, container, logger) {
    var installer;
    if(typeof this.modules_alias[module] != 'undefined') {
        installer = this.modules_alias[module].object;
    }
    else {
        this.create(module);    //create installer for first check
        if(typeof this.modules_installer[module] !='undefined') {
            installer = this.modules_installer[module].object;
        }
    }

    if(jQuery(container).length && installer) installer.show(container);
    if(jQuery(logger).length && installer) installer.show_logs(logger);
    return installer;
}
/**
 * do command on module config
 * @param command
 * @param module
 * @param obj
 * @param callback
 */
__hw_installer.command = function(command, module, obj, callback) {
    if(typeof module == 'string') module = __hw_installer.get(module);  //get module configuration
    if(obj && jQuery(obj).hw_is_ajax_working({alert:1, loadingText:'working..'})) return;

    if(obj) jQuery(obj).off('click');

    var cmd = new HW_Module_Command_Segments(module, command);
    cmd.execute(callback, function(){   //complete command
        jQuery(obj).hw_reset_ajax_state();
        jQuery(obj).on('click');    //resume click event
    }, obj);

}
/**
 * init module comands
 * @param module
 * @param commands
 */
__hw_installer.load_module_commands = function(alias, module, commands) {
    this.create(module);    //create installer for first check
    if(commands) {
        //get real module slug
        commands.module = function() {
            return module;
        };
        //get module installer
        commands.get = function() {
            return this.get(module);
        };
        //view module stats
        commands.view_stats = function(obj) {
            //because call from html element event, do not use `this`
            __hw_installer.command('view_stats', commands.module(), obj);
        };
        this.modules_installer[module]['commands'] = commands;
        this.modules_alias[alias] = this.modules_installer[module]; //save to alias
    }
};
/**
 * get commands for module
 * @param module_alias
 * @param command
 */
__hw_installer.get_commands = function(module_alias, command) {
    if(typeof this.modules_alias[module_alias] !=='undefined') {
        var commands = this.modules_alias[module_alias]['commands'];
        if(commands[command]!= undefined) {
            return commands[command];
        }
        return commands;
    }

};

/**
 * ajax controller
 */
__hw_installer.ajax = new function() {
    var data={};
    var count=0;
    //request ajax
    function _ajax(args){
        //_ajax.id=_ajax.id||0;
        var id=count++;
        var old_callback = args.success;
        if(typeof args.success=='function') args.success = (function(_id) {
            return function(data){
            old_callback(data);
            delete data[_id];
            }
        })(id);
        data[id]= jQuery.ajax(args);
        return data[id];
    }
    //before reload page
    window.onbeforeunload = function() {
        for(var i in data)
            if(data[i] !== undefined) {
                data[i].abort();
            }
    }
    this.request=_ajax;
}