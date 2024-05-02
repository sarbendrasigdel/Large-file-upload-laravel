<!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    {{-- <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script> --}}
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
<script>
    $(document).ready(function() {
        $('#uploadButton').click(function() {
            var fileInput = document.getElementById('fileInput');
            var file = fileInput.files[0];
            var chunkSize = 5 * 1024 * 1024; // 5MB chunk size
            var totalChunks = Math.ceil(file.size / chunkSize);
            var start = 0;

            var progressBarContainer = $('#progressBarContainer');
            var progressBar = $('#progressBar');

            progressBarContainer.show();

            uploadChunk(start);

            function uploadChunk(start) {
                var formData = new FormData();
                formData.append('file', file.slice(start, start + chunkSize));
                formData.append('chunk', Math.ceil(start / chunkSize) + 1); // Chunk number
                formData.append('chunkSize', chunkSize);
                formData.append('totalChunks', totalChunks);

                $.ajax({
                    url: '{{ route("upload.chunk") }}',
                    type: 'POST',
                    headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                    data: formData,
                    contentType: false,
                    processData: false,
                    xhr: function() {
                        var xhr = $.ajaxSettings.xhr();
                        xhr.upload.onprogress = function(event) {
                            if (event.lengthComputable) {
                                var percentComplete = (event.loaded / event.total) * 100;
                                progressBar.css('width', percentComplete + '%');
                            }
                        };
                        return xhr;
                    },
                    success: function(response) {
                        start += chunkSize;
                        if (start < file.size) {
                            uploadChunk(start);
                        } else {
                            progressBar.css('width', '100%');
                            progressBarContainer.hide();
                            console.log('File upload complete');
                        }
                    },
                    // error: function(xhr, status, error) {
                    //     console.error('Error uploading file:', error);
                    // }
                });
            }
        });
    });
</script>
