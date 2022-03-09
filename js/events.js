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

//Toggles the responses.
$(document).ready(function(){
    $("button").click(function(){
      $(".col-sm-6").toggle();
    });
});

//Same for this one.
$(document).ready(function(){
    $("button").click(function(){
      $(".col-sm-10").toggle();
    });
});
