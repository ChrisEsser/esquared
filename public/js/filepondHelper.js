FilePond.registerPlugin(FilePondPluginImageResize);

function createPond(selector) {
    return $(selector).filepond({
        server: {
            fetch: null, restore: null, revert: null,
            process: function(fieldName, file, metadata, load, error, progress, abort) {

                const formData = new FormData();
                formData.append(fieldName, file);
                const request = new XMLHttpRequest();
                request.open('POST', '/file/upload');

                request.upload.onprogress = function(e) {
                    progress(e.lengthComputable, e.loaded, e.total);
                };

                request.onload = function() {
                    if (request.status >= 200 && request.status < 300) {

                        var jsonResponse = JSON.parse(request.responseText);
                        if (typeof jsonResponse.error !== 'undefined' || typeof jsonResponse.key === 'undefined') {
                            var message = (typeof jsonResponse.error !== 'undefined') ? jsonResponse.error : 'An error occurred uploading the file';
                            alert(message);
                            return;
                        }

                        load(jsonResponse.key);
                        // $('#image_container span').text('');
                        // $('#image_container').css({'background-image' : 'url("http://app.testing/proxy/image/tmp?file=' + jsonResponse.key + '")'});

                    } else alert('An error occurred uploading the file');
                };

                request.send(formData);

                return {
                    abort: function() {
                        request.abort();
                        abort();
                    }
                }
            }
        },
        onaddfilestart: function(file) {
            // $('form button[type=submit]').attr('disabled', true);
            // canSubmit = false;
        },
        onprocessfile: function(error, file) {
            // $('form button[type=submit]').removeAttr('disabled');
            // canSubmit = true;
        },
        allowFileSizeValidation: true,
        allowFileTypeValidation: true,
        allowImageResize: true,
        imageResizeTargetWidth: 200,
        imageResizeTargetHeight: 200,
        imageResizeMode: 'cover',
        imageResizeUpscale: false,
    });
}

