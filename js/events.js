//Preloader function
$(document).ready(function(){
      $(".btn").click(function(){
    $(".loader").show();
  });
});
//Hides the preloader when the page is fully loaded.
window.addEventListener("load", function() {
    $(".loader").hide();
});
