<script src='https://cdnjs.cloudflare.com/ajax/libs/hls.js/0.10.1/hls.min.js'></script>
<!-- Or if you want a more recent canary version -->
<!-- <script src='https://cdn.jsdelivr.net/npm/hls.js@canary'></script> -->

<?php 
extract($_GET);
print "
<video style=width:100%; id='video' autoplay controls $muted ></video>
<script>
  var video = document.getElementById('video');
  if(Hls.isSupported()) {
    var hls = new Hls();
    hls.loadSource('$path');
    hls.attachMedia(video);
    hls.on(Hls.Events.MANIFEST_PARSED,function() {
      //video.play();
  });
 }
 else if (video.canPlayType('application/vnd.apple.mpegurl')) {
    video.src = '$path';
    video.addEventListener('loadedmetadata',function() {
      //video.play();
    });
  }
</script>";
?>