var mensaje = "";
$(document).ready(function() {
    $.validator.setDefaults({
        errorClass: "help-inline",
        validClass: "help-inline",
        highlight: function(element) {
            $(element).parents(".control-group:eq(0)").removeClass("success").addClass("error");
        },
        unhighlight: function(element) {
            $(element).parents(".control-group:eq(0)").removeClass("error").addClass("success");
        }
    });

    $("input[type='submit']").click(function() {
        var valor = $(this).val();
        mensaje = "¿Confirma que desea " + valor + " este registro ?";
        if ($(this).parents("form").eq(0).valid()) {
            return confirm(mensaje);
        } else {
            return false;
        }
    });
});