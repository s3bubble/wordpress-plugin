jQuery( document ).ready(function( $ ) {
    tinymce.create('tinymce.plugins.S3bubbleOembed', {
        init : function(ed, url) {

            /*
             * S3bubble video
             */
            ed.addButton('s3bubble_oembed_global_shortcode', {
                title : 'AWS S3Bubble',
                cmd : 's3bubble_oembed_global_shortcode',
                image : 'https://s3.amazonaws.com/s3bubble-cdn/theme-images/s3bubblelogo.png',
                //text: 'S3Bubble',
                //icon: false
            });
            ed.addCommand('s3bubble_oembed_global_shortcode', function() {
                
                var website = window.location.host.indexOf('www.') && window.location.host || window.location.host.replace('www.', '');

                $.post("https://s3bubbleapi.com/plugin/codes", {
                    website: website
                }, function(response) {

                    if(response.error){

                        // run a alert
                        swal({
                          title: "Error! " + response.message,
                          text: "Loading...",
                          type: "error",
                          showCancelButton: false,
                          showConfirmButton: true,
                          confirmButtonClass: "button button-danger",
                          confirmButtonText: "Ok",
                          closeOnConfirm: true
                        },
                        function(isConfirm) {
                          $('.s3bubble-select-group').html();
                        });
                         
                    }else{

                        swal({
                          title: "AWS GENERATED MEDIA",
                          text: "Success your media will be listed in the dropdown below if you media is not shown please visit your media at least once in the S3Bubble Dashboard.", 
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

                        var html = '<select class="chosen-select" tabindex="1" name="s3bubbleIframeUrl" id="s3bubbleIframeUrl"><option value="">Select Media</option>';

                        // GET THE LIVE STREAMS
                        $.each(response.streams, function (i, item) {

                            var stream = item.stream;
                            var title = (item.title) ? s3bubblePluginCleanFilename(item.title) : "No Title";
                            html += "<option data-class='s3bubble-live' data-type='' data-key='stream' value='" + stream + "'>Type: Live, Stream: " + stream + ", Title: " + title + "</option>";

                        });

                        // GET THE PLAYLISTS
                        $.each(response.playlists, function (i, item) {

                            var code = item.code;
                            var title = s3bubblePluginCleanFilename(item.title);
                            var type = item.type;
                            html += "<option data-class='s3bubble-playlist' data-type='" + type + "' data-key='" + title + "' value='" + code + "'>Type: Playlist, Code: " + code + ", Title: " + title + "</option>"; 
                            
                        });

                        // GET THE MEDIA
                        $.each(response.media, function (i, item) {

                            var code = item.code;
                            var key = item.key;
                            var title = (item.title) ? s3bubblePluginCleanFilename(item.title) : key;
                            var type = (item.type === 'audio') ? 's3bubble-audio' : 's3bubble'; 
                            var className = (item.type === 'audio') ? 's3bubble-audio' : 's3bubble'; 
                            html += "<option data-class='" + className + "' data-type='' data-key='" + key + "' value='" + code + "'>Type: Media, Code: " + code + ", Title: " + title + "</option>"; 

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