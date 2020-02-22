$(document).ready(function(){
    $("select").change(function(){
        $(this).find("option:selected").each(function(){
            var optionValue = $(this).attr("value");
            if(optionValue){
                $(".data").not("." + optionValue).hide();
                $("." + optionValue).show();
            } else{
                $(".data").hide();
            }
        });
    }).change();
});

function copy(name, sourcesize){
  values = document.getElementsByName(name);
  buttons = document.getElementsByClassName('paste');
  for (var i = 0; i < buttons.length; i++) {
    if (buttons[i].value === sourcesize) {
      buttons[i].disabled = false;
    }else{
      buttons[i].disabled = true;
    }
  }
}
function paste(name){
  destination = document.getElementsByName(name);
  for(var i = 0; i < values.length; i++){
      destination[i].value = values[i].value;
  }
}
