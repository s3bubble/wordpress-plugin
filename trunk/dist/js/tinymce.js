jQuery( document ).ready(function( $ ) {
    tinymce.create('tinymce.plugins.S3bubbleOembed', {
        init : function(ed, url) {

            /*
             * S3bubble video
             */
            ed.addButton('s3bubble_oembed_global_shortcode', {
                title : 'S3Bubble',
                cmd : 's3bubble_oembed_global_shortcode',
                image : url + '/s3bubblelogo.png'
            });
            ed.addCommand('s3bubble_oembed_global_shortcode', function() {
                
                var website = window.location.host.indexOf('www.') && window.location.host || window.location.host.replace('www.', '');
                $.post("https://s3bubbleapi.com/plugin/codes", {
                    website: website
                }, function(response) {

                    if(response.error){

                        // run a alert
                        swal({
                          title: "Error",
                          html: true,
                          text: response.message,
                          type: "warning",
                          showCancelButton: true,
                          showConfirmButton: true,
                          confirmButtonText: "Open",
                          cancelButtonText: "Cancel",
                          closeOnConfirm: true,
                          closeOnCancel: true
                        },
                        function(isConfirm) {
                            
                            if (isConfirm) {

                                var win = window.open('https://s3bubble.com/app/#/wpwebsites', '_blank');
                                win.focus();
                            }

                        });
                         
                    }else{

                        swal({
                          title: "Success",
                          html: true,
                          text: "Your Channel Players are listed below. <p class='s3bubble-select-group'></p>", 
                          type: "success",
                          showCancelButton: true,
                          showConfirmButton: true,
                          confirmButtonClass: "button button-primary",
                          confirmButtonText: "Insert",
                          cancelButtonText: "Cancel",
                          closeOnConfirm: true,
                          closeOnCancel: true
                        },
                        function(isConfirm) {
                            if (isConfirm) {

                                var s3bubbleClass = $('#s3bubbleIframeUrl').find('option:selected').data('class');
                                var s3bubbleType  = $('#s3bubbleIframeUrl').find('option:selected').data('type');
                                var s3bubbleKey   = $('#s3bubbleIframeUrl').find('option:selected').data('key');
                                var s3bubbleCode  = $('#s3bubbleIframeUrl').find('option:selected').val();

                                tinyMCE.activeEditor.execCommand('mceInsertContent', 0, '<div class="' + s3bubbleClass + '" data-code="' + s3bubbleCode + '" data-type="' + s3bubbleType + '"><i class="s3bubble-info">AWS media ' + s3bubbleKey + ' will be displayed here...</i></div>');
                                  
                            } 
                        });

                        var s3bubblePluginCleanFilename = function(name) {

                            if (name === "" || name === undefined || name === false) {
                                return null;
                            }
                            var _name = decodeURIComponent(name);
                            _name = _name.replace(/\\/g, "");
                            return _name;

                        } 

                        var html = '<select class="chosen-select" tabindex="1" name="s3bubbleIframeUrl" id="s3bubbleIframeUrl"><option value="">Select Player</option>';

                        // GET THE LIVE STREAMS
                        $.each(response.streams, function (i, item) {

                            var stream = item.stream;
                            var title = (item.title) ? s3bubblePluginCleanFilename(item.title) : "No Title";
                            html += "<option data-class='s3bubble-live' data-type='' data-key='stream' value='" + stream + "'>Stream: " + stream + ", Title: " + title + "</option>";

                        });

                        // GET THE PLAYLISTS
                        $.each(response.playlists, function (i, item) {

                            var code = item.code;
                            var title = s3bubblePluginCleanFilename(item.title);
                            var type = item.type;
                            html += "<option data-class='s3bubble-playlist' data-type='" + type + "' data-key='" + title + "' value='" + code + "'>Code: " + code + ", Title: " + title + "</option>"; 
                            
                        });

                        // GET THE MEDIA
                        $.each(response.media, function (i, item) {

                            var code = item.code;
                            var key = item.key;
                            var title = (item.title) ? s3bubblePluginCleanFilename(item.title) : key;
                            var type = (item.type === 'audio') ? 's3bubble-audio' : 's3bubble'; 
                            var className = (item.type === 'audio') ? 's3bubble-audio' : 's3bubble'; 
                            html += "<option data-class='" + className + "' data-type='' data-key='" + key + "' value='" + code + "'>Code: " + code + ", Title: " + title + "</option>"; 

                        });

                        html += '</select>';
                        $('.s3bubble-select-group').html(html);
                        var config = {
                          '.chosen-select'           : {},
                          '.chosen-select-deselect'  : {allow_single_deselect:true},
                          '.chosen-select-no-single' : {disable_search_threshold:10},
                          '.chosen-select-no-results': {no_results_text:'Oops, nothing found!'},
                          '.chosen-select-width'     : {width:"95%"}
                        }
                        for (var selector in config) {
                          $(selector).chosen(config[selector]);
                        }

                    }   

                },'json');

            });
            
        },
    });
    // Register plugin
    tinymce.PluginManager.add( 'S3bubbleOembed', tinymce.plugins.S3bubbleOembed );
});