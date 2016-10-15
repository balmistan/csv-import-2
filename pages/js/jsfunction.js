/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

JQUERY4U = {
    sendToServer: function (linktosend, datatosend) {
        datatosend = datatosend || "[]";
        $("#wait-icon").show();
        var ret = null;
        $.ajax({
            url: linktosend,
            type: 'POST',
            data: datatosend,
            async: false,
            success: function (data) {
                try {
                    ret = JSON.parse(data);
                } catch (error) {
                    //alert(error.message)
                    // ret = null;
                    location.href = '../index.php';
                }
            },
            cache: false,
            contentType: false,
            processData: false
        });
        $("#wait-icon").hide();
        return ret;
    }, // close function sendToServer(datatosend, linktosend)
    assocChange: function () {
        var selected_option = Array();

        
        $(".db_assoc option").show();
        $(".db_assoc").each(function () {
            if ($(this).val() != "")
                selected_option.push($(this).val());
        });
        
        $(".db_assoc").each(function () {
            for(var i=0; i<selected_option.length; i++){
                if($(this).val() != selected_option[i]){
                    $(this).find("option[value="+ selected_option[i]+"]").hide();
                }
            }
        });
  
    }


}


